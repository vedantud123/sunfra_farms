<?php
	$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	date_default_timezone_set('Asia/Kolkata');

	$message = "";
	$submitted = false; 

	$machineNo = isset($_GET["machineNo"]) ? trim($_GET["machineNo"]) : null;
	$location = isset($_GET["location"]) ? trim($_GET["location"]) : null;

	if ($_SERVER["REQUEST_METHOD"] == "POST" && $machineNo && $location) {
		$username = trim($_POST["username"]);
		$password = trim($_POST["password"]);
		$duration = trim($_POST["duration"]);

		$check_sql = "SELECT * FROM rental_users WHERE username = ? AND password = ?";
		$stmt = $conn->prepare($check_sql);
		$stmt->bind_param("ss", $username, $password);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$current_timestamp = date("Y-m-d H:i:s");

			$insert_sql = "INSERT INTO rental_washing_machine (username, password, duration, machineNo, timestamp, location) VALUES (?, ?, ?, ?, ?, ?)";
			$insert_stmt = $conn->prepare($insert_sql);
			$insert_stmt->bind_param("ssssss", $username, $password, $duration, $machineNo, $current_timestamp ,$location);
			
			if ($insert_stmt->execute()) {
				$message = "Thanks for submitting the data.";
				$submitted = true; 
			} else {
				$message = "Login successful but failed to save duration.";
			}
			$insert_stmt->close();
		} else {
			$message = "Invalid username or password!";
		}
		$stmt->close();
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
        $message = "Machine number and location are required in the URL.";
    }
	$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rental User Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #83a4d4, #b6fbff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            background-color: #ffffff;
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 24px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #444;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .message {
            text-align: center;
            color: green;
            margin-top: 12px;
            font-weight: bold;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>User Login</h2>
       <?php if (!$submitted): ?>
			<form method="post" action="">
				<label>Username:</label>
				<input type="text" name="username" required>

				<label>Password:</label>
				<input type="password" name="password" required>
				
				<label>Select Duration:</label>
				<select name="duration" required>
					<option>Select Option</option>
					<option value="15 min">15 min</option>
					<option value="30 min">30 min</option>
					<option value="45 min">45 min</option>
				</select>

				<input type="submit" value="Submit">
			</form>
		<?php endif; ?>
        <p class="message"><?php echo $message; ?></p>
    </div>
</body>
</html>

