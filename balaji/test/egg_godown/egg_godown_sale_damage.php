<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'];

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$shead_name = $no_of_eggs = $sale = '';
$no_of_trays = $no_of_loose_Eggs = 0;

if ($id >= 1) {
    $query = "SELECT * FROM egg_godown_stock WHERE id = $id AND client_id = $client_id";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $shead_name = $row["shead_name"];
            $no_of_eggs = $row["no_of_eggs"];
            $sale = $row["sale"];
            $no_of_trays = floor($no_of_eggs / 30);
            $no_of_loose_Eggs = $no_of_eggs % 30;
        }
        $result->free();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string(trim($_POST['shead_name'] ?? ''));
    $no_of_trays = intval($_POST['no_of_trays'] ?? 0);
    $no_of_loose_Eggs = intval($_POST['no_of_loose_Eggs'] ?? 0);
    $no_of_eggs = $no_of_trays * 30 + $no_of_loose_Eggs;
    $sale = $mysqli->real_escape_string(trim($_POST['sale'] ?? ''));
    $type_of_eggs = "Damaged";
    $remarks = "Return";
    $sale_price = 0;

    if ($id >= 1) {
        $query = "UPDATE egg_godown_stock 
                  SET shead_name='$shead_name', no_of_eggs=$no_of_eggs, sale='$sale', 
                      type_of_eggs='$type_of_eggs', client_id=$client_id 
                  WHERE id=$id AND client_id=$client_id";
    } else {
        $timestamp = date('Y-m-d H:i:s');
        $query = "INSERT INTO egg_godown_stock 
                  (shead_name, no_of_eggs, type_of_eggs, timestamp, sale_price, sale, remarks, client_id) 
                  VALUES ('$shead_name', $no_of_eggs, '$type_of_eggs', 
                          '$timestamp', $sale_price, '$sale', '$remarks', $client_id)";
    }

    if ($mysqli->query($query)) {
        header("Location: /farm/test/egg_godown/egg_godown_sale.php");
        exit;
    } else {
        echo "Error: " . $mysqli->error . "<br>";
        echo "SQL Query: " . $query;
    }
}

$date = date('Y-m-d');
$sql = "SELECT sale FROM egg_godown_stock 
        WHERE DATE(`timestamp`) = '$date' 
        AND sale IS NOT NULL 
        AND remarks != 'Return' 
        AND sale != 'Scrap' 
        AND client_id = $client_id 
        GROUP BY sale";
$sale_result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Damage Eggs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
            margin: 0;
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        .button-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-container button {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .button-container button:hover {
            background-color: #5a6268;
        }
        form {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        form p {
            margin-bottom: 15px;
        }
        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: left;
        }
        form input, form select, form button {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
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
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/egg_godown/egg_godown_sale.php';">Go Back</button>
    </div>

    <h1>Damage Eggs</h1>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <p>
            <label for="shead_name">Shead Name:</label>
            <select name="shead_name" id="shead_name" required>
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
            <label for="no_of_loose_Eggs">No Of Loose Eggs:</label>
            <input type="text" name="no_of_loose_Eggs" id="no_of_loose_Eggs" value="<?= htmlspecialchars($no_of_loose_Eggs) ?>">
        </p>

        <p>
            <label for="sale">Party Name:</label>
            <select name="sale" id="sale" required>
                <option value="">--Select--</option>
                <?php
                if ($sale_result && $sale_result->num_rows > 0) {
                    while ($row = $sale_result->fetch_assoc()) {
                        $sale_option = htmlspecialchars($row['sale']);
                        $selected = ($sale_option === $sale) ? 'selected' : '';
                        echo "<option value='$sale_option' $selected>$sale_option</option>";
                    }
                } else {
                    echo '<option value="">No materials found</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
