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

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar { transition: all 0.3s ease; }
    .sidebar a:hover { background-color: #2b6cb0; color: white; }
    .collapsed { width: 64px !important; }
    .collapsed .sidebar-text { display: none; }
    .collapsed nav a { justify-content: center; }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .sidebar.show { display: block; }
    }
  </style>
</head>
<?php session_start(); ?>
<?php $clientName = $_SESSION['client_name'] ?? 'Yours'; ?>
<body class="bg-gray-100 text-gray-800">
    <div class="container">
        <h1>Labour Master</h1>
        <div class="button-container">
		<button onclick="window.location.href='https://sunfra.com/farm/test/attendance/showoption.php';">Go Back</button>
            <a href="labour_masterNewEdit.php">Add New Data</a>
        </div>
<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch only records that belong to this tenant
$query = "SELECT * FROM labour_master WHERE client_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

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

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
              <td>' . htmlspecialchars($row["id"]) . '</td>
              <td>' . htmlspecialchars($row["name"]) . '</td>
              <td>' . htmlspecialchars($row["dateOfBirth"]) . '</td>
              <td>' . htmlspecialchars($row["address"]) . '</td>
              <td>' . htmlspecialchars($row["phoneNumber"]) . '</td>
              <td>' . htmlspecialchars($row["aadhar"]) . '</td>
              <td>' . htmlspecialchars($row["joiningReference"]) . '</td>
              <td>' . htmlspecialchars($row["relatedTo"]) . '</td>
              <td>' . htmlspecialchars($row["startDate"]) . '</td>
              <td>' . htmlspecialchars($row["endDate"]) . '</td>
              <td><a href="labour_masterNewEdit.php?id=' . htmlspecialchars($row["id"]) . '">Edit</a></td>
          </tr>';
    }
    $result->free();
} else {
    echo '<tr><td colspan="11">No records found.</td></tr>';
}

$stmt->close();
$mysqli->close();
?>

        </table>
    </div>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
