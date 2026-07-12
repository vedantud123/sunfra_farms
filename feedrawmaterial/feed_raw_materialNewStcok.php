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

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$name = $stock = $metric = $type = ''; 

if ($id >= 1) {
    $query = "SELECT * FROM feed_rawMaterial WHERE id = $id";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $name = $row["name"];
            $stock = $row["stock"];
            $metric = $row["metric"];
            $type = $row["type"];
        }
        $result->free();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $mysqli->real_escape_string($_POST['name']);
    $stock = intval($_POST['stock']);
    $metric = $mysqli->real_escape_string($_POST['metric']);
    $type = $mysqli->real_escape_string($_POST['type']);

    if ($id > 0) {
        $query = "UPDATE feed_rawMaterial SET name='$name', stock=$stock, metric='$metric', type='$type' WHERE id=$id";
    } else {
        $query = "INSERT INTO feed_rawMaterial (name, stock, metric, type) VALUES ('$name', $stock, '$metric', '$type')";
    }

    if ($mysqli->query($query)) {
        header("Location: https://sunfra.com/farm/feedrawmaterial/feed_raw_material.php");
        exit;
    } else {
        echo "Error: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feed Raw Material</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        form {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        form p {
            margin-bottom: 15px;
        }
        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        form input, form select, form button {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        .back-button {
            display: block;
            margin: 20px auto;
            text-align: center;
        }
        .back-button button {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="button-container">
        <button class="Go-Back" onclick="window.location.href='https://sunfra.com/farm/feedrawmaterial/feed_raw_material.php';">Go Back</button>
    </div>
    <h1>Edit Feed Raw Material</h1>
    <form action="" method="post">
        <?php if ($id >= 1): ?>
            <p>
                <label for="id">ID:</label>
                <input type="text" name="id" id="id" value="<?= $id ?>" readonly>
            </p>
        <?php endif; ?>
        
        <p>
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>
        </p>
        
        <p>
            <label for="stock">Stock:</label>
            <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($stock) ?>" required>
        </p>
        
        <p>
            <label for="metric">Metric:</label>
            <select name="metric" id="metric">
                <option value="">Select option</option>
                <option value="KG" <?= $metric === 'KG' ? 'selected' : '' ?>>KG</option>
                <option value="Lit" <?= $metric === 'Lit' ? 'selected' : '' ?>>Lit</option>
            </select>
        </p>
        
        <p>
            <label for="type">Type:</label>
            <select name="type" id="type">
                <option value="">Select option</option>
                <option value="Feed Medicine" <?= $type === 'Feed Medicine' ? 'selected' : '' ?>>Feed Medicine</option>
                <option value="Water Medicine" <?= $type === 'Water Medicine' ? 'selected' : '' ?>>Water Medicine</option>
                <option value="Raw Material" <?= $type === 'Raw Material' ? 'selected' : '' ?>>Raw Material</option>
            </select>
        </p>
        
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>
</body>
</html>
