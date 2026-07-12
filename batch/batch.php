<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Management</title>
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
        <h1>Batch Management</h1>
        <div class="button-container">
            <button onclick="window.location.href='https://sunfra.com/farm/index.php';">Go Back</button>
            <a href="batchNewEdit.php">Add New Batch</a>
        </div>

        <?php
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        $query = "SELECT * FROM batch ORDER BY batch_id DESC";

        echo '<table>
              <tr>
                  <th>Batch ID</th>
                  <th>Breed</th>
                  <th>Hatch Date</th>
                  <th>No. of Chicks</th>
                  <th>Shed No</th>
                  <th>Cull Date</th>
                  <th>Live Birds</th>
                  <th>Duration</th>
                  <th>Actions</th>
              </tr>';

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $field1name = $row["batch_id"];
                $field2name = $row["breed"];
                $field3name = $row["hatchDate"];
                $field4name = $row["noOfChicks"];
                $field5name = $row["sheadNo"];
                $field6name = $row["cullDate"];
                $field7name = $row["live_birds"];

                if ($field6name === "0000-00-00" || empty($field6name)) {
                    if (!empty($field3name)) {
                        $startDateObj = new DateTime($field3name);
                        $diff = $startDateObj->diff(new DateTime());
                        $runningDays = $diff->days + 1;
                    } else {
                        $runningDays = "N/A";
                    }
                } else {
                    $runningDays = "Done";
                }

                $runningWeeks = (is_numeric($runningDays) && $runningDays !== "Done") ? floor($runningDays / 7) : "Done";

                $duration = ($runningDays !== "Done") ? "$runningDays day(s), $runningWeeks week(s)" : "Done";

                echo '<tr>
                          <td>' . htmlspecialchars($field1name) . '</td>
                          <td>' . htmlspecialchars($field2name) . '</td>
                          <td>' . htmlspecialchars($field3name) . '</td>
                          <td>' . htmlspecialchars($field4name) . '</td>
                          <td>' . htmlspecialchars($field5name) . '</td>
                          <td>' . htmlspecialchars($field6name) . '</td>
                          <td>' . htmlspecialchars($field7name) . '</td>
                          <td>' . htmlspecialchars($duration) . '</td>
                          <td><a href="batchNewEdit.php?id=' . htmlspecialchars($field1name) . '">Edit</a></td>
                      </tr>';
            }
            $result->free();
        } else {
            echo '<tr><td colspan="8">No records found.</td></tr>';
        }
        $mysqli->close();
        ?>
        </table>
    </div>
</body>
</html>