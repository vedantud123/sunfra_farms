<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Day Log</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .go-back {
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
	<button class="back-button"  onclick="window.location.href='https://sunfra.com/farm/index.php';">Go Back</button>
    <div style="text-align: center;">
        <a href="daylogNewEdit.php" class="button">Add New Data</a>
    </div>

    <?php
    $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $date = date("Y-m-d");
    $query = "SELECT * FROM dayLog WHERE date='$date' ORDER BY sheadNo DESC";

    echo '<table>
        <tr>
            <th>ID</th>
            <th>Shead No</th>
            <th>Batch ID</th>
            <th>Date</th>
            <th>Feed</th>
            <th>Water</th>
            <th>Mortality</th>
            <th>Live Birds</th>
            <th>Eggs Total</th>
            <th>Eggs Damaged</th>
            <th>Production (%)</th>
            <th>Egg Weight</th>
            <th>Edit</th>
        </tr>';

    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . $row["id"] . '</td>
                    <td>' . $row["sheadNo"] . '</td>
                    <td>' . $row["batchId"] . '</td>
                    <td>' . $row["date"] . '</td>
                    <td>' . $row["feed"] . '</td>
                    <td>' . $row["water"] . '</td>
                    <td>' . $row["mortality"] . '</td>
                    <td>' . $row["liveBirds"] . '</td>
                    <td>' . $row["eggsTotal"] . '</td>
                    <td>' . $row["eggsDamaged"] . '</td>
                    <td>' . $row["productionPercentage"] . '</td>
                    <td>' . $row["eggWeight"] . '</td>
                    <td><a href="daylogNewEdit.php?id=' . $row["id"] . '" class="button">Edit</a></td>
                </tr>';
        }
        $result->free();
    } else {
        echo '<tr><td colspan="13">No records found.</td></tr>';
    }

    $mysqli->close();
    ?>
</div>

</body>
</html>
