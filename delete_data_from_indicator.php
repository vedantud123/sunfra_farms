<?php
date_default_timezone_set('Asia/Kolkata');

$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if (!$conn) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$timestamp = $shead_name = $formula = '';
$new_stock_quantity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $conn->real_escape_string($_POST['shead_name']);
   

    $shead_name = strtolower(str_replace(" ", "_", $shead_name));
    if (stripos($shead_name, "chick") !== false) {
        $shead_name = "chick";
    } elseif (stripos($shead_name, "grower") !== false) {
        $shead_name = "grower";
    }

    $medicine_sql = "SELECT feed_rawMaterial_name, quantity FROM feed_formula_detail WHERE feed_formulaType = ?";
    $stmt1 = $conn->prepare($medicine_sql);
    $stmt1->bind_param("s", $shead_name);
    $stmt1->execute();
    $result = $stmt1->get_result();

    if (!$result) {
        die("ERROR: Could not fetch feed formula details.");
    }

    while ($row = $result->fetch_assoc()) {
        $material_name = $row['feed_rawMaterial_name'];
        $medicine_quantity = $row['quantity'];

        if ($material_name == "StoneGrit") { 
            $medicine_quantity = $medicine_quantity - 25; 
        }

        $stockQuery = "UPDATE feed_rawMaterial SET stock = stock + ? WHERE name = ?";
        $stmt2 = $conn->prepare($stockQuery);
        $stmt2->bind_param("ds", $medicine_quantity, $material_name);

        if (!$stmt2->execute()) {
            die("ERROR: Could not update stock: " . $stmt2->error);
        }
        $stmt2->close();
    }

    $stmt1->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Delete Indicator Logs Management</title>
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
    </style>
</head>
<body>
    <h1>Feed Delete Indicator Logs Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/feed_plant_supervisor.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="shead_name">Shead No:</label>
            <select id="shead_name" name="shead_name" required>
                <option value="">Select option</option>
                <option value="Shead_1" <?= ($shead_name === 'Shead_1' ? 'selected' : '') ?>>Shead_1</option>
                <option value="Shead_2" <?= ($shead_name === 'Shead_2' ? 'selected' : '') ?>>Shead_2</option>
                <option value="Shead_3" <?= ($shead_name === 'Shead_3' ? 'selected' : '') ?>>Shead_3</option>
                <option value="Shead_4" <?= ($shead_name === 'Shead_4' ? 'selected' : '') ?>>Shead_4</option>
                <option value="Shead_5" <?= ($shead_name === 'Shead_5' ? 'selected' : '') ?>>Shead_5</option>
				<option value="Shead_6" <?= ($shead_name === 'Shead_6' ? 'selected' : '') ?>>Shead_6</option>
                <option value="Shead_7" <?= ($shead_name === 'Shead_7' ? 'selected' : '') ?>>Shead_7</option>
                <option value="Shead_8" <?= ($shead_name === 'Shead_8' ? 'selected' : '') ?>>Shead_8</option>
                <option value="chick" <?= ($shead_name === 'chick' ? 'selected' : '') ?>>chick</option>
                <option value="grower" <?= ($shead_name === 'grower' ? 'selected' : '') ?>>grower</option>
            </select>
        </p>

        <p>
            <button type="submit">Submit</button>
        </p>
    </form>
</div>
</body>
</html>

<?php $conn->close(); ?>
