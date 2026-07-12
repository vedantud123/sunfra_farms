<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? null;
    $grower_number = $_POST['grower_number'] ?? null;

    if (!$client_id || !$grower_number) {
        echo json_encode(["status" => "error", "message" => "Invalid input"]);
        exit;
    }

    $delete_stmt = $mysqli->prepare("DELETE FROM shead_status WHERE client_id = ? AND description = 'Grower Number'");
    $delete_stmt->bind_param("i", $client_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    $insert_stmt = $mysqli->prepare("INSERT INTO shead_status (shead_name, description, client_id) VALUES (?, ?, ?)");
    for ($i = 1; $i <= $grower_number; $i++) {
        $grower_name = "Grower " . $i;
        $desc = "Grower Number";
        $insert_stmt->bind_param("ssi", $grower_name, $desc, $client_id);
        $insert_stmt->execute();
    }
    $insert_stmt->close();

    echo json_encode(["status" => "success", "message" => "Growers inserted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>