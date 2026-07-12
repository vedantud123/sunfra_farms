<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
date_default_timezone_set('Asia/Kolkata');

$username = $_SESSION['username'] ?? 'Guest';
$client_id = $_SESSION['client_id'] ?? 0;

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$date = date('Y-m-d');

$sql_fetch = "SELECT * FROM farm_task_list_logs WHERE date = ? AND client_id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("si", $date, $client_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();

$today = date('l');

if ($result->num_rows == 0) {
    $sql_take_data = "SELECT * FROM farm_task_list WHERE (repetation = 'Daily' OR repetation = ?) AND client_id = ?";
    $stmt = $conn->prepare($sql_take_data);
    $stmt->bind_param("si", $today, $client_id);
    $stmt->execute();
    $take_data_result = $stmt->get_result();

    if ($take_data_result->num_rows > 0) {
        $sql_insert = "INSERT INTO farm_task_list_logs 
                       (task_name, task_location, time, repetation, status, date, client_id) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        while ($row = $take_data_result->fetch_assoc()) {
            $stmt_insert->bind_param("ssssssi",
                $row['task_name'],
                $row['task_location'],
                $row['time'],
                $row['repetation'],
                $row['status'],
                $date,
                $client_id
            );
            $stmt_insert->execute();
        }
        $stmt_insert->close();
    }
}

function convertSheadNames($name) {
    return preg_replace('/Shead_(\d+)/', 'Shead $1', $name);
}

if ($result->num_rows > 0) {
    $sql_user = "SELECT * FROM task_master WHERE assigned_date = ? AND client_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("si", $date, $client_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $stmt_user->close();

    if ($user_result->num_rows > 0) {
        while ($row = $user_result->fetch_assoc()) {
            $location = $row['location'];
            $person_name = $row['person_name'];
            $converted_location = convertSheadNames($location);

            $sql_user_name = "SELECT user_name FROM farm_supervisor WHERE NAME = ? AND client_id = ?";
            $stmt_user_name = $conn->prepare($sql_user_name);
            $stmt_user_name->bind_param("si", $person_name, $client_id);
            $stmt_user_name->execute();
            $stmt_user_name->bind_result($user_name);
            $stmt_user_name->fetch();
            $stmt_user_name->close();

            if (!empty($user_name)) {
                $sql_update = "UPDATE farm_task_list_logs 
                               SET assigned_to = ? 
                               WHERE task_location = ? AND date = ? AND client_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sssi", $user_name, $converted_location, $date, $client_id);
                $stmt_update->execute();
                $stmt_update->close();
            }
        }
    }
}

$display_query = "SELECT * FROM farm_task_list_logs 
                  WHERE assigned_to = ? AND date = ? AND client_id = ? 
                  ORDER BY time";
$stmt_display = $conn->prepare($display_query);
$stmt_display->bind_param("ssi", $username, $date, $client_id);
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
        /* General Page Styling */
		body {
			font-family: 'Poppins', sans-serif;
			background-color: #f0f2f5;
			margin: 0;
			padding: 10px;
			text-align: center;
		}

		/* Heading */
		h2 {
			color: #333;
			font-size: 24px;
			margin-bottom: 20px;
		}

		/* Table Styling */
		table {
			width: 90%;
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

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Location</th>
                <th>Task Name</th>
                <th>Time</th>
                <th>Repetation</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($display_result->num_rows > 0) {
                while ($row = $display_result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['date']}</td>
                            <td>{$row['task_location']}</td>
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
            } else {
                echo "<tr><td colspan='8' style='text-align:center;'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
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
