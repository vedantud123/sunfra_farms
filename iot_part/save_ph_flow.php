<?php
header('Content-Type: application/json; charset=utf-8');

// DEBUG: enable while testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "msg" => "DB connection failed"]);
    exit;
}

// Read GET parameters
$water_value = isset($_GET['water_value']) ? (float)$_GET['water_value'] : null;
$ph_value    = isset($_GET['ph_value'])    ? (float)$_GET['ph_value']    : null;

// At least one value must be provided
if ($water_value === null && $ph_value === null) {
    echo json_encode(["success" => false, "msg" => "Provide ?water_value= or ?ph_value="]);
    exit;
}

$inserted = ["water_id" => null, "ph_id" => null];

$conn->begin_transaction();

try {

    // Insert WATER
    if ($water_value !== null) {
        $stmt = $conn->prepare("INSERT INTO dosing_pump_water (water_value, timestamp) VALUES (?, NOW())");
        $stmt->bind_param("d", $water_value);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $inserted['water_id'] = $stmt->insert_id;
        $stmt->close();
    }

    // Insert PH
    if ($ph_value !== null) {
        $stmt = $conn->prepare("INSERT INTO dosing_pump_ph (ph_value, timestamp) VALUES (?, NOW())");
        $stmt->bind_param("d", $ph_value);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $inserted['ph_id'] = $stmt->insert_id;
        $stmt->close();
    }

    $conn->commit();
    echo json_encode(["success" => true, "inserted" => $inserted]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "msg" => "Insert failed", "error" => $e->getMessage()]);
}

$conn->close();
