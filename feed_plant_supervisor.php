<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login/login.php");
    exit;
}

$links = [
    ["name" => "Feed Formula", "url" => "https://sunfra.com/farm/feedformuladetails/feed_formula_details.php"],
	["name" => "Feed Raw Material", "url" => "https://sunfra.com/farm/feedrawmaterial/feed_raw_material.php"],
	["name" => "Feed Material to shead", "url" => "https://sunfra.com/farm/feedsheadfeeding/feed_shead_feeding.php"],
	["name" => "Water Medicine", "url" => "https://sunfra.com/farm/sanitization/sanitization.php"],
	["name" => "Update Raw Material", "url" => "https://sunfra.com/farm/feednewstockloading/feed_new_stock_loading.php"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Links Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }
        h1, h2 {
            text-align: center;
            color: #555;
        }
        .links-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .link-item {
            margin: 10px 0;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .link-item a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .link-item:hover {
            background-color: #f0f8ff;
        }
        .link-item:hover a {
            color: #0056b3;
        }
        .logout-button {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #ff4d4d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .logout-button:hover {
            background-color: #e63939;
        }
    </style>
</head>
<body>
	<div class="back-button">
		<button class="button" onclick="window.location.href='https://sunfra.com/farm/index.php'">Go Back</button>
    </div>    <h1>Feed Plant Supervisor</h1>
    <h2>Hello, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>

    <div class="links-container">
        <?php foreach ($links as $link): ?>
            <div class="link-item">
                <a href="<?= htmlspecialchars($link['url']) ?>">
                    <?= htmlspecialchars($link['name']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <a class="logout-button" href="login/logout.php">Logout</a>
</body>
</html>
