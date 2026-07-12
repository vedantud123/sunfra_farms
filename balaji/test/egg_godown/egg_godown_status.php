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

function getTrayCount($eggs) {
    $wholeTrays = floor($eggs / 30);
    $remainder = $eggs % 30; 
    $formattedRemainder = str_pad($remainder, 2, "0", STR_PAD_LEFT);
    return "{$wholeTrays}.{$formattedRemainder}";
}

function getTotalEggsFromTrays($trays) {
    $wholeTrays = floor($trays);
    $decimalPart = $trays - $wholeTrays;
    $partialEggs = round($decimalPart * 100);
    return ($wholeTrays * 30) + $partialEggs;
}

$shead_queries = ['shead 1', 'shead 2', 'shead 3', 'shead 4', 'shead 5', 'shead 6', 'shead 7', 'shead 8'];
$date_condition = date("Y-m-d");
$log_messages = [];

foreach ($shead_queries as $shead) {
    $production_value = $sale_value = $closing_balance_for_day = '';

    $query = "SELECT 
        SUM(CASE WHEN sale IS NULL AND type_of_eggs IN ('Good', 'Big', 'Bullet', 'Small') THEN no_of_eggs ELSE 0 END) AS production_good_eggs,
        SUM(CASE WHEN sale IS NOT NULL AND type_of_eggs IN ('Good', 'Big', 'Bullet', 'Small') THEN no_of_eggs ELSE 0 END) AS sale_good_eggs,
        SUM(CASE WHEN sale IS NULL AND type_of_eggs IN ('Damaged', 'Scrap') THEN no_of_eggs ELSE 0 END) AS production_damage_eggs,
        SUM(CASE WHEN sale IS NOT NULL AND type_of_eggs IN ('Damaged', 'Scrap') THEN no_of_eggs ELSE 0 END) AS sale_damage_eggs
        FROM egg_godown_stock
        WHERE shead_name = ? AND DATE(timestamp) = ? AND client_id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ssi", $shead, $date_condition, $client_id);
    $stmt->execute();
    $stmt->bind_result($production_good, $sale_good, $production_damage, $sale_damage);
    $stmt->fetch();
    $stmt->close();

    $production_value = $production_good + $production_damage;
    $production = getTrayCount($production_value);
    $sale_value = $sale_good + $sale_damage;

    $return_query = "SELECT SUM(no_of_eggs) AS return_eggs 
                     FROM egg_godown_stock 
                     WHERE remarks = 'Return' AND shead_name = ? AND DATE(timestamp) = ? AND client_id = ?";
    $return_stmt = $mysqli->prepare($return_query);
    $return_stmt->bind_param("ssi", $shead, $date_condition, $client_id);
    $return_stmt->execute();
    $return_stmt->bind_result($return_eggs);
    $return_stmt->fetch();
    $return_stmt->close();

    $return_eggs = $return_eggs ?? 0;
    $sale_value = $sale_value - $return_eggs;
    $sale = getTrayCount($sale_value);

    $yesterday_date = date("Y-m-d", strtotime("-1 day"));
    $balance_query = "SELECT closing_balance FROM egg_godown_status WHERE DATE = ? AND shead_name = ? AND client_id = ?";
    $balance_stmt = $mysqli->prepare($balance_query);
    $balance_stmt->bind_param("ssi", $yesterday_date, $shead, $client_id);
    $balance_stmt->execute();
    $balance_stmt->bind_result($opening_balance);
    $balance_stmt->fetch();
    $balance_stmt->close();

    $opening_balance = $opening_balance ?? 0.00;

    $current_balance = getTotalEggsFromTrays($production) + getTotalEggsFromTrays($opening_balance);
    $closing_balance_eggs = $current_balance - $sale_value;
    $closing_balance_for_day = getTrayCount($closing_balance_eggs);

    $check_data_query = "SELECT id FROM egg_godown_status WHERE DATE = ? AND shead_name = ? AND client_id = ?";
    $check_stmt = $mysqli->prepare($check_data_query);
    $check_stmt->bind_param("ssi", $date_condition, $shead, $client_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    $row_count = $check_stmt->num_rows;
    $check_stmt->close();

    if ($row_count > 0) {
        $update_query = "UPDATE egg_godown_status 
                         SET opening_balance = ?, production = ?, sale = ?, closing_balance = ? 
                         WHERE DATE = ? AND shead_name = ? AND client_id = ?";
        $update_stmt = $mysqli->prepare($update_query);
        $update_stmt->bind_param("ddddssi", $opening_balance, $production, $sale, $closing_balance_for_day, $date_condition, $shead, $client_id);
        if ($update_stmt->execute()) {
            $log_messages[] = "✅ Record updated successfully for <strong>$shead</strong>.";
        } else {
            $log_messages[] = "❌ Error updating record for <strong>$shead</strong>: " . $update_stmt->error;
        }
        $update_stmt->close();
    } else {
        $insert_query = "INSERT INTO egg_godown_status 
                         (date, shead_name, opening_balance, production, sale, closing_balance, client_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $mysqli->prepare($insert_query);
        $insert_stmt->bind_param("ssddddi", $date_condition, $shead, $opening_balance, $production, $sale, $closing_balance_for_day, $client_id);
        if ($insert_stmt->execute()) {
            $log_messages[] = "✅ Record inserted successfully for <strong>$shead</strong>.";
        } else {
            $log_messages[] = "❌ Error inserting record for <strong>$shead</strong>: " . $insert_stmt->error;
        }
        $insert_stmt->close();
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Egg Godown Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
            color: #333;
        }
        h1 {
            color: #007bff;
            text-align: center;
        }
        .log-container {
            max-width: 700px;
            margin: 30px auto;
            background: #ffffff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .log-container ul {
            list-style: none;
            padding: 0;
        }
        .log-container li {
            padding: 10px 0;
            border-bottom: 1px solid #e6e6e6;
        }
        .log-container li:last-child {
            border-bottom: none;
        }
        .back-btn {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background: #007bff;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            width: 200px;
        }
        .back-btn:hover {
            background: #0056b3;
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
    <h1>Egg Godown Daily Status Update</h1>
    <div class="log-container">
        <ul>
            <?php foreach ($log_messages as $msg): ?>
                <li><?= $msg ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="back-btn">← Go Back to Dashboard</a>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
