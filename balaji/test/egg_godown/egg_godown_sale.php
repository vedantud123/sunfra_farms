<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'];
$username = $_SESSION['username'];

date_default_timezone_set('Asia/Kolkata');
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$shead_name = $no_of_eggs = $type_of_eggs = $remarks = $sale = $sale_price = '';
$no_of_trays = $no_of_loose_eggs = '';

if ($id >= 1) {
    $query = "SELECT * FROM egg_godown_stock WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $shead_name = $row["shead_name"];
        $type_of_eggs = $row["type_of_eggs"];
        $sale = $row["sale"];
        $sale_price = $row["sale_price"];
        $remarks = $row["remarks"];
        $no_of_eggs = $row["no_of_eggs"];
        $no_of_trays = floor($no_of_eggs / 30);
        $no_of_loose_eggs = $no_of_eggs % 30;
    }
    $stmt->close();
}

function getTrayCount($trays) {
    $wholeTrays = floor($trays / 30);
    $remainder = $trays % 30;
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string($_POST['shead_name']);
    $no_of_trays = $mysqli->real_escape_string($_POST['no_of_trays']);
    $no_of_loose_eggs = $mysqli->real_escape_string($_POST['no_of_loose_eggs'] ?? 0);
    $no_of_eggs = is_numeric($no_of_trays) ? ($no_of_trays * 30) + $no_of_loose_eggs : 0;
    $type_of_eggs = $mysqli->real_escape_string($_POST['type_of_eggs']);
    $sale = $mysqli->real_escape_string($_POST['sale']);
    $sale_price = $mysqli->real_escape_string($_POST['sale_price']);
    $remarks = $mysqli->real_escape_string($_POST['remarks']);

    if ($id > 0) {
        $query = "UPDATE egg_godown_stock SET shead_name = ?, no_of_eggs = ?, type_of_eggs = ?, sale = ?, sale_price = ?, remarks = ? WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sissisii", $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $id, $client_id);
    } else {
        $timestamp = date('Y-m-d H:i:s');
        $query = "INSERT INTO egg_godown_stock (shead_name, no_of_eggs, type_of_eggs, sale, sale_price, remarks, timestamp, client_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sisssssi", $shead_name, $no_of_eggs, $type_of_eggs, $sale, $sale_price, $remarks, $timestamp, $client_id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/egg_godown/egg_godown_sale.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$query = "SELECT * FROM egg_godown_stock WHERE sale IS NOT NULL AND client_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Egg Sale Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        h1 { color: #333; }
        .button-container { margin-bottom: 20px; }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover { background-color: #0056b3; }
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
        input, select {
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
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 30px auto;
            background: white;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
        }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #f1f1f1; }
        td a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        td a:hover { text-decoration: underline; }
    </style>
    <script>
        window.onload = function () {
            fetch('https://sunfra.com/farm/test/egg_godown/egg_godown_status.php');
        };
    </script>

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
    <h1>Egg Sale Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown.php';">Go Back</button>
    </div>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown_sale_damage.php';">Damage Eggs During Sale</button>
    </div>

    <?php
    $allowedUsers = ['vedant', 'divya', 'venkat'];
    if (($client_id == 1 && in_array($username, $allowedUsers)) || $client_id != 1) {
        echo '<div class="add-data"><a href="remove_data.php">Remove Data</a></div>';
    }
    ?>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="shead_name">Shead Name:</label>
            <select name="shead_name" id="shead_name">
                <option value="">Select option</option>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="Shead <?= $i ?>" <?= $shead_name === "Shead $i" ? 'selected' : '' ?>>Shead <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <label for="no_of_trays">No Of Trays:</label>
            <input type="text" name="no_of_trays" id="no_of_trays" value="<?= htmlspecialchars($no_of_trays) ?>" required>
        </p>
        <p>
            <label for="no_of_loose_eggs">No Of Loose Eggs:</label>
            <input type="text" name="no_of_loose_eggs" id="no_of_loose_eggs" value="<?= htmlspecialchars($no_of_loose_eggs) ?>">
        </p>
        <p>
            <label for="type_of_eggs">Type of Eggs:</label>
            <select name="type_of_eggs" id="type_of_eggs" required>
                <option>Select Option</option>
                <?php foreach (['Good', 'Damaged', 'Small', 'Big'] as $type): ?>
                    <option value="<?= $type ?>" <?= $type_of_eggs === $type ? 'selected' : '' ?>><?= $type ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="sale">Sale To:</label>
            <input type="text" name="sale" id="sale" value="<?= htmlspecialchars($sale) ?>">
        </p>
        <p>
            <label for="sale_price">Sale Price:</label>
            <input type="text" name="sale_price" id="sale_price" value="<?= htmlspecialchars($sale_price) ?>">
        </p>
        <p>
            <label for="remarks">Remarks:</label>
            <input name="remarks" id="remarks" value="<?= htmlspecialchars($remarks) ?>">
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Date & Time</th>
            <th>Shead Name</th>
            <th>No of Eggs</th>
            <th>Type of Eggs</th>
            <th>Sale</th>
            <th>Sale Price</th>
            <th>Remarks</th>
            <th>Edit</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['timestamp']) ?></td>
            <td><?= htmlspecialchars($row['shead_name']) ?></td>
            <td><?= htmlspecialchars(getTrayCount($row['no_of_eggs'])) ?></td>
            <td><?= htmlspecialchars($row['type_of_eggs']) ?></td>
            <td><?= htmlspecialchars($row['sale']) ?></td>
            <td><?= htmlspecialchars($row['sale_price']) ?></td>
            <td><?= htmlspecialchars($row['remarks']) ?></td>
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
