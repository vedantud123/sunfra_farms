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
    <title>Labour Master</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .button-container {
            margin-bottom: 20px;
            text-align: center;
        }
        .button-container a,
        .button-container button {
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px;
            color: white;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .button-container a:hover,
        .button-container button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th,
        table td {
            padding: 10px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #007BFF;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        table a {
            color: #007BFF;
            text-decoration: none;
        }
        table a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Labour Master</h1>
        <div class="button-container">
		<button onclick="window.location.href='https://sunfra.com/farm/attendance/showoption.php';">Go Back</button>
            <a href="labour_masterNewEdit.php">Add New Data</a>
        </div>

        <?php
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        $query = "SELECT * FROM labour_master ORDER BY id DESC";

        echo '<table>
              <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Date of Birth</th>
                  <th>Address</th>
                  <th>Phone Number</th>
                  <th>Aadhar Number</th>
                  <th>Joining Reference</th>
                  <th>Related To</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Actions</th>
              </tr>';

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $field1name = htmlspecialchars($row["id"]);
                $field2name = htmlspecialchars($row["name"]);
                $field3name = htmlspecialchars($row["dateOfBirth"]);
                $field4name = htmlspecialchars($row["address"]);
                $field5name = htmlspecialchars($row["phoneNumber"]);
                $field6name = htmlspecialchars($row["aadhar"]);
                $field7name = htmlspecialchars($row["joiningReference"]);
                $field8name = htmlspecialchars($row["relatedTo"]);
                $field9name = htmlspecialchars($row["startDate"]);
                $field10name = htmlspecialchars($row["endDate"]);

                echo '<tr>
                          <td>' . $field1name . '</td>
                          <td>' . $field2name . '</td>
                          <td>' . $field3name . '</td>
                          <td>' . $field4name . '</td>
                          <td>' . $field5name . '</td>
                          <td>' . $field6name . '</td>
                          <td>' . $field7name . '</td>
                          <td>' . $field8name . '</td>
                          <td>' . $field9name . '</td>
                          <td>' . $field10name . '</td>
                          <td><a href="labour_masterNewEdit.php?id=' . $field1name . '">Edit</a></td>
                      </tr>';
            }
            $result->free();
        } else {
            echo '<tr><td colspan="11">No records found.</td></tr>';
        }

        $mysqli->close();
        ?>
        </table>
    </div>
</body>
</html>
