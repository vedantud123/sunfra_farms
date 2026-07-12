<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
date_default_timezone_set('Asia/Kolkata');

?>

<!DOCTYPE html>
<html>

<head>
    <title>Insert Page page</title>
</head>

<body>
<button onclick="history.back()">Go Back</button>
    <center>
        <?php
			$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

			if ($conn === false) {
				die("ERROR: Could not connect. " . mysqli_connect_error());
			}

			$id = $_REQUEST['id'] ?? 0;
			$sheadNo = trim($_REQUEST['sheadNo']);
			$no_of_trays = intval($_REQUEST['no_of_trays']);
			$no_of_loose_eggs = intval($_REQUEST['no_of_loose_eggs']);
			$no_of_damaged_eggs = intval($_REQUEST['no_of_damaged_eggs']);
			$production = ($no_of_trays * 30) + $no_of_loose_eggs;
			$timestamp = date('Y-m-d H:i:s');
			$today = date('Y-m-d');

			if ($id >= 1) {
				$sql = "UPDATE supervisor_production_shead 
						SET sheadNo = ?, no_of_trays = ?, no_of_loose_eggs = ?, production = ?, no_of_damaged_eggs = ? 
						WHERE id = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("siiiii", $sheadNo, $no_of_trays, $no_of_loose_eggs, $production, $no_of_damaged_eggs, $id);
			} else {
				$check_query = "SELECT id FROM supervisor_production_shead WHERE sheadNo = ? AND DATE(timestamp) = ?";
				$check_stmt = $conn->prepare($check_query);
				$check_stmt->bind_param("ss", $sheadNo, $today);
				$check_stmt->execute();
				$check_stmt->store_result();

				if ($check_stmt->num_rows > 0) {
					echo "<h3 style='color:red;'>Data already exists for Shead No: $sheadNo on $today</h3>";
					$check_stmt->close();
					$conn->close();
					exit; 
				}
				$check_stmt->close();

				$sql = "INSERT INTO supervisor_production_shead (sheadNo, no_of_trays, no_of_loose_eggs, production, no_of_damaged_eggs, timestamp) 
						VALUES (?, ?, ?, ?, ?, ?)";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("siiiis", $sheadNo, $no_of_trays, $no_of_loose_eggs, $production, $no_of_damaged_eggs, $timestamp);
			}

			if ($stmt->execute()) {
				echo "<h3>Data stored successfully</h3>";
			} else {
				echo "ERROR: Could not execute query: " . $stmt->error;
			}

			$stmt->close();
			$conn->close();
		?>

    </center>
	<form action="supervisor_production_shead.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="supervisor_production_shead.php"></a>
    </div>
	</div>
	</form>
</body>

</html>