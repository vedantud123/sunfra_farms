<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login/login.php");
    exit;
}
		
$links = [
    ["name" => "Feed Material Price", "url" => "https://sunfra.com/farm/profit_and_loss_details/feed_material_price_perkg.php"],
    ["name" => "Egg Price Per Piece", "url" => "https://sunfra.com/farm/profit_and_loss_details/egg_cutting_price.php"],
	["name" => "Water Medicine And Sanitization Price (In Lit)", "url" => "https://sunfra.com/farm/profit_and_loss_details/water_medicine_and_sanitization_price.php"],
	["name" => "Profit And Loss Summary", "url" => "https://sunfra.com/farm/profit_and_loss_details/profit_and_loss_daily.php"],
	["name" => "Summary Report", "url" => "https://sunfra.com/farm/dashboard.php"],
	["name" => "Vaccination", "url" => "https://sunfra.com/farm/profit_and_loss_details/vaccination_costing.php"],
	["name" => "Labour Salary", "url" => "https://sunfra.com/farm/profit_and_loss_details/labour_salary_details.php"],
	["name" => "Water Sprayer", "url" => "https://sunfra.com/farm/profit_and_loss_details/voltage_water_spray.php"]
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
		.go-back-button {
			display: block;
			width: 120px;
			margin: 20px auto;
			padding: 10px;
			text-align: center;
			background-color: #007bff;
			color: white;
			text-decoration: none;
			border-radius: 5px;
			font-weight: bold;
			border: none;
			cursor: pointer;
		}
		.go-back-button:hover {
			background-color: #218838;
		}
    </style>
</head>
<body>
    <h1>Welcome to Sunfra Store</h1>
    <h2>Hello, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
	 
<button class="go-back-button" onclick="window.location.href='https://sunfra.com/farm/index.php';">Go Back</button>

    <div class="links-container">
        <?php foreach ($links as $link): ?>
            <div class="link-item">
                <a href="<?= htmlspecialchars($link['url']) ?>">
                    <?= htmlspecialchars($link['name']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
	
    <a class="logout-button" href="https://sunfra.com/farm/login/logout.php">Logout</a>
</body>
</html>
