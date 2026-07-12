<?php
$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

function get_db_connection() {
    global $host, $user, $password, $database;
    $mysqli = new mysqli($host, $user, $password, $database);
    if ($mysqli->connect_errno) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $mysqli->connect_error]);
        exit;
    }
    $mysqli->set_charset('utf8');
    return $mysqli;
}
?>
