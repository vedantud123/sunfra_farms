<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$client_id = $_SESSION['client_id'] ?? 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Insert Page</title>
</head>

<body>
    <button onclick="window.location.href='https://sunfra.com/farm/test/attendance/labour_attendance.php';">Go Back</button>
    <center>
        <?php
        $conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $id = $_REQUEST['id'] ?? 0;
        $name = $_REQUEST['name'] ?? '';
        $status = $_REQUEST['status'] ?? '';
        $working_place = $_REQUEST['working_place'] ?? '';
        $date = date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');

        if ($id >= 1) {
            $sql = "UPDATE attendance SET name=?, status=?, date=?, working_place=?, client_id=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $name, $status, $date, $working_place, $client_id, $id);
        } else {
            $sql = "INSERT INTO attendance (name, status, date, working_place, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $name, $status, $date, $working_place, $timestamp, $client_id);
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

    <form action="labour_attendance.php" method="post">
        <div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
            <div style="display: inline-flex; gap: 0px; align-items: center;">
                <input type="submit" value="Done">
            </div>
        </div>
    </form>
</body>

</html>
