<?php

header('Content-Type: application/json');

$response = [];

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

$shead_queries = ['1', '2', '3', '4', '5', '6', '7', '8', 'Chick', 'grower'];
$egg_data = [];

$query = "SELECT company_name,
    TYPE,
    feed_rawMaterial_name AS Material,
    SUM(CASE WHEN feed_formulaType = 'shead_1' THEN quantity ELSE 0 END) AS shead_1,
    SUM(CASE WHEN feed_formulaType = 'shead_2' THEN quantity ELSE 0 END) AS shead_2,
    SUM(CASE WHEN feed_formulaType = 'shead_3' THEN quantity ELSE 0 END) AS shead_3,
    SUM(CASE WHEN feed_formulaType = 'shead_4' THEN quantity ELSE 0 END) AS shead_4,
    SUM(CASE WHEN feed_formulaType = 'shead_5' THEN quantity ELSE 0 END) AS shead_5,
    SUM(CASE WHEN feed_formulaType = 'shead_6' THEN quantity ELSE 0 END) AS shead_6,
    SUM(CASE WHEN feed_formulaType = 'shead_7' THEN quantity ELSE 0 END) AS shead_7,
    SUM(CASE WHEN feed_formulaType = 'shead_8' THEN quantity ELSE 0 END) AS shead_8,
    SUM(CASE WHEN feed_formulaType = 'chick' THEN quantity ELSE 0 END) AS chick,
    SUM(CASE WHEN feed_formulaType = 'grower' THEN quantity ELSE 0 END) AS grower
FROM `feed_formula_detail`
GROUP BY TYPE, company_name, feed_rawMaterial_name
ORDER BY FIELD(TYPE, 'Feed_Formula', 'Feed_Medicine', 'Water_Medicine', 'Sanitisation'), shead_1 DESC";

$dataByType = [];

$transformedData = [];

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $company = $row["company_name"];
        $material = $row["Material"];

        foreach (['shead_1', 'shead_2', 'shead_3', 'shead_4', 'shead_5', 'shead_6', 'shead_7', 'shead_8', 'chick', 'grower'] as $key) {
            if (!isset($transformedData[$company])) {
                $transformedData[$company] = [];
            }

            if (!isset($transformedData[$company][$key])) {
                $transformedData[$company][$key] = [];
            }
			$value = (float)$row[$key];
            $rounded = round($value, 2);
            $transformedData[$company][$key][$material] = (fmod($rounded, 1) === 0.0) ? (int)$rounded : $rounded;         }
    }

    $response['feed_formula'] = $transformedData;
    $result->free();
} else {
    $response['feed_formula'] = [];
    $response['error'] = "No data found";
}


$mysqli->close();

echo json_encode($response, JSON_PRETTY_PRINT);
?>
