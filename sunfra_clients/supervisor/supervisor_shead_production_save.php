<?php
$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn === false) {
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Kolkata');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$sheadNo = isset($_POST['sheadNo']) ? trim($_POST['sheadNo']) : '';
$no_of_trays = isset($_POST['no_of_trays']) ? intval($_POST['no_of_trays']) : 0;
$no_of_loose_eggs = isset($_POST['no_of_loose_eggs']) ? intval($_POST['no_of_loose_eggs']) : 0;
$no_of_damaged_eggs = isset($_POST['no_of_damaged_eggs']) ? intval($_POST['no_of_damaged_eggs']) : 0;
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;

$production = ($no_of_trays * 30) + $no_of_loose_eggs;
$timestamp = date('Y-m-d H:i:s');
$today = date('Y-m-d');

if ($id >= 1) {
	$sql = "UPDATE supervisor_production_shead 
			SET sheadNo = ?, no_of_trays = ?, no_of_loose_eggs = ?, production = ?, no_of_damaged_eggs = ?, client_id = ? 
			WHERE id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("siiiiii", $sheadNo, $no_of_trays, $no_of_loose_eggs, $production, $no_of_damaged_eggs, $client_id, $id);
} else {
	$check_query = "SELECT id FROM supervisor_production_shead WHERE sheadNo = ? AND DATE(timestamp) = ? AND client_id = ?";
	$check_stmt = $conn->prepare($check_query);
	$check_stmt->bind_param("ssi", $sheadNo, $today, $client_id);
	$check_stmt->execute();
	$check_stmt->store_result();

	if ($check_stmt->num_rows > 0) {
		echo json_encode(["status" => "error", "message" => "Data already exists for Shead No: $sheadNo on $today"]);
		$check_stmt->close();
		$conn->close();
		exit;
	}
	$check_stmt->close();

	$sql = "INSERT INTO supervisor_production_shead 
			(sheadNo, no_of_trays, no_of_loose_eggs, production, no_of_damaged_eggs, timestamp, client_id) 
			VALUES (?, ?, ?, ?, ?, ?, ?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("siiiisi", $sheadNo, $no_of_trays, $no_of_loose_eggs, $production, $no_of_damaged_eggs, $timestamp, $client_id);
}

if ($stmt->execute()) {
	echo json_encode(["status" => "success", "message" => "Data stored successfully"]);
} else {
	echo json_encode(["status" => "error", "message" => "Query Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
