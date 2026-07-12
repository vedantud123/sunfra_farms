<?php
session_start();

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: ../index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM farm_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            if ($password == $row['password']) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $row['username'];
                $_SESSION['user_id'] = $row['id'];

                header("Location: ../index.php"); 
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
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
    <title>Login - Sunfra Farms</title>
    <style>
		body {
		  margin: 0;
		  padding: 0;
		  height: 100vh;
		  font-family: 'Segoe UI', sans-serif;
		  background: linear-gradient(to bottom right, #fff8e1, #ffe57f);
		  display: flex;
		  justify-content: center;
		  align-items: center;
		}

		.login-container {
		  background: #fffbea;
		  border: 5px dashed #fbc02d;
		  padding: 50px 40px 40px;
		  border-radius: 16px;
		  width: 100%;
		  max-width: 350px;
		  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
		  position: relative;
		  text-align: center;
		}

		.login-container::before {
		  content: "🥚";
		  font-size: 48px;
		  position: absolute;
		  top: -30px;
		  left: 50%;
		  transform: translateX(-50%);
		  background: #fffbea;
		  padding: 5px;
		  border-radius: 50%;
		  border: 3px solid #fbc02d;
		}

		.welcome {
		  font-size: 20px;
		  font-weight: bold;
		  color: #795548;
		  margin-top: 10px;
		}

		h1 {
		  color: #f57f17;
		  font-size: 26px;
		  margin: 10px 0 25px;
		}

		form {
		  text-align: left;
		  margin-top: 10px;
		}

		label {
		  display: block;
		  margin-bottom: 6px;
		  font-weight: bold;
		  color: #444;
		}

		input[type="text"],
		input[type="password"] {
		  width: 100%;
		  padding: 12px;
		  margin-bottom: 20px;
		  border: 2px solid #ffe082;
		  border-radius: 6px;
		  font-size: 16px;
		  background-color: #fffdf4;
		  transition: border-color 0.3s ease;
		}

		input[type="text"]:focus,
		input[type="password"]:focus {
		  border-color: #fbc02d;
		  outline: none;
		}

		input[type="submit"] {
		  background: #fdd835;
		  color: #333;
		  border: none;
		  padding: 12px;
		  font-size: 18px;
		  border-radius: 6px;
		  cursor: pointer;
		  transition: background 0.3s ease;
		  width: 100%;
		}

		input[type="submit"]:hover {
		  background: #fbc02d;
		}

		a {
		  color: #f57f17;
		  font-size: 14px;
		  text-decoration: none;
		  display: inline-block;
		  margin-top: 10px;
		}

		a:hover {
		  text-decoration: underline;
		}

		.error {
		  color: red;
		  font-size: 14px;
		  margin-top: 10px;
		  text-align: center;
		}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="welcome">Welcome to Sunfra Farms</div>
		<h1>Login</h1>
        <form action="login.php" method="POST">
            <p>
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </p>
            <p>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </p>
            <p>
                <input type="submit" value="Login">
            </p>
            <p>
                Don't have an account? <a href="register.php">Register</a>
            </p>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
