<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$mac_address = isset($_GET['mac_address']) ? trim($_GET['mac_address']) : '';
if (empty($mac_address)) {
    die(json_encode(["error" => "mac_address is required"]));
}

$sql_mac = "SELECT client_id FROM auto_batch_mac_address WHERE mac_address = ?";
$stmt_mac = $conn->prepare($sql_mac);
if (!$stmt_mac) {
    die(json_encode(["error" => "Prepare failed: " . $conn->error]));
}

$stmt_mac->bind_param("s", $mac_address);
$stmt_mac->execute();
$result_mac = $stmt_mac->get_result();

if ($result_mac->num_rows === 0) {
    die(json_encode(["error" => "mac_address not found"]));
}

$row_mac = $result_mac->fetch_assoc();
$client_id = (int)$row_mac['client_id'];
$stmt_mac->close();

$sql = "SELECT * FROM feed_rawmaterial WHERE client_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "Prepare failed: " . $conn->error]));
}

$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [
    "material" => []
];

while ($row = $result->fetch_assoc()) {
    if ($row['type'] === "Feed Medicine") {
        $data["material"][] = $row;
    } elseif ($row['type'] === "Raw Material") {
        $data["material"][] = $row;
    }
}

echo json_encode($data, JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>
