<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;
$username = $_SESSION['username'] ?? '';

$allowed_users_client1 = ['vedant', 'divya', 'venkat']; // For client_id 1

// Restrict access for client_id 1
if ($client_id == 1 && !in_array($username, $allowed_users_client1)) {
    echo "You do not have permission to access this page.";
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$feed_formulaType = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

if (empty($feed_formulaType)) {
    die("Error: Formula type not specified.");
}

$query = "SELECT * FROM feed_formula_detail WHERE feed_formulaType = ? AND client_id = ? ORDER BY type, quantity DESC;";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $feed_formulaType, $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (empty($data)) {
    echo "No data found for the selected formula type.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updated_values = $_POST['quantity'];

    foreach ($data as $index => $row) {
        $new_value = $updated_values[$index];

        $update_query = "UPDATE feed_formula_detail SET quantity = ? WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("dii", $new_value, $row['id'], $client_id);  // use double (float) for quantity
        $stmt->execute();
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?type=" . urlencode($feed_formulaType));
    exit();
}

$stmt->close();
$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data for Formula Type: <?php echo htmlspecialchars($feed_formulaType); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 15px;
            font-size: 22px;
        }
        .username {
            font-size: 14px;
            color: #555;
            text-align: right;
            margin-bottom: 15px;
        }
        button {
            padding: 6px 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            margin-bottom: 15px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        th {
            background-color: #007BFF;
            color: white;
            font-size: 14px;
        }
        td input {
            padding: 6px;
            font-size: 13px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        input[type="submit"] {
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            cursor: pointer;
            margin-top: 15px;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="username">
        Logged in as: <?php echo htmlspecialchars($username); ?>
    </div>

    <button onclick="window.location.href='https://sunfra.com/farm/test/feedformuladetails/feed_formula_details.php'">Go Back</button>

    <h1>Edit Data for Formula Type: <?php echo htmlspecialchars($feed_formulaType); ?></h1>

    <form action="" method="POST">
        <table class="form-table">
            <tr>
                <th>Material</th>
                <th>Quantity</th>
            </tr>
            
            <?php
            foreach ($data as $index => $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['feed_rawmaterial_name']) . '</td>';
                echo '<td><input type="text" name="quantity[]" value="' . htmlspecialchars($row['quantity']) . '" required></td>';
                echo '</tr>';
            }
            ?>
        </table>
        <input type="submit" value="Update">
    </form>
    
</div>
</body>
</html>
