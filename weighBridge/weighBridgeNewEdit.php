<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$id = $date = $vehicleNumber = $material = $empty = $gross = $net = $ownerName = $type = $driverNumber = $ownerNumber = $details = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $date = $_POST['date'];
    $vehicleNumber = $_POST['vehicleNumber'];
    $material = $_POST['material'];
    $empty = $_POST['empty'];
    $gross = $_POST['gross'];
    $net = $_POST['net'];
    $ownerName = $_POST['ownerName'];
    $type = $_POST['type'];
    $driverNumber = $_POST['driverNumber'];
    $ownerNumber = $_POST['ownerNumber'];
    $details = $_POST['details'];
    $timestamp = date('Y-m-d H:i:s');
  
    $check_sql = "SELECT id FROM weighBridge WHERE id='$id'";
    $result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        $sql = "UPDATE weighBridge SET 
            date='$date',
            vehicleNumber='$vehicleNumber',
            material='$material',
            empty='$empty',
            gross='$gross',
            net='$net',
            ownerName='$ownerName',
            type='$type',
            driverNumber='$driverNumber',
            ownerNumber='$ownerNumber',
            details='$details',
            timestamp='$timestamp'
            WHERE id='$id'";
    } else {
        $sql = "INSERT INTO weighBridge (id, date, vehicleNumber, material, empty, gross, net, ownerName, type, driverNumber, ownerNumber, details, timestamp) 
            VALUES ('$id', '$date', '$vehicleNumber', '$material', '$empty', '$gross', '$net', '$ownerName', '$type', '$driverNumber', '$ownerNumber', '$details', '$timestamp')";
    }

     if (mysqli_query($conn, $sql)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM weighBridge WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $date = $row["date"];
        $vehicleNumber = $row["vehicleNumber"];
        $material = $row["material"];
        $empty = $row["empty"];
        $gross = $row["gross"];
        $net = $row["net"];
        $ownerName = $row["ownerName"];
        $type = $row["type"];
        $driverNumber = $row["driverNumber"];
        $ownerNumber = $row["ownerNumber"];
        $details = $row["details"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Weighbridge Entry</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            padding: 0;
            margin: 0;
        }
        .container {
            width: 80%;
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
        }
        .form-group {
            margin: 12px 0;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            margin-top: 4px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .form-group input[type="submit"] {
            background: #28a745;
            color: #fff;
            border: none;
            margin-top: 20px;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background: #218838;
        }
        .back-btn {
            margin-top: 10px;
            text-align: center;
        }
        .back-btn button {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-btn button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
	<div class="back-btn">
		<button onclick="window.location.href='https://sunfra.com/farm/weighBridge/weighBridge.php'">Go Back</button>
	</div>
    <div class="container">
        <h1>Weighbridge Entry Form</h1>
		
        <form action="" method="post">
            <div class="form-group">
                <label for="id">ID (Enter New or Existing):</label>
                <input type="text" name="id" id="id" value="<?= $id ?>" required>
            </div>
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" name="date" id="date" value="<?= $date ?>">
            </div>
            <div class="form-group">
                <label for="vehicleNumber">Vehicle Number:</label>
                <input type="text" name="vehicleNumber" id="vehicleNumber" value="<?= $vehicleNumber ?>">
            </div>
            <div class="form-group">
                <label for="material">Material:</label>
                <input type="text" name="material" id="material" value="<?= $material ?>">
            </div>
            <div class="form-group">
                <label for="empty">Empty:</label>
                <input type="text" name="empty" id="empty" value="<?= $empty ?>">
            </div>
            <div class="form-group">
                <label for="gross">Gross:</label>
                <input type="text" name="gross" id="gross" value="<?= $gross ?>">
            </div>
            <div class="form-group">
                <label for="net">Net:</label>
                <input type="text" name="net" id="net" value="<?= $net ?>">
            </div>
            <div class="form-group">
                <label for="ownerName">Owner Name:</label>
                <input type="text" name="ownerName" id="ownerName" value="<?= $ownerName ?>">
            </div>
            <div class="form-group">
                <label for="type">Type:</label>
                <input type="text" name="type" id="type" value="<?= $type ?>">
            </div>
            <div class="form-group">
                <label for="driverNumber">Driver Number:</label>
                <input type="text" name="driverNumber" id="driverNumber" value="<?= $driverNumber ?>">
            </div>
            <div class="form-group">
                <label for="ownerNumber">Owner Number:</label>
                <input type="text" name="ownerNumber" id="ownerNumber" value="<?= $ownerNumber ?>">
            </div>
            <div class="form-group">
                <label for="details">Details:</label>
                <input type="text" name="details" id="details" value="<?= $details ?>">
            </div>
            <div class="form-group">
                <input type="submit" value="Submit">
            </div>
        </form>
    </div>
</body>
</html>
