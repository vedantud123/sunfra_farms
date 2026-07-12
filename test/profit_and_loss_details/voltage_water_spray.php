<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_errno) {
    die("Failed to connect: " . $mysqli->connect_error);
}

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$mac_to_shead = [
    "34:94:54:97:0B:7A" => "Shead_1",
    "EC:FA:BC:45:58:0A" => "Shead_2",
    "08:3A:8D:E6:D2:4F" => "Shead_3",
    "CC:50:E3:20:37:5D" => "Shead_4",
    "08:3A:8D:E6:DF:E0" => "Shead_5",
    "08:3A:8D:E6:DE:9D" => "Shead_6",
    "08:3A:8D:E6:E1:68" => "Shead_7",
    "CC:50:E3:0A:19:FA" => "Shead_8"
];

$result_data = [];

foreach ($mac_to_shead as $mac => $shead) {
    $stmt = $mysqli->prepare("SELECT timestamp FROM voltage WHERE mac_address = ? AND status = 'on' AND DATE(timestamp) = ? AND client_id = ? ORDER BY timestamp ASC");

    if (!$stmt) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        continue;
    }

    $stmt->bind_param("ssi", $mac, $selected_date, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $timestamps = [];
    while ($row = $result->fetch_assoc()) {
        $timestamps[] = strtotime($row['timestamp']);
    }

    $filtered_times = [];
    $last_time = null;

    for ($i = 0; $i < count($timestamps); $i++) {
        $base_time = $timestamps[$i];

        if (($i + 4) < count($timestamps)) {
            $all_within_5min = true;

            for ($j = 1; $j <= 4; $j++) {
                if (($timestamps[$i + $j] - $base_time) > 300) {
                    $all_within_5min = false;
                    break;
                }
            }

            if ($all_within_5min && ($last_time === null || ($base_time - $last_time) >= 900)) {
                $filtered_times[] = date('H:i', $base_time);
                $last_time = $base_time;
            }
        }
    }

    $result_data[$shead] = $filtered_times;
    $stmt->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Shead ON Times</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 30px;
        background-color: #f9f9f9;
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
    }

    form {
        text-align: center;
        margin-bottom: 30px;
    }

    label {
        font-size: 18px;
        margin-right: 10px;
        font-weight: bold;
    }

    input[type="date"] {
        padding: 10px;
        font-size: 16px;
        border: 1px solid #aaa;
        border-radius: 6px;
        background-color: #fff;
        outline: none;
        transition: all 0.3s ease-in-out;
    }

    input[type="date"]:hover,
    input[type="date"]:focus {
        border-color: #3f51b5;
        box-shadow: 0 0 5px rgba(63, 81, 181, 0.4);
    }

    button {
        padding: 10px 20px;
        font-size: 16px;
        background-color: #3f51b5;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        margin-left: 10px;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #2c3e9f;
    }

    table {
        border-collapse: collapse;
        width: 80%;
        margin: 0 auto;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    th, td {
        border: 1px solid #ccc;
        padding: 17px 19px;
        text-align: left;
        vertical-align: top;
    }

    th {
        background-color: #3f51b5;
        color: #fff;
    }

    td span {
        display: inline-block;
        margin: 3px 6px;
        padding: 4px 10px;
        background-color: #e3f2fd;
        color: #333;
        border-radius: 5px;
        font-size: 14px;
    }

    td i {
        color: #888;
        font-style: italic;
    }
</style>

</head>
<body>

<h2>ON Times per Shead</h2>

<form method="get">
    <label for="date">Select Date: </label>
    <input type="date" name="date" id="date" value="<?= $selected_date ?>">
    <button type="submit">View</button>
</form>

<table>
    <tr>
        <th>Shead</th>
        <th>ON Times for <?= htmlspecialchars($selected_date) ?></th>
    </tr>
    <?php foreach ($result_data as $shead => $times): ?>
        <tr>
            <td><?= $shead ?></td>
            <td>
                <?php
                if (!empty($times)) {
                    foreach ($times as $time) {
                        echo "<span>$time</span>";
                    }
                } else {
                    echo "<i>No data</i>";
                }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
