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
    <title>Tractor Production Mortality</title>
    <style>
        /* Reset body and table styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        /* Center button and add margin */
        button {
            margin: 20px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        /* Add some styling for the link that navigates to the 'Add New Data' page */
        div a {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }

        div a:hover {
            background-color: #0056b3;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th {
            padding: 12px;
            text-align: left;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            text-transform: uppercase;
        }

        td {
            padding: 10px;
            text-align: left;
            font-size: 14px;
            color: #333;
        }

        /* Hover effect for table rows */
        tr:hover {
            background-color: #f1f1f1;
        }

        /* Styling for the edit link */
        a {
            color: #007bff;
            font-weight: bold;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Add alternating row colors for better readability */
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* Add a dark border to header */
        th {
            border-top: 2px solid #004085;
            border-bottom: 2px solid #004085;
        }
    </style>
</head>
<body>
    <button onclick="history.back()">Go Back</button>
    <div style="text-align: center; margin-top: 20px;">
        <a href="Tractor_production_mortalityNewEdit.php" style="padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 10px; font-weight: bold;">Add New Data</a>
    </div>

<?php 
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
$date = date("Y-m-d");
$query = "SELECT * FROM tractor_production_mortality ORDER BY id DESC";

echo '<table> 
      <tr> 
          <th>ID</th> 
          <th>Date</th> 
          <th>Shead_No</th> 
          <th>Production</th> 
          <th>Egg_Trays</th> 
          <th>Loose_Eggs</th> 
          <th>Mortality</th> 
          <th>Batch_Id</th> 
          <th>Edit</th> 
      </tr>';

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $field1name = $row["id"];
        $field2name = $row["date"];
        $field3name = $row["sheadNo"];
        $field4name = $row["production"]; 
        $field5name = $row["eggTrays"];
        $field6name = $row["looseEggs"]; 
        $field7name = $row["mortality"]; 
        $field8name = $row["batch_id"];

        echo '<tr> 
                  <td>'.$field1name.'</td> 
                  <td>'.$field2name.'</td> 
                  <td>'.$field3name.'</td> 
                  <td>'.$field4name.'</td> 
                  <td>'.$field5name.'</td> 
                  <td>'.$field6name.'</td>
                  <td>'.$field7name.'</td> 
                  <td>'.$field8name.'</td> 
                  <td><a href="Tractor_production_mortalityNewEdit.php?id='.$field1name.'">Edit</a></td> 
              </tr>';
    }
    $result->free();
} 
?>
</body>
</html>
