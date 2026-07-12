<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start();

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

$action = $_POST['action'] ?? $_GET['action'] ?? 'add';

$client_id = intval($_POST['client_id'] ?? 0);
$material = $_POST['material'] ?? '';
$new_material = trim($_POST['new_material'] ?? '');
$material_type = $_POST['material_type'] ?? '';
$addToAccounts = isset($_POST['add_to_accounts']) ? 1 : 0;

if ($material === '__new__' && !empty($new_material)) {
    $material = $new_material; 

    $check_sql = "SELECT id FROM feed_rawmaterial WHERE name = ? AND client_id = ?";
    $stmt = $mysqli->prepare($check_sql);
    $stmt->bind_param("si", $material, $client_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $insert_sql = "INSERT INTO feed_rawmaterial (name, metric, stock, client_id, type) VALUES (?, 'KG', 0, ?, ?)";
        $insert_stmt = $mysqli->prepare($insert_sql);
        $insert_stmt->bind_param("sis", $material, $client_id, $material_type);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $stmt->close();
}

if ($action === 'save_price') {
    $material_for_price = $_POST['material'] ?? '';
    $price_per_kg = floatval($_POST['price_per_kg'] ?? 0);

    if ($material_for_price && $price_per_kg > 0) {
        $check_price_sql = "SELECT id FROM feed_rawmaterial_price WHERE name = ? AND client_id = ?";
        $check_stmt = $mysqli->prepare($check_price_sql);
        $check_stmt->bind_param("si", $material_for_price, $client_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $update_price_sql = "UPDATE feed_rawmaterial_price SET price = ? WHERE name = ? AND client_id = ?";
            $update_stmt = $mysqli->prepare($update_price_sql);
            $update_stmt->bind_param("dsi", $price_per_kg, $material_for_price, $client_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            $insert_price_sql = "INSERT INTO feed_rawmaterial_price (name, price, client_id) VALUES (?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_price_sql);
            $insert_stmt->bind_param("sdi", $material_for_price, $price_per_kg, $client_id);
            $insert_stmt->execute();
            $insert_stmt->close();
        }

        $check_stmt->close();
        $mysqli->close();
        ob_clean();
        header("Location: https://sunfra.com/farm/test/weighbridge_json_to_web.php");
        exit;
    }
}

elseif ($action === 'add_without_weighbridge') {
    $date = $_POST['date'] ?? '';
    $quantity = floatval($_POST['quantity'] ?? 0);
    $type_manual = $_POST['type'] ?? 'Purchase';

    if (empty($date) || empty($material) || $quantity <= 0 || $client_id <= 0) {
        die("❌ Required data missing or invalid for without weighbridge entry.");
    }

    $insert_query = "INSERT INTO weighbridge (
        date, vehicleNumber, material, empty, gross, net, ownerName, type, driverNumber, ownerNumber, details, client_id
    ) VALUES (
        ?, NULL, ?, NULL, NULL, ?, NULL, ?, NULL, NULL, 'Manual entry without weighbridge', ?
    )";

    $stmt = $mysqli->prepare($insert_query);
    if ($stmt === false) {
        die("❌ Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("ssdsi", $date, $material, $quantity, $type_manual, $client_id);

    if (!$stmt->execute()) {
        die("❌ Query error: " . $stmt->error);
    }
    $stmt->close();

    if ($type_manual === 'Purchase') {
        $stock_update_query = "UPDATE feed_rawmaterial SET stock = stock + ? WHERE client_id = ? AND name = ?";
    } elseif ($type_manual === 'Sale') {
        $stock_update_query = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE client_id = ? AND name = ?";
    } else {
        $stock_update_query = ''; 
    }

    if (!empty($stock_update_query)) {
        $stock_stmt = $mysqli->prepare($stock_update_query);
        if ($stock_stmt === false) {
            die("❌ Prepare failed for stock update: " . $mysqli->error);
        }
        $stock_stmt->bind_param("dis", $quantity, $client_id, $material);
        $stock_stmt->execute();
        $stock_stmt->close();
    }

    $mysqli->close();

    ob_clean();
    header("Location: https://sunfra.com/farm/test/weighbridge_json_to_web.php");
    exit;
}

elseif ($action === 'add' || $action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $vehicleNumber = $_POST['vehicleNumber'] ?? '';
    $empty = intval($_POST['empty'] ?? 0);
    $gross = intval($_POST['gross'] ?? 0);
    $net = intval($_POST['net'] ?? 0);
    $ownerName = $_POST['ownerName'] ?? '';
    $type = $_POST['type'] ?? '';
    $driverNumber = $_POST['driverNumber'] ?? '';
    $ownerNumber = $_POST['ownerNumber'] ?? '';
    $details = $_POST['details'] ?? '';

    $old_net = 0;
    $old_material = '';
    $old_type = '';

    if ($action === 'update' && $id > 0) {
        $old_query = $mysqli->prepare("SELECT net, material, type FROM weighbridge WHERE id = ? AND client_id = ?");
        $old_query->bind_param("ii", $id, $client_id);
        $old_query->execute();
        $old_query->bind_result($old_net, $old_material, $old_type);
        $old_query->fetch();
        $old_query->close();

        if ($old_material !== '') {
            if ($old_type === 'Purchase') {
                $revert_sql = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE name = ? AND client_id = ?";
            } elseif ($old_type === 'Sale') {
                $revert_sql = "UPDATE feed_rawmaterial SET stock = stock + ? WHERE name = ? AND client_id = ?";
            } else {
                $revert_sql = '';
            }

            if (!empty($revert_sql)) {
                $revert_stmt = $mysqli->prepare($revert_sql);
                $revert_stmt->bind_param("isi", $old_net, $old_material, $client_id);
                $revert_stmt->execute();
                $revert_stmt->close();
            }
        }

        $update_query = "UPDATE weighbridge SET 
            date = ?, vehicleNumber = ?, material = ?, empty = ?, gross = ?, net = ?,
            ownerName = ?, type = ?, driverNumber = ?, ownerNumber = ?, details = ?
            WHERE id = ? AND client_id = ?";

        $stmt = $mysqli->prepare($update_query);
        if ($stmt === false) {
            die("❌ Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param(
            "sssiiisssssii",
            $date, $vehicleNumber, $material, $empty, $gross, $net,
            $ownerName, $type, $driverNumber, $ownerNumber, $details,
            $id, $client_id
        );

        if (!$stmt->execute()) {
            die("❌ Query error: " . $stmt->error);
        }

        $stmt->close();
        ob_clean();
        header("Location: https://sunfra.com/farm/test/weighbridge_json_to_web.php");
        exit;
    } else {
        $insert_query = "INSERT INTO weighbridge (
            id, date, vehicleNumber, material, empty, gross, net, ownerName, type, driverNumber, ownerNumber, details, client_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($insert_query);
        if ($stmt === false) {
            die("❌ Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param(
            "isssiiisssssi",
            $id, $date, $vehicleNumber, $material, $empty, $gross, $net,
            $ownerName, $type, $driverNumber, $ownerNumber, $details,
            $client_id
        );

        if (!$stmt->execute()) {
            die("❌ Query error: " . $stmt->error);
        }

        $stmt->close();
    }

    if ($type === 'Purchase') {
        $stock_update_query = "UPDATE feed_rawmaterial SET stock = stock + ? WHERE client_id = ? AND name = ?";
    } elseif ($type === 'Sale') {
        $stock_update_query = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE client_id = ? AND name = ?";
    } else {
        $stock_update_query = '';
    }

    if (!empty($stock_update_query)) {
        $stock_stmt = $mysqli->prepare($stock_update_query);
        if ($stock_stmt === false) {
            die("❌ Prepare failed for stock update: " . $mysqli->error);
        }
        $stock_stmt->bind_param("iis", $net, $client_id, $material);
        $stock_stmt->execute();
        $stock_stmt->close();
    }

    $mysqli->close();

    if ($type === 'Purchase' && $addToAccounts === 1) {
        ob_clean();
        header("Location: https://sunfra.com/farm/test/weighbridge_json_to_web.php?show_price=1&material=" . urlencode($material) . "&client_id=$client_id");
        exit;
    }

    ob_clean();
    header("Location: https://sunfra.com/farm/test/weighbridge_json_to_web.php");
    exit;
}

$mysqli->close();
ob_end_flush();
?>