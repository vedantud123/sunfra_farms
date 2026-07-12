<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit;
}

date_default_timezone_set("Asia/Kolkata");
$timestamp = date("Y-m-d H:i:s");

$current_date = $data['date'] ?? date("Y-m-d");
$client_id = $data['client_id'] ?? null;
$shead_name = $data['shead_name'] ?? '';

$original_date = $data['original_date'] ?? $current_date;
$original_shead = $data['original_shead_name'] ?? $shead_name;

if (!$client_id || !$shead_name) {
    echo json_encode(["status" => "error", "message" => "Missing client_id or shead_name"]);
    exit;
}

function getTotalEggsFromTrays($trays, $loose) {
    $trays = floatval($trays);
    $loose = intval($loose);
    return intval(($trays * 30) + $loose);
}

$good = getTotalEggsFromTrays($data['good_trays'] ?? 0, $data['good_loose'] ?? 0);
$small = getTotalEggsFromTrays($data['small_trays'] ?? 0, $data['small_loose'] ?? 0);
$big = getTotalEggsFromTrays($data['big_trays'] ?? 0, $data['big_loose'] ?? 0);
$damaged = getTotalEggsFromTrays($data['damaged_trays'] ?? 0, $data['damaged_loose'] ?? 0);

function upsertEggs($mysqli, $shead_name, $type, $count, $timestamp, $client_id, $current_date, $original_shead, $original_date) {
    // Check if record exists for original key
    $check_sql = "SELECT id FROM egg_godown_stock 
                  WHERE shead_name = ? AND type_of_eggs = ? AND DATE(timestamp) = ? AND client_id = ?";
    $stmt = $mysqli->prepare($check_sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }
    $stmt->bind_param("sssi", $original_shead, $type, $original_date, $client_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $update_sql = "UPDATE egg_godown_stock 
                       SET no_of_eggs = ?, timestamp = ?, shead_name = ?
                       WHERE shead_name = ? AND type_of_eggs = ? AND DATE(timestamp) = ? AND client_id = ?";
        $update_stmt = $mysqli->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        $update_stmt->bind_param("isssssi", $count, $timestamp, $shead_name, $original_shead, $type, $original_date, $client_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $insert_sql = "INSERT INTO egg_godown_stock 
                       (timestamp, shead_name, no_of_eggs, type_of_eggs, client_id) 
                       VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $mysqli->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        $insert_stmt->bind_param("ssssi", $timestamp, $shead_name, $count, $type, $client_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $stmt->close();
}

try {
    $mysqli->begin_transaction();

    upsertEggs($mysqli, $shead_name, 'Good', $good, $timestamp, $client_id, $current_date, $original_shead, $original_date);
    upsertEggs($mysqli, $shead_name, 'Small', $small, $timestamp, $client_id, $current_date, $original_shead, $original_date);
    upsertEggs($mysqli, $shead_name, 'Big', $big, $timestamp, $client_id, $current_date, $original_shead, $original_date);
    upsertEggs($mysqli, $shead_name, 'Damaged', $damaged, $timestamp, $client_id, $current_date, $original_shead, $original_date);

    $mysqli->commit();

    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
