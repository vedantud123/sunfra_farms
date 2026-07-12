<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insert Feed Formula</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            text-align: center;
            padding: 20px;
        }
        button, input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #0056b3;
        }
        h3 { color: green; }
        .error { color: red; }
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

    <button onclick="window.location.href='feed_formula_details.php'">Go Back</button>

    <?php
    $conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

    if ($conn->connect_error) {
        die("<p class='error'>Connection failed: " . $conn->connect_error . "</p>");
    }

    $type = $_REQUEST['type'] ?? '';
    $name = $_REQUEST['name'] ?? '';

    if (empty($type) || empty($name)) {
        echo "<p class='error'>Both 'type' and 'name' are required.</p>";
    } else {
        $formulaTypes = ['shead_1', 'shead_2', 'shead_3', 'shead_4', 'shead_5', 'shead_6', 'shead_7', 'shead_8', 'chick', 'grower'];

        $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        try {
            $stmt = $conn->prepare("INSERT INTO feed_formula_detail (feed_rawMaterial_name, feed_formulaType, quantity, type, client_id) VALUES (?, ?, 0, ?, ?)");

            foreach ($formulaTypes as $formulaType) {
                $stmt->bind_param("sssi", $name, $formulaType, $type, $client_id);
                if (!$stmt->execute()) {
                    throw new Exception("Insert failed: " . $stmt->error);
                }
            }

            $conn->commit();
            echo "<h3>All rows inserted successfully for client ID: $client_id</h3>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p class='error'>Transaction failed: " . $e->getMessage() . "</p>";
        }

        $stmt->close();
    }

    $conn->close();
    ?>

    <form action="feed_formula_details.php" method="post">
        <input type="submit" value="Done">
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
