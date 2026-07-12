<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$client_id = $_SESSION['client_id'] ?? 0;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$name = $price = '';

// Fetch existing record for editing
if ($id >= 1) {
    $stmt = $mysqli->prepare("SELECT * FROM feed_rawmaterial_price WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $name = $row["name"];
        $price = $row["price"];
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $mysqli->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);

  if ($id > 0) {
    // Update existing
    $stmt = $mysqli->prepare("UPDATE feed_rawmaterial_price SET name = ?, price = ? WHERE id = ? AND client_id = ?");
    $stmt->bind_param("sdii", $name, $price, $id, $client_id);
} else {
    // Check for duplicate before insert
    $check_stmt = $mysqli->prepare("SELECT id FROM feed_rawmaterial_price WHERE name = ? AND client_id = ?");
    $check_stmt->bind_param("si", $name, $client_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<p style='color:red;'>Material with the same name already exists for this client.</p>";
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Proceed with insert
    $stmt = $mysqli->prepare("INSERT INTO feed_rawmaterial_price (name, price, client_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $name, $price, $client_id);
}

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/profit_and_loss_details/feed_material_price_perkg.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch data
$price_stmt = $mysqli->prepare("SELECT * FROM feed_rawmaterial_price WHERE client_id = ? ORDER BY id DESC");
$price_stmt->bind_param("i", $client_id);
$price_stmt->execute();
$price_result = $price_stmt->get_result();

$material_stmt = $mysqli->prepare("SELECT name, type FROM feed_rawmaterial WHERE client_id = ? AND type IN ('Feed Medicine', 'Raw Material')");
$material_stmt->bind_param("i", $client_id);
$material_stmt->execute();
$material_result = $material_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Price Per KG/LIT</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; padding: 20px; }
        h1 { color: #333; }
        .button-container { margin-bottom: 20px; }
        button {
            background-color: #007bff; color: white; padding: 10px 15px;
            border: none; cursor: pointer; border-radius: 5px;
        }
        button:hover { background-color: #0056b3; }
        form {
            background: white; padding: 20px; max-width: 500px; margin: auto;
            border-radius: 10px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        p {
            display: flex; justify-content: space-between; align-items: center;
            margin: 10px 0;
        }
        label { flex: 1; font-weight: bold; text-align: left; }
        input, select {
            flex: 2; padding: 8px; border: 1px solid #ccc; border-radius: 5px;
        }
        .table-container { width: 40%; margin: 30px auto; }
        table {
            border-collapse: collapse; width: 100%; max-width: 800px; margin: 0 auto;
            background: white; text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        table th, table td {
            border: 1px solid #ddd; padding: 10px; font-size: 14px; text-align: center;
        }
        table th {
            background-color: #007bff; color: white; font-weight: bold;
        }
        table tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #f1f1f1; }
        td a {
            text-decoration: none; color: #007bff; font-weight: bold;
        }
        td a:hover { text-decoration: underline; }
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
    <h1>Material Price Per KG/LIT</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/test_show_profit_loss_details.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="name">Material Name:</label>
            <select name="name" id="name">
                <option value="">Select option</option>
                <?php
                if ($material_result->num_rows > 0) {
                    while ($row = $material_result->fetch_assoc()) {
                        $selected = ($name === $row['name']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row['name']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                    }
                } else {
                    echo "<option value=''>No data found</option>";
                }
                ?>
            </select>
        </p>
        <p>
            <label for="price">Price:</label>
            <input type="text" name="price" id="price" value="<?= htmlspecialchars($price) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Material Name</th>
            <th>Price (Per KG/LIT)</th>
            <th>Edit</th>
        </tr>
        <?php while ($row = $price_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td><a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
