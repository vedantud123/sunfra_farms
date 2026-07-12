<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: text/html; charset=UTF-8");
date_default_timezone_set("Asia/Kolkata");

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    die("<div class='center-msg' style='color:red;'>❌ DB Connection Failed: " . $mysqli->connect_error . "</div>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sheadNo = $_POST['sheadNo'] ?? '';
    $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;

    if (empty($sheadNo) || $client_id === 0) {
        echo "<div class='center-msg' style='color:red;'>❌ Invalid input data</div>";
        exit;
    }

    $bird_values = [];
    for ($i = 1; $i <= 8; $i++) {
        $bird_values[$i] = isset($_POST["bird$i"]) ? (float) $_POST["bird$i"] : 0.0;
    }

    $average = array_sum($bird_values) / 40;
    $timestamp = date('Y-m-d H:i:s');
    $today = date('Y-m-d');

    if (!empty($_POST['id'])) {
        $id = (int) $_POST['id'];

        $update = $mysqli->prepare("UPDATE supervisor_birds_weight 
            SET sheadNo = ?, bird1 = ?, bird2 = ?, bird3 = ?, bird4 = ?, bird5 = ?, bird6 = ?, bird7 = ?, bird8 = ?, birds_average = ?, timestamp = ? 
            WHERE id = ?");
        $update->bind_param("sddddddddddi",
            $sheadNo,
            $bird_values[1], $bird_values[2], $bird_values[3], $bird_values[4],
            $bird_values[5], $bird_values[6], $bird_values[7], $bird_values[8],
            $average,
            $timestamp,
            $id
        );

        if ($update->execute()) {
            echo "<div class='center-msg' style='color:green;'>✅ Data updated successfully</div>";
        } else {
            echo "<div class='center-msg' style='color:red;'>❌ Update Error: {$update->error}</div>";
        }

        $update->close();
    } else {
        $check = $mysqli->prepare("SELECT COUNT(*) FROM supervisor_birds_weight WHERE DATE(timestamp) = ? AND sheadNo = ? AND client_id = ?");
        $check->bind_param("ssi", $today, $sheadNo, $client_id);
        $check->execute();
        $check->bind_result($exists);
        $check->fetch();
        $check->close();

        if ($exists > 0) {
            echo "<div class='center-msg' style='color:red;'>❌ Data already exists today for Shead: $sheadNo</div>";
        } else {
            $insert = $mysqli->prepare("INSERT INTO supervisor_birds_weight 
				(sheadNo, bird1, bird2, bird3, bird4, bird5, bird6, bird7, bird8, birds_average, timestamp, client_id) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$insert->bind_param("sddddddddssi", 
				$sheadNo, 
				$bird_values[1], $bird_values[2], $bird_values[3], $bird_values[4], 
				$bird_values[5], $bird_values[6], $bird_values[7], $bird_values[8], 
				$average, 
				$timestamp, 
				$client_id
			);

            if ($insert->execute()) {
                echo "<div class='center-msg' style='color:green;'>✅ Data inserted successfully</div>";
            } else {
                echo "<div class='center-msg' style='color:red;'>❌ Insert Error: {$insert->error}</div>";
            }

            $insert->close();
        }
    }
} else {
    echo "<div class='center-msg' style='color:red;'>❌ Invalid Request</div>";
}
?>
