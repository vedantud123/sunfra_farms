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
$name = $new_stock_quantity = $check_timestamp = '';

// Fetch existing entry
if ($id >= 1) {
    $query = "SELECT * FROM feed_new_stock_loading WHERE id = ? AND client_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $name = $row["name"];
        $new_stock_quantity = $row["new_stock_quantity"];
        $check_timestamp = $row["timestamp"];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = $mysqli->real_escape_string($_POST['name']);
    $new_stock_quantity = $mysqli->real_escape_string($_POST['new_stock_quantity']);

    // Only add stock if it's a new insert
    if ($id == 0) {
        $add_new_rawmaterial_query = "UPDATE feed_rawmaterial SET stock = stock + ? WHERE name = ? AND client_id = ?";
        $add_new_rawmaterial_stmt = $mysqli->prepare($add_new_rawmaterial_query);
        $add_new_rawmaterial_stmt->bind_param("dsi", $new_stock_quantity, $name, $client_id);
        $add_new_rawmaterial_stmt->execute();
        $add_new_rawmaterial_stmt->close();
    }

    // Rollback stock if editing
    if ($check_timestamp != '') {
        $reduce_query = "SELECT * FROM feed_new_stock_loading_logs WHERE timestamp = ? AND client_id = ?";
        $reduce_stmt = $mysqli->prepare($reduce_query);
        $reduce_stmt->bind_param("si", $check_timestamp, $client_id);
        $reduce_stmt->execute();
        $result = $reduce_stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $reduce_name = $row['name'];
            $reduce_qty = $row['new_stock_quantity'];

            $reduce_rawmaterial_query = "UPDATE feed_rawmaterial SET stock = stock - ? WHERE name = ? AND client_id = ?";
            $reduce_rawmaterial_stmt = $mysqli->prepare($reduce_rawmaterial_query);
            $reduce_rawmaterial_stmt->bind_param("dsi", $reduce_qty, $reduce_name, $client_id);
            $reduce_rawmaterial_stmt->execute();
            $reduce_rawmaterial_stmt->close();
        }
        $reduce_stmt->close();

        $delete_logs_query = "DELETE FROM feed_new_stock_loading_logs WHERE timestamp = ? AND client_id = ?";
        $delete_logs_stmt = $mysqli->prepare($delete_logs_query);
        $delete_logs_stmt->bind_param("si", $check_timestamp, $client_id);
        $delete_logs_stmt->execute();
        $delete_logs_stmt->close();
    }

    $timestamp = date('Y-m-d H:i:s');

    $log_insert_query = "INSERT INTO feed_new_stock_loading_logs (name, new_stock_quantity, timestamp, client_id) VALUES (?, ?, ?, ?)";
    $log_insert_stmt = $mysqli->prepare($log_insert_query);
    $log_insert_stmt->bind_param("sdsi", $name, $new_stock_quantity, $timestamp, $client_id);
    $log_insert_stmt->execute();
    $log_insert_stmt->close();

    if ($id > 0) {
        $update_query = "UPDATE feed_new_stock_loading SET name = ?, new_stock_quantity = ?, timestamp = ? WHERE id = ? AND client_id = ?";
        $stmt = $mysqli->prepare($update_query);
        $stmt->bind_param("sdsii", $name, $new_stock_quantity, $timestamp, $id, $client_id);
    } else {
        $insert_query = "INSERT INTO feed_new_stock_loading (name, new_stock_quantity, timestamp, client_id) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($insert_query);
        $stmt->bind_param("sdsi", $name, $new_stock_quantity, $timestamp, $client_id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: https://sunfra.com/farm/test/feednewstockloading/feed_new_stock_loading.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Dropdown for materials (filtered by client)
$sql = "SELECT name FROM feed_rawmaterial WHERE client_id = ? ORDER BY name";
$material_stmt = $mysqli->prepare($sql);
$material_stmt->bind_param("i", $client_id);
$material_stmt->execute();
$result = $material_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed New Stock Management</title>
	  <script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    /* General Body Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
        text-align: center;
    }

    /* Container for Centered Content */
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 10px 15px;
        text-align: center;
    }

    /* Form Styles */
    form {
        display: inline-block;
        text-align: left;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    form p {
        margin-bottom: 15px;
    }

    form label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    form input, form select, form button {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }

    form button {
        background-color: #007bff;
        color: white;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    form button:hover {
        background-color: #0056b3;
    }

    /* Back Button */
    .button-container {
        margin-bottom: 20px;
    }

    .button-container button {
        background-color: #6c757d;
        color: white;
        font-size: 14px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .button-container button:hover {
        background-color: #5a6268;
    }

    /* Title Styles */
    h1 {
        color: #007bff;
        margin: 20px 0;
        font-size: 24px;
    }

    /* Table Styles */
    .table-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        overflow-x: auto; /* Adds horizontal scroll for small screens */
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        background: white;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    table th, table td {
        border: 1px solid #ddd;
        padding: 10px;
        font-size: 14px;
        text-align: center;
    }

    table th {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }

    table tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    /* Responsive Design for Smaller Screens */
    @media (max-width: 600px) {
        h1 {
            font-size: 20px;
        }

        form {
            width: 90%;
            padding: 15px;
        }

        table th, table td {
            font-size: 12px;
            padding: 8px;
        }
    }.sidebar {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 250px;
		  height: 100vh;
		  background: #0d6efd;
		  color: #fff;
		  padding: 20px 10px;
		  transition: width 0.3s;
		  overflow-y: auto;
		  z-index: 1050;
		}

		.sidebar.collapsed {
		  width: 50px !important;
		  padding: 20px 0 !important;
		}

		.sidebar.collapsed .sidebar-text {
		  display: none !important;
		}

		.main-content {
		  margin-left: 250px;
		  transition: margin-left 0.3s;
		}

		.main-content.collapsed {
		  margin-left: 50px;
		}

		@media (max-width: 768px) {
		  .sidebar {
			position: fixed;
			left: 0; top: 0;
			height: 100vh;
			width: 250px;
			transform: translateX(-100%);
			transition: transform 0.3s;
			z-index: 1100;
			background: #0d6efd;
		  }
		  .sidebar.show {
			transform: translateX(0);
		  }
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
		}@media (max-width: 768px) {
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
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
    <h1>Feed New Stock Management</h1>
    <div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php';">Go Back</button>
    </div>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <p>
            <label for="name">Select Material:</label>
            <select name="name" id="name" required>
                <option value="">--Select--</option>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['name']) . '" ' . ($name == $row['name'] ? 'selected' : '') . '>' . htmlspecialchars($row['name']) . '</option>';
                    }
                } else {
                    echo '<option value="">No materials found</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label for="new_stock_quantity">New Stock Quantity:</label>
            <input type="number" step="0.01" name="new_stock_quantity" id="new_stock_quantity" value="<?= htmlspecialchars($new_stock_quantity) ?>" required>
        </p>
        <p>
            <button type="submit">Submit</button>
        </p>
    </form>

    <?php
$query = "SELECT * FROM feed_new_stock_loading WHERE client_id = $client_id ORDER BY id DESC";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Material Name</th>
                <th>New Stock Quantity</th>
                <th>Date & Time</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['new_stock_quantity']) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No data available to display.</p>
    <?php endif; ?>

</div>
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

<?php $mysqli->close(); ?>