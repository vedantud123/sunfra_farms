<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$result = $conn->query("SELECT client_id, username, company_name, status FROM sunfra_clients ORDER BY client_id DESC");

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row['client_id'];

        $data[$client_id][] = [
            "client_id" => $client_id,
            "username" => $row["username"],
            "company_name" => $row["company_name"],
            "status" => $row["status"]
        ];
    }
}

$conn->close();
echo json_encode($data, JSON_PRETTY_PRINT);
