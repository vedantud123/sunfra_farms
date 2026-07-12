<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Raw Material</title>
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
            max-width: 1200px;
            margin: 30px auto;
        }
        button, a {
            display: inline-block;
            padding: 10px 15px;
            margin: 10px 0;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        button:hover, a:hover {
            background-color: #0056b3;
        }
        h1 {
            text-align: center;
            color: #007BFF;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
            height: 40px;
            border-right: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="https://sunfra.com/farm/feed_plant_supervisor.php" class="btn">Go Back</a>
        <h1>Feed Raw Material List</h1>

        <?php
        $allowedUsers = ['vedant', 'divya', 'venkat'];

        if (isset($_SESSION['username']) && in_array($_SESSION['username'], $allowedUsers)) {
            echo '
            <div class="add-data">
                <h1><a href="feed_raw_materialNewEdit.php">Add New Data</a></h1>
            </div>';
        }

        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

        if ($mysqli->connect_error) {
            die("Database connection failed: " . $mysqli->connect_error);
        }

        $material_day_logs = "
            SELECT material_name, SUM(reduced_quantity) AS total_reduction
            FROM `feed_material_reduction_logs`
            WHERE `timestamp` BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()
            GROUP BY material_name
        ";

        $material_day_logs_result = $mysqli->query($material_day_logs);

        if ($material_day_logs_result) {
            while ($row = $material_day_logs_result->fetch_assoc()) {
                $material_name = $row["material_name"];
                $total_reduction = $row["total_reduction"];
                $reduction_quantity_avg = ($total_reduction > 0) ? ($total_reduction / 7) : 1; 

                $update_day = "
                    UPDATE `feed_rawMaterial`
                    SET `days` = `stock` / $reduction_quantity_avg
                    WHERE `name` = '$material_name'
                ";

                if ($mysqli->query($update_day) === TRUE) {
                } else {
                }
            }
        } else {
            echo "Error in selecting material logs: " . $mysqli->error;
        }

        $material_day_logs_result->free();

        $query = "SELECT * FROM feed_rawMaterial order by type ASC";

        echo '<table> 
              <tr> 
                  <th>Id</th> 
                  <th>Name</th> 
                  <th>Stock</th> 
                  <th>Metric</th> 
                  <th>Type</th>
                  <th>Stock For Days</th>					
              </tr>';

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $field1name = $row["id"];
                $field2name = $row["name"];
                $field3name = $row["stock"];
                $field4name = $row["metric"];
                $field5name = $row["type"];
                $field6name = $row["days"];

                echo '<tr> 
                          <td>' . $field1name . '</td> 
                          <td>' . $field2name . '</td> 
                          <td>' . $field3name . '</td> 
                          <td>' . $field4name . '</td> 
                          <td>' . $field5name . '</td>
                          <td>' . $field6name . '</td>
                      </tr>';
            }
            $result->free();
        } else {
            echo "Error fetching raw material data: " . $mysqli->error;
        }

        $mysqli->close();
        ?>
        </table>
    </div>
</body>
</html>
