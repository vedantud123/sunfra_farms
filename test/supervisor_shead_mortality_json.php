<?php
header('Content-Type: application/json');

// DB connection
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $mysqli->connect_error]));
}

// Updated query to fetch client_id instead of company_name
$query = "SELECT id, date, sheadNo, noOfBirds, timestamp, client_id FROM supervisor_shead_mortality ORDER BY id DESC";

$result = $mysqli->query($query);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row['client_id']; // Extract client_id
        
        // Group by client_id
        if (!isset($data[$client_id])) {
            $data[$client_id] = [];
        }

        $data[$client_id][] = $row;
    }
    $result->free();
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
