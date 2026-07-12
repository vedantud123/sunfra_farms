<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    if ($id > 0) {
        $query = "DELETE FROM egg_godown_stock WHERE id = $id";
        
        if ($mysqli->query($query)) {
            header("Location: https://sunfra.com/farm/egg_godown/remove_data.php");
            exit;
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
</head>
<body>

    <button onclick="window.location.href='https://sunfra.com/farm/egg_godown/egg_godown_sale.php';">Go Back</button>
    <h1>Delete Feed Raw Material</h1>
    <form action="" method="post">
        <label for="id">Enter ID to Delete:</label>
        <input type="number" name="id" id="id" required>
        <button type="submit">Delete</button>
    </form>
</body>
</html>
