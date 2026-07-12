<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$medicine_cost = $other_cost = $shead_name = '';

if ($id >= 1) {
    $query = "SELECT shead_name, medicine_cost, other_cost FROM egg_cutting_price WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $shead_name = $row["shead_name"];
            $medicine_cost = $row["medicine_cost"];
            $other_cost = $row["other_cost"];
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $medicine_cost = isset($_POST['medicine_cost']) ? floatval($_POST['medicine_cost']) : 0.0;
    $other_cost = isset($_POST['other_cost']) ? floatval($_POST['other_cost']) : 0.0;

    if ($id > 0) {
        $query = "UPDATE egg_cutting_price SET medicine_cost = ?, other_cost = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);

        if ($stmt) {
            $stmt->bind_param("ddi", $medicine_cost, $other_cost, $id);
            $stmt->execute();
            $stmt->close();
        }

        // Redirect after successful update
        header("Location: https://sunfra.com/farm/profit_and_loss_details/medicine_and_other_cost.php");
        exit;
    }
}

$query = "SELECT id, shead_name, medicine_cost, other_cost FROM egg_cutting_price";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine And Other Cost</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        .button-container {
            margin-bottom: 20px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        form {
            background: white;
            padding: 20px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        p {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
        }

        label {
            flex: 1;
            font-weight: bold;
            text-align: left;
        }

        input {
            flex: 2;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button[type="submit"] {
            width: 100%;
            background-color: #007bff;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .table-container {
            width: 60%;
            margin: 30px auto; 
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
            text-align: center;
        }

        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        td a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Medicine And Other Cost</h1>

    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/profitandloss_details.php';">Go Back</button>
    </div>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="medicine_cost">Medicine Price:</label>
            <input type="text" name="medicine_cost" id="medicine_cost" value="<?= htmlspecialchars($medicine_cost) ?>" required>
        </p>
        <p>
            <label for="other_cost">Other Price:</label>
            <input type="text" name="other_cost" id="other_cost" value="<?= htmlspecialchars($other_cost) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Shead Name</th>
                <th>Medicine Cost</th>
                <th>Other Cost</th>
                <th>Edit</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['shead_name']) ?></td>
                <td><?= htmlspecialchars($row['medicine_cost']) ?></td>
                <td><?= htmlspecialchars($row['other_cost']) ?></td>
                <td>
                    <a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
