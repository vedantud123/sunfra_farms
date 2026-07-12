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
    <title>WeighBridge Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .container {
            text-align: center;
            margin-top: 20px;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        a:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
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

	<button onclick="window.location.href='https://sunfra.com/farm/index.php'">Go Back</button>
    <div class="container">
        <a href="weighBridgeNewEdit.php">Add New Data</a>
    </div>
	<?php
	$allowedUsers = ['vedant', 'divya', 'venkat']; 
	
	if (isset($_SESSION['username']) && in_array($_SESSION['username'], $allowedUsers)) {
        echo '
        <div class="container">
            <a href="https://sunfra.com/farm/feednewstockloading/feed_new_stock_loading.php">Update The Material</a>
        </div>';
    }
	?>
	
    <?php 
    $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
    $query = "SELECT * FROM weighBridge  ORDER BY id DESC";

    echo '<table> 
          <thead>
              <tr> 
                  <th>ID</th> 
                  <th>Date</th> 
                  <th>Vehicle Number</th> 
                  <th>Materials</th> 
                  <th>Empty</th> 
                  <th>Gross</th> 
                  <th>Net</th> 
                  <th>Owner Name</th> 
                  <th>Type</th> 
                  <th>Driver Number</th> 
                  <th>Owner Number</th> 
                  <th>Details</th> 
                  <th>EDIT</th> 
              </tr>
          </thead>
          <tbody>';

    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr> 
                      <td>'.$row["id"].'</td> 
                      <td>'.$row["date"].'</td> 
                      <td>'.$row["vehicleNumber"].'</td> 
                      <td>'.$row["material"].'</td> 
                      <td>'.$row["empty"].'</td> 
                      <td>'.$row["gross"].'</td> 
                      <td>'.$row["net"].'</td> 
                      <td>'.$row["ownerName"].'</td> 
                      <td>'.$row["type"].'</td> 
                      <td>'.$row["driverNumber"].'</td> 
                      <td>'.$row["ownerNumber"].'</td> 
                      <td>'.$row["details"].'</td>
                      <td><a href="weighBridgeNewEdit.php?id='.$row["id"].'" class="edit-link">Edit</a></td> 
                  </tr>';
        }
        $result->free();
    }

    echo '</tbody></table>';
    ?>

</body>
</html>
