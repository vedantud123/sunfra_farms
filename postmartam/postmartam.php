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
    <title>Postmartam Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .button-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-container a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .button-container a:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-link {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        .action-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Postmartam Records</h1>
        <div class="button-container" style="text-align: center; margin: 20px 0;">
			<button class="go-back" style="padding: 10px 15px; background-color: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer;" onclick="window.location.href='https://sunfra.com/farm/index.php';">
				Go Back
			</button>
			<a href="postmartamNewEdit.php" style="margin-left: 10px; padding: 10px 15px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
				Add New Data
			</a>
		</div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date-Time</th>
                    <th>Shead No</th>
                    <th>Tarkiya</th>
                    <th>Heart</th>
                    <th>Lever</th>
                    <th>Gizzard</th>
                    <th>Kidney</th>
                    <th>Ovaries</th>
                    <th>Option 1</th>
                    <th>Option 2</th>
                    <th>Option 3</th>
                    <th>Option 4</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
                $query = "SELECT * FROM postmartam ORDER BY id DESC";

                if ($result = $mysqli->query($query)) {
                    while ($row = $result->fetch_assoc()) {
                        $field1name = $row["id"];
                        $field2name = $row["timestamp"];
                        $field3name = $row["sheadNo"];
                        $field4name = $row["tarkiya"]; 
                        $field5name = $row["heart"];
                        $field6name = $row["lever"];
                        $field7name = $row["gizzard"];
                        $field8name = $row["kidney"];
                        $field9name = $row["ovaries"];
                        $field10name = $row["option_1"];
                        $field11name = $row["option_2"];
                        $field12name = $row["option_3"];
                        $field13name = $row["option_4"];
                        $field14name = $row["remarks"];

                        echo '<tr> 
                                  <td>'.$field1name.'</td> 
                                  <td>'.$field2name.'</td> 
                                  <td>'.$field3name.'</td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field4name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field5name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field6name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field7name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field8name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field9name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field10name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field11name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field12name.'" class="action-link">Image</a></td> 
                                  <td><a href="https://sunfra.com/farm/postmartam/'.$field13name.'" class="action-link">Image</a></td> 
                                  <td>'.$field14name.'</td> 
                                  <td><a href="postmartamNewEdit.php?id='.$field1name.'" class="action-link">Edit</a></td> 
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
	