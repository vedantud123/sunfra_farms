<?php
session_start();

$links = [
    ["name" => "Feed Feeding Shead", "url" => "https://sunfra.com/farm/supervisorfeedfeedingshead/supervisor_feed_feeding_shead.php"],
    ["name" => "Shead Mortality", "url" => "https://sunfra.com/farm/supervisorsheadmortality/supervisor_shead_mortality.php"],
    ["name" => "Production Shead", "url" => "https://sunfra.com/farm/supervisorproductionshead/supervisor_production_shead.php"],
    ["name" => "Water Shead", "url" => "https://sunfra.com/farm/supervisorwatershead/supervisor_water_shead.php"],
	["name" => "Birds Weight", "url" => "https://sunfra.com/farm/supervisorbirdweight/supervisor_bird_weight.php"]
];

$servername = "216.172.184.173";
$username = "sunfra_farms";
$password = "sunfra_farms";
$dbname = "sunfra_farms";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date('Y-m-d');

$sql = "SELECT ba.sheadNo, bi.day, bi.type, bi.title, bi.description
        FROM batch ba
        JOIN bible bi ON (DATEDIFF(CURDATE(), ba.hatchDate) + 1) = bi.day
        ORDER BY ba.sheadNo, bi.day";

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheadNo = $row['sheadNo'];
        $day = $row['day'];
        $type = $row['type'];
        $title = $row['title'];
        $description = $row['description'];
        $status = 'Not Done';
        $timestamp = date('Y-m-d H:i:s');

        $check_sql = "SELECT * FROM `medicine_status_logs` WHERE sheadNo = ? AND day = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $sheadNo, $day);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            $insert_stmt = $conn->prepare("INSERT INTO `medicine_status_logs` (sheadNo, day, type, title, description, status, timestamp)
                                          VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssss", $sheadNo, $day, $type, $title, $description, $status, $timestamp);
            $insert_stmt->execute();
        }
        $check_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $sheadNo = $_POST['sheadNo'];
    $new_status = $_POST['status'];

    $update_sql = $conn->prepare("UPDATE `medicine_status_logs` SET status = ?, timestamp = NOW() WHERE sheadNo = ?");
    $update_sql->bind_param("ss", $new_status, $sheadNo);
    $update_sql->execute();
    $update_sql->close();

    header("Location: supervisor.php");
    exit();
}

$sql = "SELECT sheadNo, day, type, title, description, status, timestamp
        FROM `medicine_status_logs`
        WHERE status != 'Done' and DATE(timestamp) = CURDATE()
        ORDER BY CASE WHEN status = 'Not Done' THEN 1 ELSE 2 END, timestamp DESC";

$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
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
        .container {
            width: 700px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .update-col {
            width: 120px;
            text-align: center;
        }
        .update-status-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            margin-top: 10px;
        }
        .update-status-container select, .update-status-container button {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .update-status-container button {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .update-status-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="back-button">
    <button class="button" onclick="window.location.href='https://sunfra.com/farm/index.php'">Go Back</button>
</div>

<h1>Feed Plant Supervisor</h1>

<div class="links-container">
    <?php foreach ($links as $link): ?>
        <div class="link-item">
            <a href="<?= htmlspecialchars($link['url']) ?>"><?= htmlspecialchars($link['name']) ?></a>
        </div>
    <?php endforeach; ?>
</div>

<a class="logout-button" href="login/logout.php">Logout</a>
<?php if ($result->num_rows > 0) { ?>

<div class="container">
    <h2>Medicine Status Logs</h2>
    <table>
        <tr>
            <th>Day</th>
            <th>Type</th>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Timestamp</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['day']); ?></td>
                <td><?= htmlspecialchars($row['type']); ?></td>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= htmlspecialchars($row['description']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
                <td><?= htmlspecialchars($row['timestamp']); ?></td>
				<?php if ($row['status'] != 'Done') { ?>

                <td class="update-col">
                    <?php if ($row['status'] !== 'Done') { ?>
                        <form action="" method="POST">
                            <input type="hidden" name="sheadNo" value="<?= htmlspecialchars($row['sheadNo']); ?>">
                            <div class="update-status-container">
                                <select name="status">
                                    <option value="Done">Done</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </div>
                        </form>
                    <?php } ?>
                </td>
		        <?php } ?>
            </tr>
        <?php } ?>
    </table>
</div>
<?php } ?>
</body>
</html>
