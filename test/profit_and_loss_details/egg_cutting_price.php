<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$shead_name = $cutting_price = '';

if ($id >= 1) {
    $stmt = $mysqli->prepare("SELECT * FROM egg_cutting_price WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();

    if ($row = $edit_result->fetch_assoc()) {
        $shead_name = $row["shead_name"];
        $cutting_price = $row["cutting_price"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $shead_name = trim($_POST['shead_name']);
    $cutting_price = floatval($_POST['cutting_price']);

    if ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE egg_cutting_price SET shead_name = ?, cutting_price = ? WHERE id = ? AND client_id = ?");
        $stmt->bind_param("sdii", $shead_name, $cutting_price, $id, $client_id);
    } else {
		$check_stmt = $mysqli->prepare("SELECT id FROM egg_cutting_price WHERE shead_name = ? AND client_id = ?");
		$check_stmt->bind_param("si", $shead_name, $client_id);
		$check_stmt->execute();
		$check_stmt->store_result();

		if ($check_stmt->num_rows > 0) {
			echo "<script>alert('❌ Entry for this Shead already exists!'); window.location.href='egg_cutting_price.php';</script>";
			exit;
		}
		$check_stmt->close();
		$stmt = $mysqli->prepare("INSERT INTO egg_cutting_price (shead_name, cutting_price, client_id) VALUES (?, ?, ?)");
		$stmt->bind_param("sdi", $shead_name, $cutting_price, $client_id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/profit_and_loss_details/egg_cutting_price.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

$records_stmt = $mysqli->prepare("SELECT * FROM egg_cutting_price WHERE client_id = ?");
$records_stmt->bind_param("i", $client_id);
$records_stmt->execute();
$records_result = $records_stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Egg Price Per Piece</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* 1. Force your sidebar original blue background, overriding Tailwind */
.sidebar {
  background-color: #0d6efd ; /* your original shade */
}

/* 2. Reset Tailwind’s text alignment and fonts for body */
html, body {
  font-family: Arial, sans-serif !important;
  margin: 0 !important;
  padding: 20px !important;
  background-color: #f4f4f4 !important;
  color: #333 !important;
  text-align: center !important;
  line-height: 1.5 !important;
}

/* 3. Headings style */
h1, h2 {
  font-weight: 700 !important;
  color: #333 !important;
  text-align: center !important;
  font-size: 1.75rem !important;
  margin-bottom: 20px !important;
  line-height: 1.2 !important;
}

/* 4. Form container */
form {
  max-width: 500px !important;
  margin: 0 auto 40px !important;
  background-color: #fff !important;
  padding: 20px !important;
  border-radius: 10px !important;
  box-shadow: 0 0 10px rgba(0,0,0,0.1) !important;
  font-family: Arial, sans-serif !important;
  text-align: left !important;
}

/* 5. Form rows alignment */
form p {
  display: flex !important;
  justify-content: space-between !important;
  align-items: center !important;
  margin: 10px 0 !important;
}

/* 6. Labels */
label {
  flex: 1 1 40% !important;
  font-weight: 700 !important;
  text-align: left !important;
  padding-right: 10px !important;
  white-space: nowrap !important;
}

/* 7. Inputs and selects */
input, select {
  flex: 2 1 55% !important;
  padding: 8px !important;
  font-size: 14px !important;
  border: 1px solid #ccc !important;
  border-radius: 5px !important;
  box-sizing: border-box !important;
  background-color: #fff !important;
}

/* 8. Submit button full width */
button[type="submit"] {
  width: 100% !important;
  background-color: #007bff !important;
  font-weight: 700 !important;
  padding: 12px !important;
  border-radius: 5px !important;
  border: none !important;
  cursor: pointer !important;
  transition: background-color 0.3s ease !important;
}

button[type="submit"]:hover {
  background-color: #0056b3 !important;
}

/* 9. Button container */
.button-container {
  margin-bottom: 20px !important;
  text-align: center !important;
}

/* 10. Table styling */
table {
  width: 80% !important;
  max-width: 800px !important;
  margin: 30px auto !important;
  border-collapse: collapse !important;
  background: #fff !important;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
  border-radius: 8px !important;
  overflow: hidden !important;
  font-size: 14px !important;
  text-align: center !important;
}

th, td {
  padding: 10px !important;
  border: 1px solid #ddd !important;
  vertical-align: middle !important;
}

th {
  background-color: #007bff !important;
  color: #fff !important;
  font-weight: 700 !important;
  white-space: nowrap !important;
}

td:first-child, th:first-child {
  background-color: #28a745 !important;
  color: white !important;
  font-weight: 700 !important;
  white-space: nowrap !important;
}

tr:nth-child(even) {
  background-color: #f8f9fa !important;
}

tr:hover {
  background-color: #f1f1f1 !important;
}

td a {
  color: #007bff !important;
  font-weight: 700 !important;
  text-decoration: none !important;
}

td a:hover {
  text-decoration: underline !important;
}

/* 11. Sidebar fixed and sizing */
.sidebar {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 250px !important;
  height: 100vh !important;
  padding: 20px 10px !important;
  overflow-y: auto !important;
  box-sizing: border-box !important;
  transition: width 0.3s ease !important;
  z-index: 1050 !important;
  /* background-color forced above */
}

.sidebar.collapsed {
  width: 50px !important;
  padding: 20px 5px !important;
  overflow-x: hidden !important;
}

.sidebar.collapsed .sidebar-text {
  display: none !important;
}

/* 12. Main content margin */
.main-content {
  margin-left: 250px !important;
  padding: 20px !important;
  box-sizing: border-box !important;
  min-height: 100vh !important;
  transition: margin-left 0.3s ease !important;
  text-align: left !important;
}

.main-content.collapsed {
  margin-left: 50px !important;
}

/* 13. Responsive adjustments */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%) !important;
    width: 250px !important;
  }
  .sidebar.show {
    transform: translateX(0) !important;
  }
  .main-content,
  .main-content.collapsed {
    margin-left: 0 !important;
    padding: 10px !important;
    text-align: left !important;
  }
}.back-button {
		  display: inline-block;
		  margin-bottom: 1rem;
		  background-color: #3498db; /* Blue background */
		  color: white;
		  padding: 10px 16px;
		  border-radius: 6px;
		  text-decoration: none;
		  font-weight: 600;
		  transition: background-color 0.3s ease;
		}

		.back-button:hover {
		  background-color: #217dbb; /* Darker blue on hover */
		}.centered {
		  text-align: center;
		  margin-bottom: 1rem; /* optional spacing */
		}
    </style>
</head>
<body>
<div>
	<aside id="sidebar" class="sidebar bg-blue-800 text-white p-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-xl font-semibold sidebar-text"><?= htmlspecialchars($clientName) ?></h2>
		<button id="collapse-btn" class="text-white">
		  <i class="fas fa-angle-double-left"></i>
		</button>
      </div>

      <nav class="space-y-2">
         <a href="https://sunfra.com/farm/test/test_dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-home"></i> <span class="sidebar-text">Home</span>
        </a>
        <a href="https://sunfra.com/farm/test/batch_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-globe"></i> <span class="sidebar-text">Batch</span>
        </a>
        <a href="https://sunfra.com/farm/test/weighbridge_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-truck"></i> <span class="sidebar-text">WeighBridge</span>
        </a>
        <a href="https://sunfra.com/farm/test/tractor_production_mortality_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-tractor"></i> <span class="sidebar-text">Tractor Production Mortality</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_attendance.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-check"></i> <span class="sidebar-text">Attendance</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-tie"></i> <span class="sidebar-text">Shead Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-warehouse"></i> <span class="sidebar-text">Feed Plant Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-egg"></i> <span class="sidebar-text">Egg Godown Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-chart-line"></i> <span class="sidebar-text">Profit And Loss</span>
        </a>
        <a href="https://sunfra.com/farm/test/settings.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-sliders-h"></i> <span class="sidebar-text">Feature Settings</span>
        </a>
        <a href="https://sunfra.com" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-life-ring"></i> <span class="sidebar-text">Support</span>
        </a>
        <a href="https://sunfra.com/farm/test/logout.php" class="flex items-center gap-3 p-2 rounded hover:bg-red-600">
          <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text">Logout</span>
        </a>
      </nav>
    </aside>
<main class="main-content">
	<div class="centered">
	  <a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php" class="back-button">
		← Go Back
	  </a>
	</div>
    <h1>Egg Price Per Piece</h1>
     
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="shead_name">Shead Name:</label>
            <select name="shead_name" id="shead_name">
				<option value="">Select option</option>
                <option value="Shead 1" <?= $shead_name === 'Shead 1' ? 'selected' : '' ?>>Shead 1</option>
                <option value="Shead 2" <?= $shead_name === 'Shead 2' ? 'selected' : '' ?>>Shead 2</option>
                <option value="Shead 3" <?= $shead_name === 'Shead 3' ? 'selected' : '' ?>>Shead 3</option>
                <option value="Shead 4" <?= $shead_name === 'Shead 4' ? 'selected' : '' ?>>Shead 4</option>
                <option value="Shead 5" <?= $shead_name === 'Shead 5' ? 'selected' : '' ?>>Shead 5</option>
                <option value="Shead 6" <?= $shead_name === 'Shead 6' ? 'selected' : '' ?>>Shead 6</option>
                <option value="Shead 7" <?= $shead_name === 'Shead 7' ? 'selected' : '' ?>>Shead 7</option>
                <option value="Shead 8" <?= $shead_name === 'Shead 8' ? 'selected' : '' ?>>Shead 8</option>
			</select>
        </p>
        <p>
            <label for="cutting_price">Cutting Price:</label>
            <input type="text" name="cutting_price" id="cutting_price" value="<?= htmlspecialchars($cutting_price) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

   <h2>Records</h2>
<table>
    <tr>
        <!--<th>ID</th>-->
        <th>Shead Name</th>
        <th>Cutting Price (Per Egg)</th>
        <th>Edit</th>
    </tr>
    <?php while ($row = $records_result->fetch_assoc()): ?>
    <tr>
        <!--<td><?= htmlspecialchars($row['id']) ?></td>-->
        <td><?= htmlspecialchars($row['shead_name']) ?></td>
        <td><?= htmlspecialchars($row['cutting_price']) ?></td>
        <td><a href="?id=<?= htmlspecialchars($row['id']) ?>">Edit</a></td>
    </tr>
    <?php endwhile; ?>
</table>
</main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
	  const sidebar = document.getElementById('sidebar');
	  const mainContent = document.querySelector('.main-content');
	  const collapseBtn = document.getElementById('collapse-btn');

	  collapseBtn?.addEventListener('click', function () {
		sidebar.classList.toggle('collapsed');
		mainContent.classList.toggle('collapsed');
		const icon = this.querySelector('i');
		if (icon) {
		  icon.classList.toggle('fa-angle-double-left');
		  icon.classList.toggle('fa-angle-double-right');
		}
	  });

	  menuBtn?.addEventListener('click', function () {
		sidebar.classList.toggle('show');
	  });
	});
</script>
</body>
</html>
