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
    <title>Supervisor Water Shead</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            width: 90%;
            margin: 20px auto;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #0056b3;
        }
        .add-button {
            text-align: center;
            margin: 20px 0;
        }
        .add-button a {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .add-button a:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        table th {
            background-color: #007BFF;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        table tr:hover {
            background-color: #e9ecef;
        }
        .edit-link {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        .edit-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
		<button class="button" onclick="window.location.href='https://sunfra.com/farm/supervisor.php'">Go Back</button>
        <div class="add-button">
            <a href="supervisor_water_sheadNewEdit.php">Add New Data</a>
        </div>

        <?php
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        $query = "SELECT * FROM supervisor_water_shead ORDER BY id DESC";

        echo '<table>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Flushed Out Open Reading</th>
                <th>Flushed Out Closing Reading</th>
                <th>Day End Closing Reading</th>
                <th>Date & Time</th>
                <th>Edit</th>
            </tr>';

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $field1name = $row["id"];
                $field2name = $row["date"];
                $field3name = $row["flushed_out_open_reading"];
                $field4name = $row["flushed_out_closing_reading"];
                $field5name = $row["day_end_closing_reading"];
                $field6name = $row["timestamp"];

                echo '<tr>
                    <td>' . $field1name . '</td>
                    <td>' . $field2name . '</td>
                    <td>' . $field3name . '</td>
                    <td>' . $field4name . '</td>
                    <td>' . $field5name . '</td>
                    <td>' . $field6name . '</td>
                    <td><a class="edit-link" href="supervisor_water_sheadNewEdit.php?id=' . $field1name . '">Edit</a></td>
                </tr>';
            }
            $result->free();
        }
        ?>
        </table>
    </div>
</body>
</html>
