<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labour Attendance</title>
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
        <h1>Labour Attendance</h1>
        <div class="button-container">
            <button onclick="window.location.href='https://sunfra.com/farm/test/attendance/showoption.php';">Go Back</button>
            <a href="labour_attendanceNewEdit.php">Add New Data</a>
        </div>

        <?php
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        $query = "SELECT * FROM attendance WHERE client_id = ? ORDER BY id DESC";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table>
              <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Date</th>
                  <th>TimeStamp</th>
                  <th>Status</th>
                  <th>Working Place</th>
                  <th>Actions</th>
              </tr>';

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $field1name = htmlspecialchars($row["id"]);
                $field2name = htmlspecialchars($row["name"]);
                $field3name = htmlspecialchars($row["date"]);
                $field4name = htmlspecialchars($row["timestamp"]);
                $field5name = htmlspecialchars($row["status"]);
                $field6name = htmlspecialchars($row["working_place"]);

                echo '<tr>
                          <td>' . $field1name . '</td>
                          <td>' . $field2name . '</td>
                          <td>' . $field3name . '</td>
                          <td>' . $field4name . '</td>
                          <td>' . $field5name . '</td>
                          <td>' . $field6name . '</td>
                          <td><a href="labour_attendanceNewEdit.php?id=' . $field1name . '">Edit</a></td>
                      </tr>';
            }
        } else {
            echo '<tr><td colspan="7">No records found.</td></tr>';
        }

        $stmt->close();
        $mysqli->close();
        ?>
        </table>
    </div>
</body>
</html>
