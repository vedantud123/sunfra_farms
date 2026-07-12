<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$shead_name = $no_of_eggs = $sale = $type = ''; 

if ($id >= 1) {
    $query = "SELECT * FROM egg_godown_stock WHERE id = $id";
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $shead_name = $row["shead_name"];
            $no_of_eggs = $row["no_of_eggs"];
            $sale = $row["sale"];
        }
        $result->free();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = $mysqli->real_escape_string(trim($_POST['shead_name'] ?? ''));
    $no_of_trays = $mysqli->real_escape_string($_POST['no_of_trays']);
	$no_of_loose_Eggs = $mysqli->real_escape_string($_POST['no_of_loose_Eggs'] ?? 0);
	$no_of_eggs = $no_of_trays * 30 + $no_of_loose_Eggs;
    $sale = $mysqli->real_escape_string(trim($_POST['sale'] ?? ''));
    $type_of_eggs = "Damaged";
	$remarks = "Return";
    $sale_price = 0;

    if ($id >= 1) {
        $query = "UPDATE egg_godown_stock 
                  SET shead_name='$shead_name', no_of_eggs=$no_of_eggs, sale='$sale', 
                      type_of_eggs='$type_of_eggs' 
                  WHERE id=$id";
    } else {
        $timestamp = date('Y-m-d H:i:s');
        $query = "INSERT INTO egg_godown_stock 
                  (shead_name, no_of_eggs, type_of_eggs, timestamp, sale_price, sale, remarks) 
                  VALUES ('$shead_name', $no_of_eggs, '$type_of_eggs', 
                          '$timestamp', $sale_price, '$sale', '$remarks')";
    }

    if ($mysqli->query($query)) {
        header("Location: /farm/egg_godown/egg_godown_sale.php");
        exit;
    } else {
        echo "Error: " . $mysqli->error . "<br>";
        echo "SQL Query: " . $query;
    }
}

$date = date('Y-m-d');
$sql = "SELECT sale FROM egg_godown_stock WHERE DATE(`timestamp`) = '$date' AND sale IS NOT NULL AND remarks != 'Return' AND sale != 'Scrap' GROUP BY sale;"; 
$sale_result = $mysqli->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feed Raw Material</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
            margin: 0;
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
        h1 {
            text-align: center;
            color: #007bff;
        }
        .back-button {
            display: block;
            margin: 20px auto;
            text-align: center;
        }
        .back-button button {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="button-container">
        <button class="Go-Back" onclick="window.location.href='https://sunfra.com/farm/egg_godown/egg_godown_sale.php';">Go Back</button>
    </div>
    <h1>Damage Eggs</h1>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        
        <p>
			<label for="shead_name">Shead Name:</label>
            <select name="shead_name" id="shead_name">
				<option value="">Select option</option>
                <option value="Shead 1" <?= $shead_name === 'Shead 1' ? 'selected' : '' ?>>Shead 1</option>
                <option value="Shead 2" <?= $shead_name === 'Shead 2' ? 'selected' : '' ?>>Shead 2</option>
				<option value="Shead 3" <?= $shead_name === 'Shead 3' ? 'selected' : '' ?>>Shead 3</option>
				<option value="Shead 4" <?= $shead_name === 'Shead 4' ? 'selected' : '' ?>>Shead 4</option>
				<option value="Shead 5" <?= $shead_name === 'Shead 5' ? 'selected' : '' ?>>Shead 5</option>
				<option value="Shead 6" <?= $shead_name === 'Shead 6' ? 'selected' : '' ?>>Shead 6</option>
				<option value="Shead 7" <?= $shead_name === 'Shead 7' ? 'selected' : '' ?>>Shead 7</option>
				<option value="Shead 8" <?= $shead_name === 'Shead 8' ? 'selected' : '' ?>>Shead 8</option>
			</select>
        </p>
        
         <p>
            <label for="no_of_trays">No Of Trays:</label>
            <input type="text" name="no_of_trays" id="no_of_trays" value="<?= htmlspecialchars($no_of_trays) ?>" >
        </p>
		 <p>
            <label for="no_of_loose_Eggs">No Of Loose Eggs:</label>
            <input type="text" name="no_of_loose_Eggs" id="no_of_loose_Eggs" value="<?= htmlspecialchars($no_of_loose_Eggs) ?>" >
        </p>
        
        <p>
            <label for="sale">Party Name:</label>
                <select name="sale" id="sale" required>
                    <option value="">--Select--</option>
                    <?php
                    if ($sale_result->num_rows > 0) {
                        while ($row = $sale_result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['sale']) . '">' . htmlspecialchars($row['sale']) . '</option>';
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
</body>
</html>
