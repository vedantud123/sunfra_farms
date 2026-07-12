<?php 
header('Content-Type: application/json');
 
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
 
if ($mysqli->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "❌ DB Connection failed: " . $mysqli->connect_error
    ]));
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = trim($_POST['shead_name']);
    $cutting_price = floatval($_POST['cutting_price']);
	$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
	
    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE egg_cutting_price SET shead_name = ?, cutting_price = ? WHERE id = ? AND client_id = ?");
        $stmt->bind_param("sdii", $shead_name, $cutting_price, $id, $client_id);
        $stmt->execute();
 
        echo json_encode([
            "status" => "success",
            "message" => "✅ Updated successfully"
        ]);
    } else {
        $check = $mysqli->prepare("SELECT id FROM egg_cutting_price WHERE shead_name = ? AND client_id = ?");
        $check->bind_param("si", $shead_name, $client_id);
        $check->execute();
        $check->store_result();
 
        if ($check->num_rows > 0) {
            echo json_encode([
                "status" => "error",
                "message" => "❌ Already exists"
            ]);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO egg_cutting_price (shead_name, cutting_price, client_id) VALUES (?, ?, ?)");
            $stmt->bind_param("sdi", $shead_name, $cutting_price, $client_id);
            $stmt->execute();
 
            echo json_encode([
                "status" => "success",
                "message" => "✅ Inserted successfully"
            ]);
        }
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "❌ Invalid request method"
    ]);
}
?>
 