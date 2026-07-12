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
			$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

			if ($conn->connect_error) {
				die("ERROR: Could not connect. " . $conn->connect_error);
			}

			$id = $_REQUEST['id'] ?? null;
			$sheadNo = $_REQUEST['sheadNo'] ?? null;
			$Box_1 = $_REQUEST['Box_1'] ?? 0;
			$Box_2 = $_REQUEST['Box_2'] ?? 0;
			$Box_3 = $_REQUEST['Box_3'] ?? 0;
			$Box_4 = $_REQUEST['Box_4'] ?? 0;
			$Box_5 = $_REQUEST['Box_5'] ?? 0;
			$Box_6 = $_REQUEST['Box_6'] ?? 0;
			$Box_7 = $_REQUEST['Box_7'] ?? 0;
			$Box_8 = $_REQUEST['Box_8'] ?? 0;
			$Box_9 = $_REQUEST['Box_9'] ?? 0;
			$Box_10 = $_REQUEST['Box_10'] ?? 0;

			$date = date('Y-m-d');
			$timestamp = date('Y-m-d H:i:s');

			if (!empty($id)) {
				$sql = "UPDATE supervisor_feed_feeding_shead 
						SET sheadNo = ?, Box_1 = ?, Box_2 = ?, Box_3 = ?, Box_4 = ?, Box_5 = ?, Box_6 = ?, 
							Box_7 = ?, Box_8 = ?, Box_9 = ?, Box_10 = ?
						WHERE id = ?";

				$stmt = $conn->prepare($sql);
				$stmt->bind_param("sddddddddddi", $sheadNo, $Box_1, $Box_2, $Box_3, $Box_4, $Box_5, $Box_6, 
											   $Box_7, $Box_8, $Box_9, $Box_10, $id);
			} else {
				$check_query = "SELECT COUNT(*) FROM supervisor_feed_feeding_shead WHERE DATE(timestamp) = ? AND sheadNo = ?";
				$check_stmt = $conn->prepare($check_query);
				$check_stmt->bind_param("ss", $date, $sheadNo);
				$check_stmt->execute();
				$check_stmt->bind_result($count);
				$check_stmt->fetch();
				$check_stmt->close();

				if ($count > 0) {
					echo "<h3 style='color:red;'>Data already exists for today in Shead: $sheadNo</h3>";
					exit;
				}

				$sql = "INSERT INTO supervisor_feed_feeding_shead 
						(sheadNo, Box_1, Box_2, Box_3, Box_4, Box_5, Box_6, Box_7, Box_8, Box_9, Box_10, timestamp) 
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

				$stmt = $conn->prepare($sql);
				$stmt->bind_param("sdddddddddds", $sheadNo, $Box_1, $Box_2, $Box_3, $Box_4, $Box_5, $Box_6, 
											   $Box_7, $Box_8, $Box_9, $Box_10, $timestamp);
			}

			if ($stmt->execute()) {
				echo "<h3>Data stored successfully</h3>";
			} else {
				echo "ERROR: " . $stmt->error;
			}

			$stmt->close();
			$conn->close();
		?>

    </center>
	<form action="supervisor_feed_feeding_shead.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="supervisor_feed_feeding_shead.php"></a>
    </div>
	</div>
	</form>
</body>

</html>