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
    <title>Supervisor Shead Mortality</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .back-button, .add-new-button {
            display: inline-block;
            padding: 10px 20px;
            margin-bottom: 20px;
            color: #fff;
            background-color: #007BFF;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .back-button:hover, .add-new-button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #007BFF;
            color: #fff;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .edit-link {
            padding: 5px 10px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .edit-link:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="https://sunfra.com/farm/supervisor.php" class="back-button">Go Back</a>
        <a href="supervisor_shead_mortalityNewEdit.php" class="add-new-button">Add New Data</a>

        <?php
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        $query = "SELECT * FROM supervisor_shead_mortality ORDER BY id DESC";

        echo '<table> 
            <thead>
                <tr> 
                    <th>ID</th> 
                    <th>Date</th> 
                    <th>Shead No</th> 
                    <th>Number of Birds</th> 
                    <th>Date & Time</th>
                    <th>Edit</th> 
                </tr>
            </thead>
            <tbody>';

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $field1name = $row["id"];
                $field2name = $row["date"];
                $field3name = $row["sheadNo"];
                $field4name = $row["noOfBirds"];
                $field5name = $row["timestamp"];

                echo '<tr> 
                        <td>' . $field1name . '</td> 
                        <td>' . $field2name . '</td> 
                        <td>' . $field3name . '</td> 
                        <td>' . $field4name . '</td> 
                        <td>' . $field5name . '</td> 
                        <td><a href="supervisor_shead_mortalityNewEdit.php?id=' . $field1name . '" class="edit-link">Edit</a></td> 
                    </tr>';
            }
            $result->free();
        }

        echo '</tbody></table>';
        ?>
    </div>
</body>
</html>
