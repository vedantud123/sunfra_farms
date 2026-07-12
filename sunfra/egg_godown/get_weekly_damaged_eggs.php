<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_errno) {
    echo json_encode([]);
    exit;
}

$client_id = $_GET['client_id'] ?? null;
if (!$client_id || !is_numeric($client_id)) {
    echo json_encode([]);
    exit;
}

$monthParam = $_GET['months'] ?? '';
$monthParts = array_filter(array_map('trim', explode(',', $monthParam)));

$sheadParam = $_GET['sheads'] ?? '';
$sheads = array_filter(array_map('trim', explode(',', $sheadParam)));

if (empty($monthParts) || empty($sheads)) {
    echo json_encode([]);
    exit;
}

$data = [];

foreach ($monthParts as $ym) {
    [$year, $month] = explode("-", $ym);
    $year = (int)$year;
    $month = (int)$month;

    $filterShead = "AND shead_name IN ('" . implode("','", array_map([$mysqli, 'real_escape_string'], $sheads)) . "')";
    $filterClient = "AND client_id = " . (int)$client_id;

    $weekCase = "
        CASE
            WHEN DAY(DATE(timestamp)) BETWEEN 1 AND 7 THEN 'Week 1'
            WHEN DAY(DATE(timestamp)) BETWEEN 8 AND 14 THEN 'Week 2'
            WHEN DAY(DATE(timestamp)) BETWEEN 15 AND 21 THEN 'Week 3'
            WHEN DAY(DATE(timestamp)) BETWEEN 22 AND 28 THEN 'Week 4'
            ELSE 'Week 5'
        END
    ";

    $sqls = [
        "production" => "
            SELECT shead_name, $weekCase AS week, SUM(no_of_eggs) AS val
            FROM egg_godown_stock
            WHERE type_of_eggs = 'Damaged' AND sale IS NULL AND MONTH(timestamp) = $month AND YEAR(timestamp) = $year $filterShead $filterClient
            GROUP BY shead_name, week
        ",
        "sale" => "
            SELECT shead_name, $weekCase AS week, SUM(no_of_eggs) AS val
            FROM egg_godown_stock
            WHERE type_of_eggs = 'Damaged' AND sale IS NOT NULL AND remarks = 'Return' AND MONTH(timestamp) = $month AND YEAR(timestamp) = $year $filterShead $filterClient
            GROUP BY shead_name, week
        ",
        "total" => "
            SELECT shead_name, $weekCase AS week, SUM(no_of_eggs) AS val
            FROM egg_godown_stock
            WHERE MONTH(timestamp) = $month AND YEAR(timestamp) = $year $filterShead $filterClient
            GROUP BY shead_name, week
        "
    ];

    $temp = [];

    foreach ($sqls as $type => $sql) {
        $res = $mysqli->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $key = $row['shead_name'] . '_' . $row['week'];
                if (!isset($temp[$key])) {
                    $temp[$key] = [
                        'Shead Name' => $row['shead_name'],
                        'Week' => $row['week'],
                        'Month' => date('F Y', strtotime("$year-$month-01")),
                        'Production Damaged' => 0,
                        'Sale Damaged' => 0,
                        'Total Production' => 0
                    ];
                }

                $val = (int)$row['val'];
                if ($type === "production") $temp[$key]['Production Damaged'] = $val;
                if ($type === "sale")       $temp[$key]['Sale Damaged'] = $val;
                if ($type === "total")      $temp[$key]['Total Production'] = $val;
            }
        }
    }

    foreach ($temp as &$entry) {
        $entry['Total Damaged'] = $entry['Production Damaged'] + $entry['Sale Damaged'];
        $entry['100_Trays_Damage'] = $entry['Total Production'] > 0
            ? round(($entry['Total Damaged'] / $entry['Total Production']) * 3000)
            : 0;
        unset($entry['Total Production']);
    }

    $data = array_merge($data, array_values($temp));
}

echo json_encode($data);
?>
