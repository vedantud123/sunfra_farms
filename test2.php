<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "your_database";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sheds = [
    "Shed 1" => "00:1A:2B:3C:4D:5E",
    "Shed 2" => "00:1A:2B:3C:4D:5F",
    "Shed 3" => "00:1A:2B:3C:4D:60",
    "Shed 4" => "00:1A:2B:3C:4D:61",
    "Shed 5" => "00:1A:2B:3C:4D:62",
    "Shed 6" => "00:1A:2B:3C:4D:63",
    "Shed 7" => "00:1A:2B:3C:4D:64",
    "Shed 8" => "00:1A:2B:3C:4D:65",
];

$shedStatus = [];

foreach ($sheds as $shedName => $macAddress) {
    $query = "SELECT switch_status FROM device_data WHERE mac_address = '$macAddress' ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $shedStatus[$shedName] = ($row['switch_status'] == 1) ? "ON" : "OFF";
    } else {
        $shedStatus[$shedName] = "No Data";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shed Monitoring</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f9;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }
        .shed-box {
            width: 200px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .on {
            color: green;
        }
        .off {
            color: red;
        }
        .no-data {
            color: gray;
        }
    </style>
</head>
<body>

    <h2>Shed Monitoring System</h2>

    <div class="container">
        <?php foreach ($sheds as $shedName => $macAddress): ?>
            <div class="shed-box">
                <h3><?= htmlspecialchars($shedName); ?></h3>
                <p><strong>MAC:</strong> <?= htmlspecialchars($macAddress); ?></p>
                <p class="<?= strtolower($shedStatus[$shedName]); ?>">
                    Status: <?= htmlspecialchars($shedStatus[$shedName]); ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
