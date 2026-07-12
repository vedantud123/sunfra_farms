<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$allowedUsers = ['vedant', 'divya', 'venkat'];
$username = $_SESSION['username'] ?? '';

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$formula_query = "SELECT DISTINCT water_formulaType FROM water_formula_details ORDER BY water_formulaType";
$formula_result = $mysqli->query($formula_query);

$formula_types = [];
while ($row = $formula_result->fetch_assoc()) {
    $formula_types[] = $row['water_formulaType'];
}

$water_formulaType = "water_medicine";

$query = "SELECT 
    water_medicine_name AS Material,
    water_formulaType,
    quantity,
    type,
    id
FROM water_formula_details ORDER BY type, water_medicine_name, water_formulaType";
$result = $mysqli->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['type']][$row['Material']][$row['water_formulaType']] = [
        'quantity' => $row['quantity'],
        'id' => $row['id'],
    ];
}

$sending_raw_material_list_query = "SELECT name FROM feed_rawMaterial";
$sending_raw_material_list_result = $mysqli->query($sending_raw_material_list_query);

$sending_from_rawMaterial = [];
while ($row = $sending_raw_material_list_result->fetch_assoc()) {
    $sending_from_rawMaterial[] = $row['name'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $successMessages = [];
    $skippedDuplicates = 0;
    $insertedCount = 0;
    $updatedCount = 0;

    if (isset($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $id => $quantity) {
            if (is_numeric($quantity) && $quantity >= 0) {
                $update_query = "UPDATE water_formula_details SET quantity = ? WHERE id = ?";
                $stmt = $mysqli->prepare($update_query);
                $stmt->bind_param("di", $quantity, $id);
                $stmt->execute();
                $updatedCount++;
            }
        }
    }

    if (!empty($_POST['new_material']) && isset($_POST['new_quantities']) && !empty($_POST['new_type'])) {
        $new_material = $_POST['new_material'];
        $new_type = $_POST['new_type'];
        $quantities = $_POST['new_quantities'];

        foreach ($formula_types as $type) {
            $quantity = $quantities[$type] ?? 0;
            if (is_numeric($quantity) && $quantity >= 0) {
                $check_query = "SELECT id FROM water_formula_details WHERE water_medicine_name = ? AND water_formulaType = ? AND type = ?";
                $stmt_check = $mysqli->prepare($check_query);
                $stmt_check->bind_param("sss", $new_material, $type, $new_type);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows == 0) {
                    $insert_query = "INSERT INTO water_formula_details (water_medicine_name, water_formulaType, quantity, type) VALUES (?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($insert_query);
                    $stmt->bind_param("ssds", $new_material, $type, $quantity, $new_type);
                    $stmt->execute();
                    $insertedCount++;
                } else {
                    $skippedDuplicates++;
                }
                $stmt_check->close();
            }
        }
    }
	
	 if (!empty($_POST['second_new_material']) && isset($_POST['second_new_quantities']) && !empty($_POST['second_new_type'])) {
        $second_new_material = $_POST['second_new_material'];
        $second_new_type = $_POST['second_new_type'];
        $second_new_quantities = $_POST['second_new_quantities'];

        foreach ($formula_types as $type) {
            $quantity = $second_new_quantities[$type] ?? 0;
            if (is_numeric($quantity) && $quantity >= 0) {
                $check_query = "SELECT id FROM water_formula_details WHERE water_medicine_name = ? AND water_formulaType = ? AND type = ?";
                $stmt_check = $mysqli->prepare($check_query);
                $stmt_check->bind_param("sss", $second_new_material, $type, $second_new_type);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows == 0) {
                    $insert_query = "INSERT INTO water_formula_details (water_medicine_name, water_formulaType, quantity, type) VALUES (?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($insert_query);
                    $stmt->bind_param("ssds", $second_new_material, $type, $quantity, $second_new_type);
                    $stmt->execute();
                    $insertedCount++;
                } else {
                    $skippedDuplicates++;
                }
                $stmt_check->close();
            }
        }
    }
	
	 if (!empty($_POST['third_new_material']) && isset($_POST['third_new_quantities']) && !empty($_POST['third_new_type'])) {
        $third_new_material = $_POST['third_new_material'];
        $third_new_type = $_POST['third_new_type'];
        $third_new_quantities = $_POST['third_new_quantities'];

        foreach ($formula_types as $type) {
            $quantity = $third_new_quantities[$type] ?? 0;
            if (is_numeric($quantity) && $quantity >= 0) {
                $check_query = "SELECT id FROM water_formula_details WHERE water_medicine_name = ? AND water_formulaType = ? AND type = ?";
                $stmt_check = $mysqli->prepare($check_query);
                $stmt_check->bind_param("sss", $third_new_material, $type, $third_new_type);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows == 0) {
                    $insert_query = "INSERT INTO water_formula_details (water_medicine_name, water_formulaType, quantity, type) VALUES (?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($insert_query);
                    $stmt->bind_param("ssds", $third_new_material, $type, $quantity, $third_new_type);
                    $stmt->execute();
                    $insertedCount++;
                } else {
                    $skippedDuplicates++;
                }
                $stmt_check->close();
            }
        }
    }
	
	 if (!empty($_POST['fourth_new_material']) && isset($_POST['fourth_new_quantities']) && !empty($_POST['fourth_new_type'])) {
        $fourth_new_material = $_POST['fourth_new_material'];
        $fourth_new_type = $_POST['fourth_new_type'];
        $fourth_new_quantities = $_POST['fourth_new_quantities'];

        foreach ($formula_types as $type) {
            $quantity = $fourth_new_quantities[$type] ?? 0;
            if (is_numeric($quantity) && $quantity >= 0) {
                $check_query = "SELECT id FROM water_formula_details WHERE water_medicine_name = ? AND water_formulaType = ? AND type = ?";
                $stmt_check = $mysqli->prepare($check_query);
                $stmt_check->bind_param("sss", $fourth_new_material, $type, $fourth_new_type);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows == 0) {
                    $insert_query = "INSERT INTO water_formula_details (water_medicine_name, water_formulaType, quantity, type) VALUES (?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($insert_query);
                    $stmt->bind_param("ssds", $fourth_new_material, $type, $quantity, $fourth_new_type);
                    $stmt->execute();
                    $insertedCount++;
                } else {
                    $skippedDuplicates++;
                }
                $stmt_check->close();
            }
        }
    }

    if ($updatedCount > 0) {
        $successMessages[] = "$updatedCount rows updated successfully.";
    }
    if ($insertedCount > 0) {
        $successMessages[] = "$insertedCount new rows inserted successfully.";
    }
    if ($skippedDuplicates > 0) {
        $successMessages[] = "$skippedDuplicates duplicate entries skipped.";
    }

    if (!empty($successMessages)) {
        $finalMessage = implode("\\n", $successMessages);
        echo "<script>
                alert('$finalMessage');
                window.location.href = 'https://sunfra.com/farm/watermedicine/water_medicine.php';
              </script>";
        exit;
    } else {
        header("Location: https://sunfra.com/farm/watermedicine/water_medicine.php");
        exit;
    }
}
	
$shead_queries = ['1', '2', '3', '4', '5', '6', '7', '8', 'Chick', 'grower'];
$egg_data = [];

foreach ($shead_queries as $shead) {
	$hatchDate = 0;
    $day_query = "SELECT hatchDate FROM `batch` WHERE cullDate = '0000-00-00' AND sheadNo = ?";
    $day_stmt = $mysqli->prepare($day_query);
    
    if ($day_stmt) {
        $day_stmt->bind_param("s", $shead);
        $day_stmt->execute();
        $day_stmt->bind_result($hatchDate);
        $day_stmt->fetch();
        $day_stmt->close();
    } else {
        echo "Error in preparing statement: " . $mysqli->error;
        continue;
    }
    
    if (!empty($hatchDate)) {
        $startDateObj = new DateTime($hatchDate);
        $diff = $startDateObj->diff(new DateTime());
        $runningDays = $diff->days + 1;
    } else {
        $runningDays = "N/A";
    }
    
    if (is_numeric($runningDays)) {
        $runningWeeks = floor($runningDays / 7);
    } else {
        $runningWeeks = "N/A";
    }
    
    $duration = (is_numeric($runningDays) && $runningDays !== "Done") ? strval($runningWeeks) : "Done";
    
    $egg_data[] = [
        "Running_week" => $duration
    ];
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Raw Material Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 12px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 24px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 0px;
            text-align: center;
        }
        table th {
            background-color: #007bff;
            color: white;
            font-weight: 700;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        input[type="text"], select {
            padding: 8px;
            width: 50%;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <button onclick="window.location.href='https://sunfra.com/farm/watermedicine/water_medicine.php'">Go Back</button>
    <h1>Feed Raw Material Report</h1>
    <form method="POST" action="">
        <table>
			<thead>
                <tr>
                    <th>WEEK</th>
                    <?php foreach ($egg_data as $row): ?>
                        <th><?= htmlspecialchars($row['Running_week']) ?></th>
                     <?php endforeach; ?>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>Material</th>
                    <?php foreach ($formula_types as $type): ?>
                        <th><?php echo htmlspecialchars($type); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $type => $materials): ?>
                    <?php foreach ($materials as $material => $formulas): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material); ?></td>
                            <?php foreach ($formula_types as $formulaType): ?>
                                <td>
                                    <?php if (isset($formulas[$formulaType])): ?>
                                        <input type="text" name="quantity[<?php echo $formulas[$formulaType]['id']; ?>]" 
                                               value="<?php echo htmlspecialchars($formulas[$formulaType]['quantity']); ?>">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <tr>
                    <td>
                        <select name="new_material">
                            <option value="">-- Select Material --</option>
                            <?php foreach ($sending_from_rawMaterial as $material): ?>
                                <option value="<?php echo htmlspecialchars($material); ?>">
                                    <?php echo htmlspecialchars($material); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <?php foreach ($formula_types as $type): ?>
                        <td>
                            <input type="text" name="new_quantities[<?php echo $type; ?>]" value="0">
                        </td>
                    <?php endforeach; ?>
                </tr>
				 <tr>
                    <td>
                        <select name="second_new_material">
                            <option value="">-- Select Material --</option>
                            <?php foreach ($sending_from_rawMaterial as $material): ?>
                                <option value="<?php echo htmlspecialchars($material); ?>">
                                    <?php echo htmlspecialchars($material); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <?php foreach ($formula_types as $type): ?>
                        <td>
                            <input type="text" name="second_new_quantities[<?php echo $type; ?>]" value="0">
                        </td>
                    <?php endforeach; ?>
                </tr>
				 <tr>
                    <td>
                        <select name="third_new_material">
                            <option value="">-- Select Material --</option>
                            <?php foreach ($sending_from_rawMaterial as $material): ?>
                                <option value="<?php echo htmlspecialchars($material); ?>">
                                    <?php echo htmlspecialchars($material); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <?php foreach ($formula_types as $type): ?>
                        <td>
                            <input type="text" name="third_new_quantities[<?php echo $type; ?>]" value="0">
                        </td>
                    <?php endforeach; ?>
                </tr>
				 <tr>
                    <td>
                        <select name="fourth_new_material">
                            <option value="">-- Select Material --</option>
                            <?php foreach ($sending_from_rawMaterial as $material): ?>
                                <option value="<?php echo htmlspecialchars($material); ?>">
                                    <?php echo htmlspecialchars($material); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <?php foreach ($formula_types as $type): ?>
                        <td>
                            <input type="text" name="fourth_new_quantities[<?php echo $type; ?>]" value="0">
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <div style="text-align: center; margin-top: 20px;">
            <input type="submit" value="Update">
        </div>
    </form>
</div>
<?php $mysqli->close(); ?>
</body>
</html>
