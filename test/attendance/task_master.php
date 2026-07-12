<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;
if ($client_id <= 0) {
    die("Invalid client session.");
}

date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$locations = ["Feed_Godown", "Egg_Godown", "Gate_Manager", "Shead_1", "Shead_2", "Shead_3", "Shead_4", "Shead_5", "Shead_6", "Shead_7", "Shead_8", "Chick", "Grower"];
$date = date('Y-m-d');

// Ensure entries exist for all locations for this client/date
foreach ($locations as $location) {
    $check_sql = "SELECT id FROM task_master WHERE location = ? AND assigned_date = ? AND client_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ssi", $location, $date, $client_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $insert_sql = "INSERT INTO task_master (location, assigned_date, client_id) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("ssi", $location, $date, $client_id);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt->close();
}

$timestamp = date('Y-m-d H:i:s');
$employee_names = [];

// Get supervisors based on client_id
$emp_query = "SELECT name FROM farm_supervisor WHERE client_id = ?";
$stmt_emp = $conn->prepare($emp_query);
$stmt_emp->bind_param("i", $client_id);
$stmt_emp->execute();
$emp_result = $stmt_emp->get_result();
while ($row = $emp_result->fetch_assoc()) {
    $employee_names[] = $row['name'];
}
$stmt_emp->close();

// Update assignments
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["assignments"])) {
    foreach ($_POST["assignments"] as $location => $person_name) {
        $update_sql = "UPDATE task_master SET person_name = ?, assigned_at = ? WHERE location = ? AND assigned_date = ? AND client_id = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("ssssi", $person_name, $timestamp, $location, $date, $client_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
}

// Fetch assignments for today and current tenant
$result = $conn->prepare("SELECT * FROM task_master WHERE assigned_date = ? AND client_id = ?");
$result->bind_param("si", $date, $client_id);
$result->execute();
$assignments = $result->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign People to Locations</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; text-align: center; }
        .container { width: 70%; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        input[type="radio"] { margin: 5px; }
        .submit-btn { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; margin-top: 10px; }
        .submit-btn:hover { background: #218838; }
        .back-button {
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <a class="back-button" href="https://sunfra.com/farm/test/attendance/showoption.php">Go Back</a>
    <h2>Assign People to Locations</h2>
    <form action="" method="post">
        <table>
            <tr>
                <th>Location</th>
                <th>Person Assigned</th>
                <th>Assign / Update</th>
            </tr>
            <?php while ($row = $assignments->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["location"]) ?></td>
                    <td><?= $row["person_name"] ? htmlspecialchars($row["person_name"]) : "Not Assigned" ?></td>
                    <td>
                        <?php foreach ($employee_names as $emp): ?>
                            <label>
                                <input type="radio" name="assignments[<?= htmlspecialchars($row["location"]) ?>]"
                                       value="<?= htmlspecialchars($emp) ?>"
                                       <?= ($row["person_name"] == $emp) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($emp) ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <button type="submit" class="submit-btn">Submit Assignments</button>
    </form>
</div>

</body>
</html>

<?php
$conn->close();
?>
