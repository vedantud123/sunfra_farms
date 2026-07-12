<?php
header("Content-Type: application/json");

// Prevent json_encode from outputting long float tails
ini_set('serialize_precision', -1);
ini_set('precision', 14);

$host     = "216.172.184.173";
$user     = "sunfra_farms";
$password = "sunfra_farms";
$database = "sunfra_farms";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode([
        "status"  => "error",
        "message" => "DB Connection failed: " . $conn->connect_error
    ]);
    exit;
}

$sql = "SELECT id, first_range, second_range 
        FROM dosing_pump_ph_range 
        ORDER BY id ASC 
        LIMIT 1";

$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        // Normalize to exactly 2 decimal places
        // Option A: keep them as numbers
        $first  = (float) number_format((float)$row["first_range"],  2, '.', '');
        $second = (float) number_format((float)$row["second_range"], 2, '.', '');

        // If you are okay with them as strings, you could also do:
        // $first  = sprintf('%.2F', $row["first_range"]);
        // $second = sprintf('%.2F', $row["second_range"]);

        $data[] = [
            "id"           => (int)$row["id"],
            "first_range"  => $first,
            "second_range" => $second
        ];
    }

    echo json_encode([
        "status" => "success",
        "count"  => count($data),
        "data"   => $data
    ], JSON_PRESERVE_ZERO_FRACTION);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "No range data found"
    ]);
}

$conn->close();
?>
