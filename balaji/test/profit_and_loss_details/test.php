<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$date = date('Y-m-d'); 
$shead_name = 'Shead_2';
$total_amount = 0;

$attendance_query = "SELECT * FROM `attendance` WHERE `date` = ? AND `working_place` = ?";
$attendance_stmt = $mysqli->prepare($attendance_query);
if (!$attendance_stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$attendance_stmt->bind_param("ss", $date, $shead_name);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();

while ($row = $attendance_result->fetch_assoc()) {
    $name = $row['name'];

    $supervisor_query = "SELECT * FROM `farm_supervisor` WHERE `name` = ?";
    $supervisor_stmt = $mysqli->prepare($supervisor_query);
    $supervisor_stmt->bind_param("s", $name);
    $supervisor_stmt->execute();
    $supervisor_result = $supervisor_stmt->get_result();

    if ($supervisor_result->num_rows == 0) {
        $salary_query = "SELECT salary FROM `labour_salaries` WHERE `name` = ?";
        $salary_stmt = $mysqli->prepare($salary_query);
        $salary_stmt->bind_param("s", $name);
        $salary_stmt->execute();
        $salary_result = $salary_stmt->get_result();

        if ($salary_row = $salary_result->fetch_assoc()) {
            $salary = $salary_row['salary']; 
            $total_amount += $salary;
        }
        $salary_stmt->close();
    } else {
        $count = 0;
        $check_number_of_locations = "SELECT COUNT(*) as total FROM `task_master` WHERE `assigned_date` = ? AND `person_name` = ?";
        $check_stmt = $mysqli->prepare($check_number_of_locations);
        $check_stmt->bind_param("ss", $date, $name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_row = $check_result->fetch_assoc()) {
            $count = $check_row['total'];
            echo "Supervisor $name is assigned to $count location(s) on $date.<br>";
        }
        $check_stmt->close();

        $salary_query = "SELECT salary FROM `labour_salaries` WHERE `name` = ?";
        $salary_stmt = $mysqli->prepare($salary_query);
        $salary_stmt->bind_param("s", $name);
        $salary_stmt->execute();
        $salary_result = $salary_stmt->get_result();

        if ($salary_row = $salary_result->fetch_assoc()) {
            $salary = ($count > 0) ? ($salary_row['salary'] / $count) : 0;
            $total_amount += $salary;
        }
        $salary_stmt->close();
    }

    $supervisor_stmt->close();
}

$attendance_stmt->close();
$mysqli->close();

echo "<br><strong>Total Salary Amount (including supervisor's share) for $shead_name on $date:</strong> ₹" . number_format($total_amount, 2);
?>
