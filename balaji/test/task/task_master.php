<?php
session_start();

date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$locations = ["Feed_Godown", "Egg_Godown", "Gate_Manager", "Shead_1", "Shead_2", "Shead_3", "Shead_4", "Shead_5", "Shead_6", "Shead_7", "Shead_8", "Chick", "Grower"];
$date = date('Y-m-d');

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
$emp_query = "SELECT name FROM labour_master WHERE client_id = ? ORDER BY name ASC";
$emp_stmt = $conn->prepare($emp_query);
$emp_stmt->bind_param("i", $client_id);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();
while ($row = $emp_result->fetch_assoc()) {
    $employee_names[] = $row['name'];
}
$emp_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["person_name"])) {
    $location = $_POST["location"];
    $person_name = $_POST["person_name"];

    $update_sql = "UPDATE task_master SET person_name = ?, assigned_at = ? WHERE location = ? AND assigned_date = ? AND client_id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ssssi", $person_name, $timestamp, $location, $date, $client_id);
    $stmt_update->execute();
    $stmt_update->close();
}

$result_stmt = $conn->prepare("SELECT * FROM task_master WHERE assigned_date = ? AND client_id = ?");
$result_stmt->bind_param("si", $date, $client_id);
$result_stmt->execute();
$result = $result_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign People to Locations</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; text-align: center; }
        .container { width: 60%; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #007bff; color: white; }
        select, button { padding: 5px; margin: 5px; border-radius: 5px; }
        .assign-btn { background: #28a745; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 5px; }
        .assign-btn:hover { background: #218838; }
    </style>

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

<div class="container">
    <h2>Assign People to Locations</h2>
    <button class="go-back-btn" onclick="window.location.href='https://sunfra.com/farm/test/attendance/showoption.php'">
        Go Back
    </button>
    <table>
        <tr>
            <th>Location</th>
            <th>Person Assigned</th>
            <th>Assigned At</th>
            <th>Assign / Update</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row["location"]) ?></td>
                <td><?= $row["person_name"] ? htmlspecialchars($row["person_name"]) : "Not Assigned" ?></td>
                <td><?= $row["assigned_at"] ?? 'Not Assigned' ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="location" value="<?= htmlspecialchars($row["location"]) ?>">
                        <select name="person_name" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employee_names as $emp): ?>
                                <option value="<?= htmlspecialchars($emp) ?>" <?= ($row["person_name"] == $emp) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="assign-btn">Assign / Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
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

<?php
$conn->close();
?>
