<?php
$conn =  mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Fetch all users from farm_users
$sql = "SELECT id, username, password, client_id, status FROM farm_users";
$result = $conn->query($sql);

$users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Return JSON
echo json_encode($users, JSON_PRETTY_PRINT);

// Close connection
$conn->close();
?>
