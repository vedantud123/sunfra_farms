<?php
$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if (!$conn) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

date_default_timezone_set('Asia/Kolkata');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = mysqli_real_escape_string($conn, $_POST['name']);
$status = mysqli_real_escape_string($conn, $_POST['status']);
$working_place = mysqli_real_escape_string($conn, $_POST['working_place']);
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;

$date = date('Y-m-d');
$timestamp = date('Y-m-d H:i:s');

if ($id > 0) {
    $sql = "UPDATE attendance SET 
                name='$name', 
                status='$status', 
                date='$date', 
                working_place='$working_place' 
            WHERE id=$id";
} else {
    $sql = "INSERT INTO attendance (name, status, date, working_place, timestamp, client_id) 
            VALUES ('$name', '$status', '$date', '$working_place', '$timestamp', '$client_id')";
}

if (mysqli_query($conn, $sql)) {
    mysqli_close($conn);
    header("Location: https://sunfra.com/farm/sunfra/attendance/labour_attendance_json_to_web.php");
    exit;
}

mysqli_close($conn);
?>
