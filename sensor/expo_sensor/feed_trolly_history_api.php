<?php
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli(
    "localhost",
    "sunfra_farms",
    "sunfra_farms",
    "sunfra_farms"
);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => "DB connection failed"
    ]);
    exit;
}

$mode = $_GET['mode'] ?? 'yesterday';
$data = [];

if ($mode === "yesterday") {

    // ✅ ONLY yesterday
    $sql = "
        SELECT 
            feed_time AS label,
            feed_quantity AS used_kg
        FROM feed_trolly_history
        WHERE feed_date = CURDATE() - INTERVAL 1 DAY
        ORDER BY feed_time
    ";

} elseif ($mode === "weekly") {

    // ✅ LAST 7 DAYS INCLUDING TODAY (NO FUTURE)
    $sql = "
        SELECT 
            feed_date AS label,
            SUM(feed_quantity) AS used_kg
        FROM feed_trolly_history
        WHERE feed_date BETWEEN CURDATE() - INTERVAL 6 DAY AND CURDATE()
        GROUP BY feed_date
        ORDER BY feed_date
    ";

} elseif ($mode === "monthly") {

    // ✅ CURRENT MONTH UP TO TODAY
  $sql = "
    SELECT 
        DATE_FORMAT(feed_date, '%Y-%m') AS label,
        SUM(feed_quantity) AS used_kg
    FROM feed_trolly_history
    WHERE feed_date <= CURDATE()
    GROUP BY YEAR(feed_date), MONTH(feed_date)
    ORDER BY YEAR(feed_date), MONTH(feed_date)
";

} elseif ($mode === "yearly") {

    // ✅ CURRENT YEAR ONLY
    $sql = "
        SELECT 
            DATE_FORMAT(feed_date, '%Y-%m') AS label,
            SUM(feed_quantity) AS used_kg
        FROM feed_trolly_history
        WHERE YEAR(feed_date) = YEAR(CURDATE())
          AND feed_date <= CURDATE()
        GROUP BY DATE_FORMAT(feed_date, '%Y-%m')
        ORDER BY label
    ";

} else {

    echo json_encode([
        "success" => false,
        "error" => "Invalid mode"
    ]);
    exit;
}

$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "mode" => $mode,
    "data" => $data
]);

$conn->close();
