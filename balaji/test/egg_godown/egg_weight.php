<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'];

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$shead_name = "";
$tray_1 = $tray_2 = $tray_3 = $tray_4 = $tray_5 = $tray_6 = $tray_7 = $tray_8 = $average = 0;
$current_date = date('Y-m-d');

if ($id >= 1) {
    $query = "SELECT * FROM egg_weight WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ii", $id, $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $shead_name = $row["shead_name"];
            $tray_1 = $row["tray_1"];
            $tray_2 = $row["tray_2"];
            $tray_3 = $row["tray_3"];
            $tray_4 = $row["tray_4"];
            $tray_5 = $row["tray_5"];
            $tray_6 = $row["tray_6"];
            $tray_7 = $row["tray_7"];
            $tray_8 = $row["tray_8"];
            $average = $row["average"];
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['form_type'] === 'shed_form') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string($_POST['shead_name']);

    $tray_1 = isset($_POST['tray_1']) ? intval($_POST['tray_1']) : 0;
    $tray_2 = isset($_POST['tray_2']) ? intval($_POST['tray_2']) : 0;
    $tray_3 = isset($_POST['tray_3']) ? intval($_POST['tray_3']) : 0;
    $tray_4 = isset($_POST['tray_4']) ? intval($_POST['tray_4']) : 0;
    $tray_5 = isset($_POST['tray_5']) ? intval($_POST['tray_5']) : 0;
    $tray_6 = isset($_POST['tray_6']) ? intval($_POST['tray_6']) : 0;
    $tray_7 = isset($_POST['tray_7']) ? intval($_POST['tray_7']) : 0;
    $tray_8 = isset($_POST['tray_8']) ? intval($_POST['tray_8']) : 0;

    $average = number_format(($tray_1 + $tray_2 + $tray_3 + $tray_4 + $tray_5 + $tray_6 + $tray_7 + $tray_8) / 240.0, 2, '.', '');

    if ($id > 0) {
        $query = "UPDATE egg_weight SET shead_name = ?, tray_1 = ?, tray_2 = ?, tray_3 = ?, tray_4 = ?, tray_5 = ?, tray_6 = ?, tray_7 = ?, tray_8 = ?, average = ? 
                  WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("siiiiiiiidii", $shead_name, $tray_1, $tray_2, $tray_3, $tray_4, $tray_5, $tray_6, $tray_7, $tray_8, $average, $id, $client_id);
        }
    } else {
        $current_date = date("Y-m-d");
        $query = "INSERT INTO egg_weight (date, shead_name, tray_1, tray_2, tray_3, tray_4, tray_5, tray_6, tray_7, tray_8, average, client_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ssiiiiiiiidi", $current_date, $shead_name, $tray_1, $tray_2, $tray_3, $tray_4, $tray_5, $tray_6, $tray_7, $tray_8, $average, $client_id);
        }
    }

    if ($stmt && $stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/egg_godown/egg_weight.php");
        exit;
    } else {
        echo "Error: " . ($stmt ? $stmt->error : $mysqli->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['form_type'] === 'date_form') {
    $selected_date = $_POST['selected_date'] ?? date('Y-m-d');
} else {
    $selected_date = date('Y-m-d');
}

$query = "SELECT * FROM egg_weight WHERE `date` = ? AND client_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("si", $selected_date, $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Production Management</title>
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

        input, select {
            flex: 2;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .table-container {
            width: 40%;
            margin: 30px auto;
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
    <h1>Egg Weight Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown.php';">Go Back</button>
    </div>

    <form action="" method="post">
        <input type="hidden" name="form_type" value="shed_form">
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
        <?php for ($i = 1; $i <= 8; $i++): ?>
        <p>
            <label for="tray_<?= $i ?>">Tray <?= $i ?>:</label>
            <input type="text" name="tray_<?= $i ?>" id="tray_<?= $i ?>" value="<?= htmlspecialchars(${'tray_' . $i}) ?>" required>
        </p>
        <?php endfor; ?>

        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <h2>Records</h2>
    <form method="POST">
        <input type="hidden" name="form_type" value="date_form">
        <p>
            <label for="selected_date">Select Date :</label>
            <input type="date" name="selected_date" id="selected_date" value="<?= htmlspecialchars($selected_date) ?>">
        </p>    
        <button type="submit">Submit</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Shead Name</th>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <th>Tray <?= $i ?></th>
            <?php endfor; ?>
            <th>Average</th>
            <th>EDIT</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['shead_name']) ?></td>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <td><?= htmlspecialchars($row["tray_$i"]) ?></td>
            <?php endfor; ?>
            <td><?= htmlspecialchars($row['average']) ?></td>
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
