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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? null;

    if ($name) {
        $mysqli->begin_transaction();

        try {
            $stmt = $mysqli->prepare("DELETE FROM feed_formula_detail WHERE feed_rawMaterial_name = ?");
            $stmt->bind_param("s", $name);

            if (!$stmt->execute()) {
                throw new Exception("Error: " . $stmt->error);
            }

            $mysqli->commit();
            echo "<script>alert('Material removed successfully.');</script>";
        } catch (Exception $e) {
            $mysqli->rollback();
            echo "<script>alert('Transaction failed: " . $e->getMessage() . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please select a material to remove.');</script>";
    }
}

$sql = "SELECT feed_rawMaterial_name FROM `feed_formula_detail` GROUP BY feed_rawMaterial_name";
$result = $mysqli->query($sql);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}
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
</head>
<body>
    <div class="container">
        <button class="go-back-btn" onclick="window.location.href='https://sunfra.com/farm/watermedicine/water_medicine.php'">Go Back</button>
        <h1>Select Material to Remove</h1>
        <form action="" method="post">
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
</body>
</html>

<?php
$mysqli->close();
?>
