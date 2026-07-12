<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $mysqli->connect_error
    ]));
}

$sheadNo = isset($_REQUEST['sheadNo']) ? trim($_REQUEST['sheadNo']) : '';
$tons = isset($_REQUEST['tons']) ? floatval($_REQUEST['tons']) : 0;

$mac_address = isset($_GET['mac_address']) ? trim($_GET['mac_address']) : '';
if (empty($mac_address)) {
    die(json_encode(["error" => "mac_address is required"]));
}

$sql_mac = "SELECT client_id FROM auto_batch_mac_address WHERE mac_address = ?";
$stmt_mac = $mysqli->prepare($sql_mac);
if (!$stmt_mac) {
    die(json_encode(["error" => "Prepare failed: " . $mysqli->error]));
}

$stmt_mac->bind_param("s", $mac_address);
$stmt_mac->execute();
$result_mac = $stmt_mac->get_result();

if ($result_mac->num_rows === 0) {
    die(json_encode(["error" => "mac_address not found"]));
}

$row_mac = $result_mac->fetch_assoc();
$client_id = (int)$row_mac['client_id'];

if (empty($sheadNo)) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required field: sheadNo"
    ]);
    exit;
}

$check_sql = "SELECT id FROM feed_shead_feeding 
              WHERE sheadNo = ? 
              AND client_id = ? 
              AND DATE(timestamp) = CURDATE()";

$check_stmt = $mysqli->prepare($check_sql);
$check_stmt->bind_param("si", $sheadNo, $client_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        "status" => "exists",
        "message" => "Record already exists for today"
    ]);
} else {
    $stmt = $mysqli->prepare("SELECT feed_rawMaterial_name, quantity FROM feed_formula_detail WHERE feed_formulaType = ? AND client_id = ?");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Formula fetch failed: " . $mysqli->error]);
        exit;
    }

    $stmt->bind_param("si", $sheadNo, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "No formula found for sheadNo: $sheadNo"]);
        $stmt->close();
        exit;
    }

    $timestamp = date('Y-m-d H:i:s');

    while ($row = $result->fetch_assoc()) {
        $material = $row['feed_rawMaterial_name'];
        $qty = $row['quantity'] * $tons; 

        $log_stmt = $mysqli->prepare("INSERT INTO feed_material_reduction_logs (material_name, reduced_quantity, timestamp, client_id) VALUES (?, ?, ?, ?)");
        if (!$log_stmt) {
            echo json_encode(["status" => "error", "message" => "Insert log failed: " . $mysqli->error]);
            exit;
        }
        $log_stmt->bind_param("sdsi", $material, $qty, $timestamp, $client_id);
        $log_stmt->execute();
        $log_stmt->close();

        $stock_stmt = $mysqli->prepare("UPDATE feed_rawmaterial SET stock = stock - ? WHERE NAME = ? AND client_id = ?");
        if (!$stock_stmt) {
            echo json_encode(["status" => "error", "message" => "Stock update failed: " . $mysqli->error]);
            exit;
        }
        $stock_stmt->bind_param("dsi", $qty, $material, $client_id);
        $stock_stmt->execute();
        $stock_stmt->close();
    }
    $stmt->close();

    $insert_sql = "INSERT INTO feed_shead_feeding (sheadNo, tons, timestamp, client_id)
                   VALUES (?, ?, NOW(), ?)";
    $insert_stmt = $mysqli->prepare($insert_sql);
    $insert_stmt->bind_param("sdi", $sheadNo, $tons, $client_id);

    if ($insert_stmt->execute()) {
        echo json_encode([
            "status" => "ok",
            "message" => "Record inserted successfully, and stock reduced",
            "sheadNo" => $sheadNo,
            "tons" => $tons
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Insert failed: " . $insert_stmt->error
        ]);
    }

    $insert_stmt->close();
}


$check_stmt->close();
$mysqli->close();
?>
