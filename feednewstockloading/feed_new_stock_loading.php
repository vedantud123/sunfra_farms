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
$name = $new_stock_quantity = $check_timestamp = '';

if ($id >= 1) {
    $query = "SELECT * FROM feed_new_stock_loading WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $name = $row["name"];
        $new_stock_quantity = $row["new_stock_quantity"];
        $check_timestamp = $row["timestamp"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $mysqli->real_escape_string($_POST['name']);
    #$new_stock_quantity = isset($_POST['new_stock_quantity']) ? floatval($_POST['new_stock_quantity']) : 0;
    $new_stock_quantity = $mysqli->real_escape_string($_POST['new_stock_quantity']);

    if ($id == 0) {
        $add_new_rawMaterial_query = "UPDATE feed_rawMaterial SET stock = stock + ? WHERE name = ?";
        $add_new_rawMaterial_stmt = $mysqli->prepare($add_new_rawMaterial_query);
        $add_new_rawMaterial_stmt->bind_param("ds", $new_stock_quantity, $name);
        $add_new_rawMaterial_stmt->execute();
        $add_new_rawMaterial_stmt->close();
    }

    if ($check_timestamp != '') {
        $reduce_value_in_rawMaterial_query = "SELECT * FROM feed_new_stock_loading_logs WHERE timestamp = ?";
        $reduce_value_in_rawMaterial_stmt = $mysqli->prepare($reduce_value_in_rawMaterial_query);
        $reduce_value_in_rawMaterial_stmt->bind_param("s", $check_timestamp);
        $reduce_value_in_rawMaterial_stmt->execute();
        $result = $reduce_value_in_rawMaterial_stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $name = $row['name'];
            $stock = $row['stock'];

            $reduce_value_rawMaterial_query = "UPDATE feed_rawMaterial SET stock = stock - ? WHERE name = ?";
            $reduce_value_rawMaterial_stmt = $mysqli->prepare($reduce_value_rawMaterial_query);
            $reduce_value_rawMaterial_stmt->bind_param("ds", $stock, $name);
            $reduce_value_rawMaterial_stmt->execute();
            $reduce_value_rawMaterial_stmt->close();
        }
        $reduce_value_in_rawMaterial_stmt->close();

        $delete_logs_query = "DELETE FROM feed_new_stock_loading_logs WHERE timestamp = ?";
        $delete_logs_stmt = $mysqli->prepare($delete_logs_query);
        $delete_logs_stmt->bind_param("s", $check_timestamp);
        $delete_logs_stmt->execute();
        $delete_logs_stmt->close();
    }

    $timestamp = date('Y-m-d H:i:s');

    $add_material_in_logs_query = "INSERT INTO feed_new_stock_loading_logs (name, new_stock_quantity, timestamp) VALUES (?, ?, ?)";
    $add_material_in_logs_stmt = $mysqli->prepare($add_material_in_logs_query);
    $add_material_in_logs_stmt->bind_param("sss", $name, $new_stock_quantity, $timestamp);
    $add_material_in_logs_stmt->execute();
    $add_material_in_logs_stmt->close();

    if ($id > 0) {
        $query = "UPDATE feed_new_stock_loading SET name = ?, new_stock_quantity = ?, timestamp = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sisi", $name, $new_stock_quantity, $timestamp, $id);
    } else {
        $query = "INSERT INTO feed_new_stock_loading (name, new_stock_quantity, timestamp) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $name, $new_stock_quantity, $timestamp);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/feednewstockloading/feed_new_stock_loading.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$sql = "SELECT name FROM feed_rawMaterial ORDER BY name";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed New Stock Management</title>
    <style>
    /* General Body Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
        text-align: center;
    }

    /* Container for Centered Content */
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 10px 15px;
        text-align: center;
    }

    /* Form Styles */
    form {
        display: inline-block;
        text-align: left;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    form p {
        margin-bottom: 15px;
    }

    form label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    form input, form select, form button {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }

    form button {
        background-color: #007bff;
        color: white;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    form button:hover {
        background-color: #0056b3;
    }

    /* Back Button */
    .button-container {
        margin-bottom: 20px;
    }

    .button-container button {
        background-color: #6c757d;
        color: white;
        font-size: 14px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .button-container button:hover {
        background-color: #5a6268;
    }

    /* Title Styles */
    h1 {
        color: #007bff;
        margin: 20px 0;
        font-size: 24px;
    }

    /* Table Styles */
    .table-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        overflow-x: auto; /* Adds horizontal scroll for small screens */
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
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

    /* Responsive Design for Smaller Screens */
    @media (max-width: 600px) {
        h1 {
            font-size: 20px;
        }

        form {
            width: 90%;
            padding: 15px;
        }

        table th, table td {
            font-size: 12px;
            padding: 8px;
        }
    }
</style>
</head>
<body>
    <h1>Feed New Stock Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/feed_plant_supervisor.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="name">Select Material:</label>
            <select name="name" id="name" required>
                <option value="">--Select--</option>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['name']) . '" ' . ($name == $row['name'] ? 'selected' : '') . '>' . htmlspecialchars($row['name']) . '</option>';
                    }
                } else {
                    echo '<option value="">No materials found</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label for="new_stock_quantity">New Stock Quantity:</label>
            <input type="number" step="0.01" name="new_stock_quantity" id="new_stock_quantity" value="<?= htmlspecialchars($new_stock_quantity) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <?php
    $query = "SELECT * FROM feed_new_stock_loading ORDER BY id DESC";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Material Name</th>
                <th>New Stock Quantity</th>
                <th>Date & Time</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['new_stock_quantity']) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No data available to display.</p>
    <?php endif; ?>

</div>
</body>
</html>

<?php $mysqli->close(); ?>