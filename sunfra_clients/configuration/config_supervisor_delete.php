<?php
	$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
	if (!$conn) { die("❌ DB connection failed: " . mysqli_connect_error()); }

	if (!isset($_POST['id'])) { echo "❌ Missing ID"; exit; }

	$id = intval($_POST['id']);
	$sql = "DELETE FROM config_feature WHERE id = $id";

	if (mysqli_query($conn, $sql)) {
		echo "✅ Supervisor removed successfully.";
	} else {
		echo "❌ Error removing supervisor.";
	}

	mysqli_close($conn);
?>
