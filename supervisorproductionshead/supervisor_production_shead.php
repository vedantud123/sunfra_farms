<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Production Shead</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd; /* Adds borders to columns */
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .edit-link {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }

        .edit-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
	<button class="button" onclick="window.location.href='https://sunfra.com/farm/supervisor.php'">Go Back</button>
    <a href="supervisor_production_sheadNewEdit.php" class="button">Add New Data</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Shead No</th>
                <th>No Of Trays</th>
                <th>No of Loose Eggs</th>
                <th>Production</th>
                <th>Total No Of Damaged Eggs</th>
                <th>Date & Time</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
            $query = "SELECT * FROM supervisor_production_shead ORDER BY id DESC";

            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row["id"] . '</td>';
                    echo '<td>' . $row["sheadNo"] . '</td>';
                    echo '<td>' . $row["no_of_trays"] . '</td>';
                    echo '<td>' . $row["no_of_loose_eggs"] . '</td>';
                    echo '<td>' . $row["production"] . '</td>';
                    echo '<td>' . $row["no_of_damaged_eggs"] . '</td>';
                    echo '<td>' . $row["timestamp"] . '</td>';
                    echo '<td><a class="edit-link" href="supervisor_production_sheadNewEdit.php?id=' . $row["id"] . '">Edit</a></td>';
                    echo '</tr>';
                }
                $result->free();
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
