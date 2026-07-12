<?php

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$apiUrl = "https://rt.ambientweather.net/v1/devices?applicationKey=134af5db96ee4c4facde6820bc14a01bfc86a92d3d224b4b877fc94671fd1cd9&apiKey=572bf73566ca44b587fa6d64303a2fc9b5b22c4d215542c8bf80e3c5d8b1b322";

$max_retries = 5;
$retry_delay = 60;

$response = false;
$attempts = 0;

while ($attempts < $max_retries) {
    $response = @file_get_contents($apiUrl);

    if ($response !== false) {
        break;
    }

    $attempts++;
    sleep($retry_delay);
}

if ($response === false) {
    die();
}

$data = json_decode($response, true);

if (!empty($data)) {
    $device = $data[0] ?? null;
    $weather = $device['lastData'] ?? null;
    $mac_address = $device['macAddress'] ?? null;

    if ($weather && $mac_address) {
        $dateutc = $weather['dateutc'] ?? null;

        $checkStmt = $conn->prepare("SELECT id FROM temperature WHERE mac_address = ? AND dateutc = ?");
        $checkStmt->bind_param("si", $mac_address, $dateutc);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            date_default_timezone_set('Asia/Kolkata');
            $datetime = date('Y-m-d H:i:s');

            $tempf = $weather['tempf'] ?? null;
            $tempc = ($tempf !== 'N/A') ? round(($tempf - 32) * (5 / 9), 2) : null;

            $humidity = $weather['humidity'] ?? null;
            $windspeed = $weather['windspeedmph'] ?? null;
            $windgust = $weather['windgustmph'] ?? null;
            $maxdailygust = $weather['maxdailygust'] ?? null;
            $winddir = $weather['winddir'] ?? null;
            $uv = $weather['uv'] ?? null;
            $solarradiation = $weather['solarradiation'] ?? null;
            $hourlyrain = $weather['hourlyrainin'] ?? null;
            $dailyrain = $weather['dailyrainin'] ?? null;
            $weeklyrain = $weather['weeklyrainin'] ?? null;
            $monthlyrain = $weather['monthlyrainin'] ?? null;
            $yearlyrain = $weather['yearlyrainin'] ?? null;
            $feelslike = $weather['feelsLike'] ?? null;
            $feelslikec = ($feelslike !== 'N/A') ? round(($feelslike - 32) * (5 / 9), 2) : null;
            $dewpoint = $weather['dewPoint'] ?? null;

            $stmt = $conn->prepare("INSERT INTO temperature 
                (mac_address, temperature, humidity, windspeed, windgust, maxdailygust, winddir, uv, solarradiation, 
                 hourlyrain, dailyrain, weeklyrain, monthlyrain, yearlyrain, feelslike, dewpoint, datetime, dateutc) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("ssdddddidddddddssi",
                $mac_address, $tempc, $humidity, $windspeed, $windgust, $maxdailygust, $winddir, $uv, $solarradiation,
                $hourlyrain, $dailyrain, $weeklyrain, $monthlyrain, $yearlyrain, $feelslikec, $dewpoint, $datetime, $dateutc);

            $stmt->execute();
            $stmt->close();
        }

        $checkStmt->close();
    }
}

$conn->close();
?>
