<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? null;
	$shead_box = $_POST['shead_box_number'] ?? null;

    if (!$client_id || !$shead_box) {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $delete_stmt = $mysqli->prepare("DELETE FROM config_shead_box WHERE client_id = ?");
    $delete_stmt->bind_param("i", $client_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    $insert_stmt = $mysqli->prepare("INSERT INTO config_shead_box (box_numbers, client_id) VALUES (?, ?)");
    for ($i = 1; $i <= $shead_box; $i++) {
        $box_numbers = "Box_" . $i;
        $insert_stmt->bind_param("si", $box_numbers, $client_id);
        $insert_stmt->execute();
    }
    $insert_stmt->close();

    echo json_encode(["status" => "success", "message" => "Sheds inserted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
