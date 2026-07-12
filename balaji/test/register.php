<?php
session_start();

$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = intval($_POST['client_id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($client_id) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!ctype_lower($username)) {
        $error = "Username must be in lowercase.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM farm_users WHERE username = ? AND client_id = ?");
        $stmt->bind_param("si", $username, $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already exists for this client.";
        } else {
            $stmt = $conn->prepare("INSERT INTO farm_users (username, password, client_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $username, $password, $client_id);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

// Fetch clients for dropdown
$clients = [];
$clientResult = $conn->query("SELECT id, name FROM clients");
while ($row = $clientResult->fetch_assoc()) {
    $clients[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Multi Client</title>
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
        input[type="text"], input[type="password"], select {
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
        .error {
            color: red;
            font-size: 14px;
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
            const regex = /^[a-z]*$/;
            if (!regex.test(input.value)) {
                message.style.display = 'block';
                message.textContent = 'Please enter only lowercase letters.';
            } else {
                message.style.display = 'none';
            }
        }
    </script>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar { transition: all 0.3s ease; }
    .sidebar a:hover { background-color: #2b6cb0; color: white; }
    .collapsed { width: 64px !important; }
    .collapsed .sidebar-text { display: none; }
    .collapsed nav a { justify-content: center; }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .sidebar.show { display: block; }
    }
  </style>
</head>
<?php session_start(); ?>
<?php $clientName = $_SESSION['client_name'] ?? 'Yours'; ?>
<body class="bg-gray-100 text-gray-800">
    <div class="register-container">
        <h2>Registration</h2>
        <form action="register.php" method="POST">
            <p>
                <label for="client_id">Client:</label>
                <select name="client_id" id="client_id" required>
                    <option value="">-- Select Client --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
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
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
        </form>
    </div>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
