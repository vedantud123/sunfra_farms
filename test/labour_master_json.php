<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

// Fetch all records
$query = "SELECT * FROM labour_master ORDER BY id DESC";
$result = $mysqli->query($query);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row['client_id'] ?? '0';

        // Prepare labour row data
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
            "endDate" => $row['endDate']
        ];

        // Group by client_id
        $data[$client_id][] = $rowData;
    }

    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo json_encode(["message" => "No records found."]);
}

$mysqli->close();
?>
