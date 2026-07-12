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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    if ($id > 0) {
        // Secure multi-tenant delete: only delete record belonging to the current client
        $query = "DELETE FROM egg_godown_stock WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ii", $id, $client_id);
            if ($stmt->execute()) {
                header("Location: https://sunfra.com/farm/test/egg_godown/remove_data.php");
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error: " . $mysqli->error;
        }
    } else {
        echo "Invalid ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Feed Raw Material</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
            margin: 0;
            text-align: center;
        }
        form {
            max-width: 300px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            font-size: 14px;
        }
        input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: red;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: darkred;
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

    <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown_sale.php';">Go Back</button>
    <h1>Delete Feed Raw Material</h1>
    <form action="" method="post">
        <label for="id">Enter ID to Delete:</label>
        <input type="number" name="id" id="id" required>
        <button type="submit">Delete</button>
    </form>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
