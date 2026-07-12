<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bird Weight Form</title>
    <style>
        /* Reset default styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e0f7fa, #fff);
            padding: 40px 20px;
        }

        .container {
            max-width: 650px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 28px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 600;
            margin-top: 15px;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        select {
            margin-top: 5px;
            padding: 10px 12px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: #007BFF;
            outline: none;
            background-color: #f0faff;
        }

        input[type="submit"] {
            margin-top: 25px;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 16px;
            background-color: #6c757d;
            color: #fff;
            font-size: 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #495057;
        }

        .center-msg {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
		<a class="back-button" href="https://sunfra.com/farm/supervisorbirdweight/supervisor_bird_weight.php">Go Back</a>
        <h1>Birds Weight</h1>

       <?php
		$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
		if ($mysqli->connect_error) {
			die("Connection failed: " . $mysqli->connect_error);
		}

		$id = $_REQUEST['id'] ?? null;
		$sheadNo = "";
		$birds = array_fill(1, 8, "");

		if ($id && is_numeric($id)) {
			$stmt = $mysqli->prepare("SELECT * FROM supervisor_birds_weight WHERE id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($row = $result->fetch_assoc()) {
				$sheadNo = $row["sheadNo"];
				for ($i = 1; $i <= 8; $i++) {
					$birds[$i] = $row["bird$i"];
				}
			}
			$stmt->close();
		}

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$sheadNo = $_POST['sheadNo'];
			$bird_values = [];
			for ($i = 1; $i <= 8; $i++) {
				$bird_values[$i] = (float) ($_POST["bird$i"] ?? 0);
			}

			$average = array_sum($bird_values) / 8;
			$timestamp = date('Y-m-d H:i:s');
			$today = date('Y-m-d');

			if (!empty($_POST['id'])) {
				$id = (int)$_POST['id'];
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
				$check = $mysqli->prepare("SELECT COUNT(*) FROM supervisor_birds_weight WHERE DATE(timestamp) = ? AND sheadNo = ?");
				$check->bind_param("ss", $today, $sheadNo);
				$check->execute();
				$check->bind_result($exists);
				$check->fetch();
				$check->close();

				if ($exists > 0) {
					echo "<div class='center-msg' style='color:red;'>❌ Data already exists today for Shead: $sheadNo</div>";
				} else {
					$insert = $mysqli->prepare("INSERT INTO supervisor_birds_weight 
						(sheadNo, bird1, bird2, bird3, bird4, bird5, bird6, bird7, bird8, birds_average, timestamp) 
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
					$insert->bind_param("sddddddddds", 
						$sheadNo, 
						$bird_values[1], $bird_values[2], $bird_values[3], $bird_values[4], 
						$bird_values[5], $bird_values[6], $bird_values[7], $bird_values[8], 
						$average, 
						$timestamp
					);

					if ($insert->execute()) {
						echo "<div class='center-msg' style='color:green;'>✅ Data inserted successfully</div>";
					} else {
						echo "<div class='center-msg' style='color:red;'>❌ Insert Error: {$insert->error}</div>";
					}
					$insert->close();
				}
			}
		}

		echo '<form method="post">';
		if ($id) {
			echo '<label for="id">ID:</label>
				  <input type="text" name="id" id="id" value="' . htmlspecialchars($id) . '" readonly>';
		}

		echo '<label for="sheadNo">Shead No:</label>
			  <select id="sheadNo" name="sheadNo" required>
				  <option value="">Select option</option>';
		$options = ["Shead_1", "Shead_2", "Shead_3", "Shead_4", "Shead_5", "Shead_6", "Shead_7", "Shead_8", "Chick", "Grower"];
		foreach ($options as $option) {
			$selected = ($sheadNo === $option) ? 'selected' : '';
			echo "<option value=\"$option\" $selected>$option</option>";
		}
		echo '</select>';

		for ($i = 1; $i <= 8; $i++) {
			$val = htmlspecialchars($birds[$i] ?? '');
			echo "<label for='bird$i'>Bird $i:</label>
				  <input type='number' step='0.01' name='bird$i' id='bird$i' value='$val' required>";
		}

		echo '<input type="submit" value="Submit">';
		echo '</form>';

		$mysqli->close();
		?>

    </div>
</body>
</html>
