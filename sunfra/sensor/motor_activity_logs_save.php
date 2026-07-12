<?php

header("Content-Type: application/json");

$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

$mysqli = new mysqli($host, $user, $password, $database);

if ($mysqli->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed"
    ]);
    exit;
}

$motor1 = isset($_REQUEST['motor1']) ? $_REQUEST['motor1'] : NULL;
$motor2 = isset($_REQUEST['motor2']) ? $_REQUEST['motor2'] : NULL;
$motor3 = isset($_REQUEST['motor3']) ? $_REQUEST['motor3'] : NULL;
$motor4 = isset($_REQUEST['motor4']) ? $_REQUEST['motor4'] : NULL;
$motor5 = isset($_REQUEST['motor5']) ? $_REQUEST['motor5'] : NULL;

$query = "INSERT INTO motor_activity_logs (motor1, motor2, motor3, motor4, motor5, timestamp) 
          VALUES ('$motor1','$motor2','$motor3','$motor4','$motor5', NOW())";

if ($mysqli->query($query)) {

    echo json_encode([
        "status" => "success",
        "message" => "Motor data saved successfully"
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "Insert failed",
        "error" => $mysqli->error
    ]);

}

$mysqli->close();

?>