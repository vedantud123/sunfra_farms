<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    echo json_encode(["status2" => "error", "message" => "Database connection failed."]);
    exit;
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

if ($client_id <= 0) {
    echo json_encode(["status2" => "error", "message" => "Invalid or missing client_id."]);
    exit;
}

$query = "SELECT * FROM labour_master WHERE client_id = $client_id ORDER BY id DESC";
$result = $mysqli->query($query);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rowData = [
            "id" => $row['id'],
            "name" => $row['name'],
            "dateOfBirth" => $row['dateOfBirth'],
            "address" => $row['address'],
            "phoneNumber" => $row['phoneNumber'],
            "aadhar" => $row['aadhar'],
            "joiningReference" => $row['joiningReference'],
            "relatedTo" => $row['relatedTo'],
            "startDate" => $row['startDate'],
            "endDate" => $row['endDate'],
			"status" => $row['status']
        ];
        $data[$client_id][] = $rowData;
    }

    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo json_encode([$client_id => []]);
}

$mysqli->close();
?>
