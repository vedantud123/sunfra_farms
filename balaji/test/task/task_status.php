<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$display_query = "
    SELECT * 
    FROM farm_task_list_logs 
    WHERE client_id = ?
      AND (date = ? OR (date = ? AND status IS NULL))
    ORDER BY date, time";



$stmt_display = $conn->prepare($display_query);
$stmt_display->bind_param("iss", $client_id, $today, $yesterday);
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

        th {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

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

        .done-btn:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .done-btn:disabled {
            background: gray;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar { transition: all 0.3s ease; }
    .sidebar a:hover { background-color: #2b6cb0; color: white; }
    .collapsed { width: 64px !important; }
    .collapsed .sidebar-text { display: none; }
    .collapsed nav a { justify-content: center; }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .sidebar.show { display: block; }
    }
  </style>
</head>
<?php session_start(); ?>
<?php $clientName = $_SESSION['client_name'] ?? 'Yours'; ?>
<body class="bg-gray-100 text-gray-800">

<h2>Farm Task List Logs</h2>
<!-- Back and Add Task Buttons -->
<div style="margin-bottom: 20px; text-align: center;">
    <button onclick="window.location.href='https://sunfra.com/farm/test/test_dashboard.php';"
            style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
        ← Back
    </button>

    <button onclick="window.location.href='https://sunfra.com/farm/test/task/task_list.php';"
            style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
        + Add Task
    </button>
</div>

<?php
$location_order = ["Gate_Manager", "Shead 1", "Shead 2", "Shead 3", "Shead 4", "Shead 5", "Shead 6", "Shead 7", "Shead 8", "Chick", "Grower", "Egg_Godown", "Feed_Godown", "Tractor_production_mortality"];

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
                        .css({ "background": "gray", "cursor": "not-allowed" });
                } else {
                    alert("Error updating status. Please try again.");
                }
            }
        });
    });
});
</script>


<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
<?php $conn->close(); ?>
