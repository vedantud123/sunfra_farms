<?php 
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

date_default_timezone_set('Asia/Kolkata');

$id_sql = "SELECT id, weight, type FROM `feed_to_shead_box_logs` ORDER BY id DESC LIMIT 1";
$id_result = $mysqli->query($id_sql);

$last_id = 0;
$last_weight = 0;
$last_type = "";  

if ($id_result && $id_result->num_rows > 0) {
    $row = $id_result->fetch_assoc();
    $last_id = $row['id'];
    $last_weight = (int) $row['weight'];  
    $last_type = $row['type']; 
}

$search_weight_sql = "SELECT * FROM feed_to_shead_box WHERE id > '$last_id' ORDER BY id ASC";  
$search_weight_result = $mysqli->query($search_weight_sql);

if ($search_weight_result) {
    while ($search_row = $search_weight_result->fetch_assoc()) {
        $row_id = $search_row['id'];
        $current_weight = (int) $search_row['weight'];  
        $datetime = $search_row['datetime'];
        $type = "";

        if ($current_weight >= $last_weight + 50) {
            $type = "Peak";
        } 
        elseif ($current_weight <= $last_weight - 50) {
            $type = "Valley";
        }

        if (!empty($type)) {
            $insert_sql = "INSERT INTO `feed_to_shead_box_logs` (`weight`, `datetime`, `type`) 
                           VALUES ('$current_weight', '$datetime', '$type')";

            if ($mysqli->query($insert_sql) === TRUE) {
                echo "Data inserted successfully for ID: $row_id ($type) - Weight: $current_weight<br>";
                $last_weight = $current_weight;  
                $last_type = $type;  
            } else {
                echo "Error inserting data: " . $mysqli->error . "<br>";
            }
        }

        $update_sql = "UPDATE `feed_to_shead_box` SET status = 'Done' WHERE id = '$row_id'";
        $mysqli->query($update_sql);
    }
} else {
    echo "No records found to process.";
}

$mysqli->close();

?>
