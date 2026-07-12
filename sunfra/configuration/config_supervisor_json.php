<?php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if (!$conn) {
    echo json_encode(["error" => "❌ Database Connection failed: " . mysqli_connect_error()]);
    exit;
}

$client_id = isset($_REQUEST['client_id']) ? intval($_REQUEST['client_id']) : 0;
if ($client_id <= 0) {
    echo json_encode(["error" => "❌ client_id missing or invalid"]);
    exit;
}

$sql = "SELECT id, feature, username, client_id 
        FROM config_feature 
        WHERE client_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
