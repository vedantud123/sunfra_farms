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

$sql = "SELECT name FROM feed_rawMaterial order by name"; 
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Select Material</title>
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
            font-size: 22px;
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
    </style>
</head>
<body>
    <div class="container">
        <button onclick="window.location.href='https://sunfra.com/farm/feedformuladetails/feed_formula_details.php'">Go Back</button>
        <h1>Select and Store Material</h1>
        <form action="feed_formula_newMaterial_store.php" method="post">
            <p>
                <label for="type" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Type:</label>
                <select name="type" id="type" required>
                    <option value="">--Select--</option>
                    <option value="1">Feed_Formula</option>
                    <option value="2">Feed_Medicine</option>
                    <option value="3">Water_Medicine</option>
                    <option value="4">Sanitisation</option>
                </select>
            </p>

            <p>
                <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Material:</label>
                <select name="name" id="name" required>
                    <option value="">--Select--</option>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                        }
                    } else {
                        echo '<option value="">No materials found</option>';
                    }
                    ?>
                </select>
            </p>

            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>

<?php
$mysqli->close();
?>
