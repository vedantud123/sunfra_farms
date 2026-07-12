<?php
if (!isset($_GET['id'])) {
    die("Missing ID");
}

$id = intval($_GET['id']);

$conn =  new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "UPDATE rental_washing_machine SET status = 'done' WHERE id = $id";
if ($conn->query($sql) === TRUE) {
    echo "Status updated successfully";
} else {
    echo "Error updating status: " . $conn->error;
}

$conn->close();
?>
