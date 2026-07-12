<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$display_query = "
    SELECT * 
    FROM farm_task_list_logs 
    WHERE (date = ?) 
       OR (date = ? AND status IS NULL)
    ORDER BY date, time";

$stmt_display = $conn->prepare($display_query);
$stmt_display->bind_param("ss", $today, $yesterday);
$stmt_display->execute();
$display_result = $stmt_display->get_result();
$stmt_display->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
		body {
			font-family: 'Poppins', sans-serif;
			background-color: #f0f2f5;
			margin: 0;
			padding: 6px;
			text-align: center;
		}

		h2 {
			color: #333;
			font-size: 20px;
			margin-bottom: 8px;
		}

		table {
			width: 60%;
			margin: 0 auto;
			border-collapse: collapse;
			background: #ffffff;
			box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
			border-radius: 10px;
			overflow: hidden;
		}

		/* Table Header */
		th {
			background: #007bff;
			color: white;
			padding: 15px;
			text-align: center;
			font-size: 16px;
		}

		/* Table Rows */
		td {
			padding: 12px;
			border-bottom: 1px solid #ddd;
			font-size: 14px;
		}

		/* Alternating Row Colors */
		tr:nth-child(even) {
			background: #f9f9f9;
		}

		/* Hover Effect on Rows */
		tr:hover {
			background: #f1f5ff;
			transition: 0.3s;
		}

		.done-btn {
			padding: 8px 16px;
			font-size: 14px;
			font-weight: bold;
			background: #28a745;
			color: white;
			border: none;
			cursor: pointer;
			border-radius: 5px;
			transition: 0.3s ease-in-out;
		}

		/* Button Hover Effect */
		.done-btn:hover {
			background: #218838;
			transform: scale(1.05);
		}

		/* Disabled Button Styling */
		.done-btn:disabled {
			background: gray;
			cursor: not-allowed;
			opacity: 0.6;
		}
    </style>
</head>
<body>

    <h2>Farm Task List Logs</h2>

   <?php
		$location_order = ["Gate_Manager", "Shead 1", "Shead 2", "Shead 3", "Shead 4", "Shead 5", "Shead 6", "Shead 7", "Shead 8","Chick","Grower", "Egg_Godown", "Feed_Godown","Tractor_production_mortality"];

		$tasks_by_location = [];

		if ($display_result->num_rows > 0) {
			while ($row = $display_result->fetch_assoc()) {
				$tasks_by_location[$row['task_location']][] = $row;
			}
		}

		foreach ($location_order as $location) {
			if (isset($tasks_by_location[$location])) { 
				echo "<h2 style='margin-top:20px;'>$location</h2>"; 

				echo "<table border='1' width='100%'>
						<thead>
							<tr>
								<th>Date</th>
								<th>Updated Date And Time</th>
								<th>Assigned To</th>								
								<th>Task Name</th>
								<th>Time</th>
								<th>Repetation</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>";

				foreach ($tasks_by_location[$location] as $row) {
					echo "<tr>
							<td>{$row['date']}</td>
							<td>{$row['timestamp']}</td>
							<td>{$row['assigned_to']}</td>
							<td>{$row['task_name']}</td>
							<td>{$row['time']}</td>
							<td>{$row['repetation']}</td>
							<td id='status_{$row['id']}'>{$row['status']}</td>
							<td>
								<button class='done-btn' data-id='{$row['id']}' 
									".($row['status'] == 'Done' ? "disabled style='background:gray; cursor:not-allowed;'" : "").">
									".($row['status'] == 'Done' ? "Completed" : "Done")."
								</button>
							</td>
						</tr>";
				}

				echo "</tbody></table>"; 
			}
		}

		foreach ($tasks_by_location as $location => $tasks) {
			if (!in_array($location, $location_order)) {
				echo "<h2 style='margin-top:20px;'>$location</h2>";
				echo "<table border='1' width='100%'>
						<thead>
							<tr>
								<th>Date</th>
								<th>Assigned To</th>																
								<th>Task Name</th>
								<th>Time</th>
								<th>Repetation</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>";

				foreach ($tasks as $row) {
					echo "<tr>
							<td>{$row['date']}</td>
							<td>{$row['assigned_to']}</td>								
							<td>{$row['task_name']}</td>
							<td>{$row['time']}</td>
							<td>{$row['repetation']}</td>
							<td id='status_{$row['id']}'>{$row['status']}</td>
							<td>
								<button class='done-btn' data-id='{$row['id']}' 
									".($row['status'] == 'Done' ? "disabled style='background:gray; cursor:not-allowed;'" : "").">
									".($row['status'] == 'Done' ? "Completed" : "Done")."
								</button>
							</td>
						</tr>";
				}

				echo "</tbody></table>";
			}
		}
	?>


	<script>
	$(document).ready(function () {
		$(".done-btn").click(function () {
			var taskId = $(this).data("id");
			var button = $(this);
			
			$.ajax({
				url: "update_status.php",
				type: "POST",
				data: { id: taskId },
				success: function (response) {
					if (response == "success") {
						$("#status_" + taskId).text("Done");
						button.text("Completed").prop("disabled", true)
							  .css({"background": "gray", "cursor": "not-allowed"});
					} else {
						alert("Error updating status. Please try again.");
					}
				}
			});
		});
	});
	</script>

</body>
</html>
<?php $conn->close(); ?>




