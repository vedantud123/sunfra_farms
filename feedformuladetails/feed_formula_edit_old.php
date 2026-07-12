<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$allowedUsers = ['vedant', 'divya','venkat']; 
$username = $_SESSION['username'] ?? '';

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['quantity'] as $id => $quantity) {
        $update_query = "UPDATE feed_formula_detail SET quantity = ? WHERE id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("si", $quantity, $id);
        $stmt->execute();
    }
    echo "<script>alert('Data updated successfully!');</script>";
}

$formula_query = "SELECT DISTINCT feed_formulaType FROM feed_formula_detail ORDER BY feed_formulaType";
$formula_result = $mysqli->query($formula_query);

$formula_types = [];
while ($row = $formula_result->fetch_assoc()) {
    $formula_types[] = $row['feed_formulaType'];
}

$query = "SELECT 
    feed_rawMaterial_name AS Material,
    feed_formulaType,
    quantity,
    id
FROM feed_formula_detail ORDER BY type, feed_formulaType";
$result = $mysqli->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['Material']][$row['feed_formulaType']] = [
        'quantity' => $row['quantity'],
        'id' => $row['id'],
    ];
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Raw Material Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-weight: 700;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        input[type="text"], select {
            padding: 8px;
            width: 90%;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
	<button onclick="window.location.href='https://sunfra.com/farm/feedformuladetails/feed_formula_details.php'">Go Back</button>
    <h1>Feed Raw Material Report</h1>
    <form method="POST" action="">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <?php foreach ($formula_types as $type): ?>
                        <th><?php echo htmlspecialchars($type); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $material => $formulas): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($material); ?></td>
                        <?php foreach ($formula_types as $type): ?>
                            <td>
                                <?php if (isset($formulas[$type])): ?>
                                    <input type="text" name="quantity[<?php echo $formulas[$type]['id']; ?>]" 
                                           value="<?php echo htmlspecialchars($formulas[$type]['quantity']); ?>" required>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align: center; margin-top: 20px;">
            <input type="submit" value="Update">
        </div>
    </form>
</div>

</body>
</html>
