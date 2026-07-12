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
    <title>Insert Page</title>
</head>

<body>
<button onclick="history.back()">Go Back</button>
<center>
    <?php
		$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$id = $_REQUEST['id'] ?? 0;
		$sheadNo = strtolower(trim($_REQUEST['sheadNo']));
		$noOfBirds = intval($_REQUEST['noOfBirds']);
		$date = date('Y-m-d');
		$timestamp = date('Y-m-d H:i:s');

		if ($id >= 1) {
			$sql = "UPDATE supervisor_shead_mortality SET sheadNo = ?, noOfBirds = ? WHERE id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("sii", $sheadNo, $noOfBirds, $id);
		} else {
			$check_query = "SELECT id FROM supervisor_shead_mortality WHERE sheadNo = ? AND date = ?";
			$check_stmt = $conn->prepare($check_query);
			$check_stmt->bind_param("ss", $sheadNo, $date);
			$check_stmt->execute();
			$check_stmt->store_result();

			if ($check_stmt->num_rows > 0) {
				echo "<h3 style='color:red;'>Mortality data already exists for Shead No: $sheadNo on $date</h3>";
				$check_stmt->close();
				$conn->close();
				exit;
			}
			$check_stmt->close();

			$sql = "INSERT INTO supervisor_shead_mortality (sheadNo, noOfBirds, date, timestamp) 
					VALUES (?, ?, ?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("siss", $sheadNo, $noOfBirds, $date, $timestamp);
		}

		if ($stmt->execute()) {
			echo "<h3>Data stored successfully</h3>";
		} else {
			echo "ERROR: Could not execute query: " . $stmt->error;
		}

		$stmt->close();

		if ($id == 0) {
			if (preg_match("/^(chick|grower)$/", $sheadNo)) {
				$update_query = "UPDATE batch SET live_birds = live_birds - ? WHERE sheadNo = ? AND cullDate = '0000-00-00'";
				$stmt = $conn->prepare($update_query);
				$stmt->bind_param("is", $noOfBirds, $sheadNo);
			} else {
				$number = preg_replace("/[^0-9]/", "", $sheadNo);
				$update_query = "UPDATE batch SET live_birds = live_birds - ? WHERE sheadNo = ? AND cullDate = '0000-00-00'";
				$stmt = $conn->prepare($update_query);
				$stmt->bind_param("is", $noOfBirds, $number);
			}

			if ($stmt->execute()) {
				echo "<h3>Batch updated successfully</h3>";
			} else {
				echo "ERROR: Could not update batch: " . $stmt->error;
			}

			$stmt->close();
		}

		$conn->close();
	?>

</center>

<form action="supervisor_shead_mortality.php" method="post">
    <div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
        <div style="display: inline-flex; gap: 0px; align-items: center;">
            <input type="submit" value="Done">
        </div>
    </div>
</form>
</body>
</html>
