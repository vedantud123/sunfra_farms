<?php
session_start();

$json = file_get_contents("https://sunfra.com/farm/test2/login/farm_users_json.php"); 
$data = json_decode($json, true);

$error = "";

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: https://sunfra.com/farm/test2/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please fill in both fields.";
    } elseif ($data['status'] !== "success") {
        $error = "Unable to fetch user data.";
    } else {
        $matched = false;

        foreach ($data['users'] as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['client_id'] = $user['client_id'];
                $_SESSION['client_name'] = $user['client_name'];
                $_SESSION['status'] = $user['status'];

                $matched = true;
                break;
            }
        }

        if ($matched) {
            header("Location: https://sunfra.com/farm/test2/index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Farm App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <h1>Login</h1>
        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <input type="submit" value="Login">

            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
