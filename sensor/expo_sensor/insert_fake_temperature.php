<?php

$host = "216.172.184.173";
$user = "sunfra_farms";
$password = "sunfra_farms";
$database = "sunfra_farms";


$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}

// ---------------- DATE RANGE ----------------
// Change dates if required
$startDate = new DateTime('2025-12-01');
$endDate   = new DateTime('2025-12-31');

// ---------------- SETTINGS ----------------
$intervalMinutes = 15;
$macAddress = "AA:BB:CC:DD:EE:01";

// ---------------- INSERT LOOP ----------------
while ($startDate <= $endDate) {

    for ($i = 0; $i < 24 * 60; $i += $intervalMinutes) {

        $dateTime = clone $startDate;
        $dateTime->modify("+$i minutes");

        // ----------- FAKE DATA GENERATION ----------
        $temperature = round(rand(220, 340) / 10, 1); // 22–34°C
        $humidity    = rand(45, 85);
        $windspeed   = round(rand(0, 50) / 10, 1);
        $windgust    = round(rand(0, 80) / 10, 1);
        $maxgust     = round(rand(0, 100) / 10, 1);
        $winddir     = rand(0, 360);
        $uv          = round(rand(0, 100) / 10, 1);
        $solar       = round(rand(0, 900), 1);

        $hourlyRain  = (rand(0, 10) > 8) ? round(rand(0, 20) / 10, 1) : 0;
        $dailyRain   = round(rand(0, 50) / 10, 1);
        $weeklyRain  = round(rand(0, 200) / 10, 1);
        $monthlyRain = round(rand(0, 800) / 10, 1);
        $yearlyRain  = round(rand(0, 3000) / 10, 1);

        $feelsLike   = round($temperature + rand(-20, 20) / 10, 1);
        $dewPoint    = round(rand(160, 240) / 10, 1);

        $datetimeStr = $dateTime->format('Y-m-d H:i:s');

        // ---------------- SQL INSERT ----------------
        $sql = "
            INSERT INTO temperature (
                temperature, datetime, humidity, windspeed, windgust,
                maxdailygust, winddir, uv, solarradiation,
                hourlyrain, dailyrain, weeklyrain, monthlyrain, yearlyrain,
                feelslike, dewpoint, mac_address, dateutc
            ) VALUES (
                '$temperature', '$datetimeStr', '$humidity', '$windspeed', '$windgust',
                '$maxgust', '$winddir', '$uv', '$solar',
                '$hourlyRain', '$dailyRain', '$weeklyRain', '$monthlyRain', '$yearlyRain',
                '$feelsLike', '$dewPoint', '$macAddress', '$datetimeStr'
            )
        ";

        $conn->query($sql);
    }

    // Next day
    $startDate->modify('+1 day');
}

echo "✅ Fake temperature data inserted successfully.";

$conn->close();
