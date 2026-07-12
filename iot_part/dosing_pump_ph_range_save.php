<?php
header("Content-Type: application/json");

// DB connection
$host     = "216.172.184.173";
$user     = "sunfra_farms";
$password = "sunfra_farms";
$database = "sunfra_farms";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode([
        "status"  => "error",
        "message" => "DB connection failed: " . $conn->connect_error
    ]);
    exit;
}

$id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$first_range = isset($_POST['first_range']) ? $_POST['first_range'] : null;
$second_range= isset($_POST['second_range']) ? $_POST['second_range'] : null;

if ($first_range === null || $second_range === null) {
    echo json_encode([
        "status"  => "error",
        "message" => "Missing parameters"
    ]);
    exit;
}

$first  = (float)$first_range;
$second = (float)$second_range;

if ($first >= $second) {
    echo json_encode([
        "status"  => "error",
        "message" => "first_range must be less than second_range"
    ]);
    exit;
}

// Normalize to 2 decimals
$firstNorm  = number_format($first,  2, '.', '');
$secondNorm = number_format($second, 2, '.', '');

if ($id > 0) {
    // UPDATE existing row
    $stmt = $conn->prepare("UPDATE dosing_pump_ph_range SET first_range = ?, second_range = ? WHERE id = ?");
    $stmt->bind_param("ddi", $firstNorm, $secondNorm, $id);
    if (!$stmt->execute()) {
        echo json_encode([
            "status"  => "error",
            "message" => "Update failed: " . $stmt->error
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    echo json_encode([
        "status" => "success",
        "mode"   => "update",
        "id"     => $id
    ]);
} else {
    // INSERT new row
    $stmt = $conn->prepare("INSERT INTO dosing_pump_ph_range (first_range, second_range) VALUES (?, ?)");
    $stmt->bind_param("dd", $firstNorm, $secondNorm);
    if (!$stmt->execute()) {
        echo json_encode([
            "status"  => "error",
            "message" => "Insert failed: " . $stmt->error
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $newId = $stmt->insert_id;
    $stmt->close();
    echo json_encode([
        "status" => "success",
        "mode"   => "insert",
        "id"     => $newId
    ]);
}

$conn->close();
?>
