<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$id = $_POST['id'] ?? null;
$sheadNo = $_POST['sheadNo'] ?? null;
$Box_1 = $_POST['Box_1'] ?? 0;
$Box_2 = $_POST['Box_2'] ?? 0;
$Box_3 = $_POST['Box_3'] ?? 0;
$Box_4 = $_POST['Box_4'] ?? 0;
$Box_5 = $_POST['Box_5'] ?? 0;
$Box_6 = $_POST['Box_6'] ?? 0;
$Box_7 = $_POST['Box_7'] ?? 0;
$Box_8 = $_POST['Box_8'] ?? 0;
$Box_9 = $_POST['Box_9'] ?? 0;
$Box_10 = $_POST['Box_10'] ?? 0;
$client_id = $_POST['client_id'] ?? null;

if (!$client_id) {
    echo json_encode(["status" => "error", "message" => "Client ID is required."]);
    exit;
}

$date = date('Y-m-d');
$timestamp = date('Y-m-d H:i:s');

if (!empty($id)) {
    $sql = "UPDATE supervisor_feed_feeding_shead 
            SET sheadNo = ?, Box_1 = ?, Box_2 = ?, Box_3 = ?, Box_4 = ?, Box_5 = ?, Box_6 = ?, 
                Box_7 = ?, Box_8 = ?, Box_9 = ?, Box_10 = ? 
            WHERE id = ? and client_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddddddddddi", $sheadNo, $Box_1, $Box_2, $Box_3, $Box_4, $Box_5, $Box_6, 
                                   $Box_7, $Box_8, $Box_9, $Box_10, $id, $client_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Entry updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed: " . $stmt->error]);
    }
    $stmt->close();

} else {
    $check_sql = "SELECT COUNT(*) FROM supervisor_feed_feeding_shead 
                  WHERE DATE(timestamp) = ? AND sheadNo = ? AND client_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ssi", $date, $sheadNo, $client_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo json_encode(["status" => "exists", "message" => "Data already exists for today for Shead: $sheadNo"]);
        $conn->close();
        exit;
    }

    $sql = "INSERT INTO supervisor_feed_feeding_shead 
            (sheadNo, Box_1, Box_2, Box_3, Box_4, Box_5, Box_6, Box_7, Box_8, Box_9, Box_10, timestamp, client_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
	$stmt->bind_param("sddddddddddsi", $sheadNo, $Box_1, $Box_2, $Box_3, $Box_4, $Box_5, $Box_6, 
                                 $Box_7, $Box_8, $Box_9, $Box_10, $timestamp, $client_id);


    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Entry inserted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Insert failed: " . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>
