<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error);
}

$material = $_GET['material'] ?? '';
$client_id = intval($_GET['client_id'] ?? 0);
$price_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material = trim($_POST['material'] ?? '');
    $client_id = intval($_POST['client_id'] ?? 0);
    $price_per_kg = floatval($_POST['price_per_kg'] ?? 0);

    if (!empty($material) && $client_id > 0 && $price_per_kg > 0) {
        $sql = "INSERT INTO feed_rawmaterial_price (material, price_per_kg, client_id, date)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE price_per_kg = VALUES(price_per_kg), date = NOW()";

        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sdi", $material, $price_per_kg, $client_id);
            if ($stmt->execute()) {
                $price_message = "✅ Price updated successfully.";
            } else {
                $price_message = "❌ Execution error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $price_message = "❌ Statement preparation failed: " . $mysqli->error;
        }
    } else {
        $price_message = "⚠️ Please enter all required fields.";
    }
}

$mysqli->close();
header("Location: https://sunfra.com/farm/test/weighbridge_json_to_web.php");
exit;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Material Price</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
<body class="bg-gray-100 text-gray-800" class="container mt-5">
    <h3>Update Material Price</h3>

    <?php if (!empty($price_message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($price_message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Material Name</label>
            <input type="text" class="form-control" name="material" value="<?= htmlspecialchars($material) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Price per KG</label>
            <input type="number" step="0.01" class="form-control" name="price_per_kg" required>
        </div>

        <input type="hidden" name="client_id" value="<?= $client_id ?>">

        <button type="submit" class="btn btn-primary">💾 Save Price</button>
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
