<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$mysqli = get_db_connection();

$sql = "SELECT id, DATE_FORMAT(feeding_time, '%H:%i:%s') AS feeding_time FROM feed_trolly_timing ORDER BY feeding_time";
if ($res = $mysqli->query($sql)) {
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        // cast id to int
        $r['id'] = (int)$r['id'];
        $rows[] = $r;
    }
    $res->free();
    $payload = ['success' => true, 'count' => count($rows), 'data' => $rows];

    $jsonFile = __DIR__ . DIRECTORY_SEPARATOR . 'timings.json';
    $written = @file_put_contents($jsonFile, json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    if ($written === false) {
        $payload['warning'] = 'Could not write timings.json (check folder permissions)';
    } else {
        $payload['json_file'] = basename($jsonFile);
    }

    echo json_encode($payload);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB query failed: ' . $mysqli->error]);
}
$mysqli->close();
?>