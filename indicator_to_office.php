<?php
date_default_timezone_set('Asia/Kolkata');

$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if (!$conn) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$timestamp = $shead_name = $formula = '';

if (!empty($_GET['shead_name']) && !empty($_GET['formula']) && !empty($_GET['timestamp'])) {
    $timestamp = mysqli_real_escape_string($conn, $_GET['timestamp']);
    $shead_name = mysqli_real_escape_string($conn, $_GET['shead_name']);
    $formula = mysqli_real_escape_string($conn, $_GET['formula']);

    $new_timestamp_unix = strtotime($timestamp);
    if (!$new_timestamp_unix) {
        die("ERROR: Invalid timestamp format.");
    }

    $check_sql = "SELECT timestamp FROM feed_indicator_logs ORDER BY timestamp DESC LIMIT 1";
    $check_result = mysqli_query($conn, $check_sql);
    $last_timestamp = null;
    
    if ($row = mysqli_fetch_assoc($check_result)) {
        $last_timestamp = $row['timestamp'];
    }

    $last_timestamp_unix = $last_timestamp ? strtotime($last_timestamp) : 0;

    if ($last_timestamp === null || ($new_timestamp_unix - $last_timestamp_unix) >= 360) {
        $sql = "INSERT INTO feed_indicator_logs (timestamp, shead_name, formula) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql);
        $stmt_insert->bind_param("sss", $timestamp, $shead_name, $formula);
        
        if (!$stmt_insert->execute()) {
            die("ERROR: Could not insert log: " . $stmt_insert->error);
        }
        $stmt_insert->close();
    } else {
        exit;
    }
} else {
    die("ERROR: Missing parameters: timestamp, shead_name, or formula.");
}

$shead_name = strtolower(str_replace(" ", "_", $shead_name));
if (stripos($shead_name, "chick") !== false) {
    $shead_name = "chick";
} elseif (stripos($shead_name, "grower") !== false) {
    $shead_name = "grower";
}

$medicine_sql = "SELECT feed_rawMaterial_name, quantity FROM feed_formula_details WHERE feed_formulaType = ?";
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
	
    $stockQuery = "UPDATE feed_rawMaterials SET stock = stock - ? WHERE name = ?";
    $stmt2 = $conn->prepare($stockQuery);
    $stmt2->bind_param("ds", $medicine_quantity, $material_name);
    
    if (!$stmt2->execute()) {
        die("ERROR: Could not update stock: " . $stmt2->error);
    }
    $stmt2->close();

    $insertQuery = "INSERT INTO feed_material_reduction_logs (material_name, reduced_quantity, timestamp) VALUES (?, ?, ?)";
    $stmt3 = $conn->prepare($insertQuery);
    $stmt3->bind_param("sis", $material_name, $medicine_quantity, $timestamp);
    
    if (!$stmt3->execute()) {
        die("ERROR: Could not insert log: " . $stmt3->error);
    }
    $stmt3->close();
}

$stmt1->close();
mysqli_close($conn);

echo "Process completed successfully!";
?>
