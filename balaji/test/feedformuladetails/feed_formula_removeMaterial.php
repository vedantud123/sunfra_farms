<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "SELECT feed_rawMaterial_name 
        FROM feed_formula_detail 
        WHERE client_id = ? 
        GROUP BY feed_rawMaterial_name";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Material</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
        }
        .container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333333;
        }
        .container select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .container button, .container input[type="submit"] {
            background-color: #007BFF;
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .container button:hover, .container input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .container a {
            text-decoration: none;
            color: #007BFF;
        }
        .go-back-btn {
            margin-bottom: 20px;
            background-color: #28a745;
        }
        .go-back-btn:hover {
            background-color: #218838;
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
        <button class="go-back-btn" onclick="window.location.href='https://sunfra.com/farm/test/feedformuladetails/feed_formula_details.php'">Go Back</button>
        <h1>Select Material to Remove</h1>
        <form action="feed_formula_removeMaterial_remove.php" method="post">
            <p>
                <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">Name of the Material:</label>
                <select name="name" id="name" required>
                    <option value="">--Select--</option>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['feed_rawMaterial_name']) . '">' . htmlspecialchars($row['feed_rawMaterial_name']) . '</option>';
                        }
                    } else {
                        echo '<option value="">No materials found</option>';
                    }
                    ?>
                </select>
            </p>
            <input type="submit" value="Remove Material">
        </form>
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

<?php
$stmt->close();
$mysqli->close();
?>
