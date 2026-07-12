<?php
header("Content-Type: application/json");
date_default_timezone_set('Asia/Kolkata');

$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$id        = isset($_POST['id']) ? intval($_POST['id']) : 0;
$sheadNo   = isset($_POST['sheadNo']) ? trim($_POST['sheadNo']) : '';
$tons      = isset($_POST['tons']) ? floatval($_POST['tons']) : 0.0;

if ($sheadNo === '' || $tons <= 0 || $client_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$mysqli->begin_transaction();

try {
    if ($id > 0) {
        $stmt = $mysqli->prepare("SELECT timestamp FROM feed_shead_feeding WHERE id = ? AND client_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $mysqli->error);
        $stmt->bind_param("ii", $id, $client_id);
        $stmt->execute();
        $stmt->bind_result($old_timestamp);
        $stmt->fetch();
        $stmt->close();

        if (!empty($old_timestamp)) {
            $stmt = $mysqli->prepare("SELECT material_name, reduced_quantity FROM feed_material_reduction_logs WHERE timestamp = ? AND client_id = ?");
            if (!$stmt) throw new Exception("Log fetch failed: " . $mysqli->error);
            $stmt->bind_param("si", $old_timestamp, $client_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $material = $row['material_name'];
                $qty = $row['reduced_quantity'];

                $update = $mysqli->prepare("UPDATE feed_rawmaterial SET stock = stock + ? WHERE NAME = ? AND client_id = ?");
                if (!$update) throw new Exception("Restore stock failed: " . $mysqli->error);
                $update->bind_param("dsi", $qty, $material, $client_id);
                $update->execute();
                $update->close();
            }

            $stmt->close();

            $del = $mysqli->prepare("DELETE FROM feed_material_reduction_logs WHERE timestamp = ? AND client_id = ?");
            if (!$del) throw new Exception("Log delete failed: " . $mysqli->error);
            $del->bind_param("si", $old_timestamp, $client_id);
            $del->execute();
            $del->close();
        }
    }

    $stmt = $mysqli->prepare("SELECT feed_rawMaterial_name, quantity FROM feed_formula_detail WHERE feed_formulaType = ? AND client_id = ?");
    if (!$stmt) throw new Exception("Formula fetch failed: " . $mysqli->error);
    $stmt->bind_param("si", $sheadNo, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) throw new Exception("No formula found for sheadNo: $sheadNo");

    $timestamp = date('Y-m-d H:i:s');

    while ($row = $result->fetch_assoc()) {
        $material = $row['feed_rawMaterial_name'];
        $qty = $row['quantity'] * $tons;

        $log_stmt = $mysqli->prepare("INSERT INTO feed_material_reduction_logs (material_name, reduced_quantity, timestamp, client_id) VALUES (?, ?, ?, ?)");
        if (!$log_stmt) throw new Exception("Insert log failed: " . $mysqli->error);
        $log_stmt->bind_param("sdsi", $material, $qty, $timestamp, $client_id);
        $log_stmt->execute();
        $log_stmt->close();

        $stock_stmt = $mysqli->prepare("UPDATE feed_rawmaterial SET stock = stock - ? WHERE NAME = ? AND client_id = ?");
        if (!$stock_stmt) throw new Exception("Stock update failed: " . $mysqli->error);
        $stock_stmt->bind_param("dsi", $qty, $material, $client_id);
        $stock_stmt->execute();
        $stock_stmt->close();
    }

    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE feed_shead_feeding SET sheadNo = ?, tons = ?, timestamp = ? WHERE id = ? AND client_id = ?");
        if (!$stmt) throw new Exception("Update failed: " . $mysqli->error);
        $stmt->bind_param("sdsii", $sheadNo, $tons, $timestamp, $id, $client_id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO feed_shead_feeding (sheadNo, tons, timestamp, client_id) VALUES (?, ?, ?, ?)");
        if (!$stmt) throw new Exception("Insert failed: " . $mysqli->error);
        $stmt->bind_param("sdsi", $sheadNo, $tons, $timestamp, $client_id);
    }

    $stmt->execute();
    $stmt->close();

    $mysqli->commit();
    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
