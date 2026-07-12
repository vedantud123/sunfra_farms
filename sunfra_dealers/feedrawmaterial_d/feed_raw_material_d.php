<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login_d/login_d.php");
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
            padding: 8px 12px; /* Reduced padding for smaller rows */
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 14px; /* Adjusted font size */
            height: 40px; /* Fixed row height */
            border-right: 1px solid #ddd; /* Added vertical line between columns */
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
        tr:last-child td, tr:last-child th {
            border-bottom: none; /* Remove bottom border for the last row */
        }
        table td:last-child, table th:last-child {
            border-right: none; /* Remove the right border for the last column */
        }
        .add-new {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
		<a href="https://sunfra.com/farm/sunfra_dealers/index_d.php" class="btn">Go Back</a>
        <h1>Feed Raw Material List</h1>

        <?php 
		

			echo '
			<div class="add-data">
				<h1><a href="feed_raw_materialNewEdit_d.php">Add New Data</a></h1>
			</div>';
	
		
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_yugandhar_pf"); 
        $date = date("Y-m-d");
        $query = "SELECT * FROM feed_rawMaterial";

        echo '<table> 
              <tr> 
                  <th>Id</th> 
                  <th>Name</th> 
                  <th>Stock</th> 
                  <th>Metric</th> 
                  <th>Type</th> 
              </tr>';

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $field1name = $row["id"];
                $field2name = $row["name"];
                $field3name = $row["stock"];
                $field4name = $row["metric"];
                $field5name = $row["type"];

                echo '<tr> 
                          <td>'.$field1name.'</td> 
                          <td>'.$field2name.'</td> 
                          <td>'.$field3name.'</td> 
                          <td>'.$field4name.'</td> 
                          <td>'.$field5name.'</td>
                      </tr>';
            }
            $result->free();
        } 
        ?>
        </table>
    </div>
</body>
</html>
