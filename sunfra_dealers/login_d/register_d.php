<?php
session_start();

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_yugandhar_pf");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!ctype_lower($username)) {
        $error = "Username must be in lowercase.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM farm_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username is already taken.";
        } else {
            $stmt = $conn->prepare("INSERT INTO farm_users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                header("Location: login_d.php");
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Farms</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        p {
            margin: 15px 0;
            font-size: 14px;
            color: #555;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .welcome {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .validation-message {
            color: red;
            font-size: 12px;
            display: none;
            text-align: left;
        }
    </style>
    <script>
        function validateUsername(input) {
            const message = document.getElementById('username-validation');
            const regex = /^[a-z]*$/; // Only lowercase letters allowed

            if (!regex.test(input.value)) {
                message.style.display = 'block';
                message.textContent = 'Please enter only lowercase letters in the username.';
            } else {
                message.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="register-container">
        <div class="welcome">Welcome to Farms</div>
        <h2>Register</h2>
        <form action="register_d.php" method="POST">
            <p>
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required oninput="validateUsername(this)">
                <span id="username-validation" class="validation-message"></span>
            </p>
            <p>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </p>
            <p>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </p>
            <p>
                <input type="submit" value="Register">
            </p>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
