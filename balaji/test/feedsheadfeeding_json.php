<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");



$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM feed_shead_feeding ORDER BY id DESC");

if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $mysqli->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result(); // ✅ This gets result from the prepared statement

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clientId = $row["client_id"] ?? '0';

        if (!isset($data[$clientId])) {
            $data[$clientId] = [];
        }

        $data[$clientId][] = [
            'id'        => $row['id'],
            'sheadNo'   => $row['sheadNo'],
            'tons'      => $row['tons'],
            'timestamp' => $row['timestamp']
        ];
    }
    $result->free();
} else {
    echo json_encode(["error" => "Query execution failed."]);
    exit;
}

$stmt->close();
$mysqli->close();

echo json_encode($data, JSON_PRETTY_PRINT);
exit;
?>
