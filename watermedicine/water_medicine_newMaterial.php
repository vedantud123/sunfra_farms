<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$sql = "SELECT name FROM feed_rawMaterial ORDER BY name";
$result = $conn->query($sql);
$type = "Water_Medicine";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_REQUEST['name'] ?? null;
    
    if ($type && $name) {
        $formulaTypes = ['shead_1', 'shead_2', 'shead_3', 'shead_4', 'shead_5', 'shead_6', 'shead_7', 'shead_8', 'chick', 'grower'];

        mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

        try {
            foreach ($formulaTypes as $formulaType) {
                $sql = "INSERT INTO feed_formula_detail (feed_rawMaterial_name, feed_formulaType, quantity, type) 
                        VALUES ('$name', '$formulaType', '0', '$type')";

                if (!mysqli_query($conn, $sql)) {
                    throw new Exception("Error: " . mysqli_error($conn));
                }
            }
            mysqli_commit($conn);
		    echo "<script>alert('Material added successfully.');</script>";
		} catch (Exception $e) {
            mysqli_rollback($conn);
            echo "Transaction failed: " . $e->getMessage();
        }
    } else {
        echo "<h3 style='color: red;'>Please select both type and material.</h3>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Select and Store Material</title>
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
        <button onclick="window.location.href='https://sunfra.com/farm/watermedicine/water_medicine.php'">Go Back</button>
        <h1>Select and Store Water Medicine</h1>
        <form action="" method="post">
           <p>
                <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Material:</label>
                <select name="name" id="name" required>
                    <option value="">--Select--</option>
                    <?php
                    if ($result && $result->num_rows > 0) {
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
