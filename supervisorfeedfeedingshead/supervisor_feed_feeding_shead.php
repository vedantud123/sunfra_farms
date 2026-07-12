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
    <title>Feed Shead Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }
        button, .add-data-btn {
            display: inline-block;
            background-color: #007BFF;
            color: #ffffff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin: 10px 0;
            border: none;
            cursor: pointer;
        }
        button:hover, .add-data-btn:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #007BFF;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .edit-link {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        .edit-link:hover {
            text-decoration: underline;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <button onclick="history.back()">Go Back</button>
        <div class="center">
            <a href="supervisor_feed_feeding_sheadNewEdit.php" class="add-data-btn">Add New Data</a>
        </div>

        <h1>Feed Shead Data</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Shead No</th>
                    <th>Box 1</th>
                    <th>Box 2</th>
                    <th>Box 3</th>
                    <th>Box 4</th>
                    <th>Box 5</th>
                    <th>Box 6</th>
                    <th>Box 7</th>
                    <th>Box 8</th>
                    <th>Box 9</th>
                    <th>Box 10</th>
                    <th>Date & Time</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
                $query = "SELECT * FROM supervisor_feed_feeding_shead ORDER BY id DESC";

                if ($result = $mysqli->query($query)) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . htmlspecialchars($row["id"]) . '</td>
                                <td>' . htmlspecialchars($row["sheadNo"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_1"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_2"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_3"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_4"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_5"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_6"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_7"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_8"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_9"]) . '</td>
                                <td>' . htmlspecialchars($row["Box_10"]) . '</td>
                                <td>' . htmlspecialchars($row["timestamp"]) . '</td>
                                <td><a href="supervisor_feed_feeding_sheadNewEdit.php?id=' . htmlspecialchars($row["id"]) . '" class="edit-link">Edit</a></td>
                              </tr>';
                    }
                    $result->free();
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
