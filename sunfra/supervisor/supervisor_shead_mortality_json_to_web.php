<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
 
$current_feature = "Shead Supervisor"; // Set per page
 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
$username  = $_SESSION['username'] ?? '';
$client_id = $_SESSION['client_id'] ?? 0;
 
if (empty($username) || !$client_id) {
    header("Location: ../login/login.php");
    exit;
}
 
$users_url = "https://sunfra.com/farm/sunfra/login/farm_users_list.php";
$users_response = @file_get_contents($users_url);
 
if ($users_response === false) {
    error_log("Admin API failure: $users_url");
    header("Location: https://sunfra.com/farm/sunfra/index.php");
    exit;
}
 
$users = json_decode($users_response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Admin JSON parse error: " . json_last_error_msg());
    header("Location: https://sunfra.com/farm/sunfra/index.php");
    exit;
}
 
$is_admin = false;
if (is_array($users)) {
    foreach ($users as $u) {
        if ($u['username'] === $username &&
            intval($u['client_id']) === intval($client_id) &&
            $u['status'] === 'admin'
        ) {
            $is_admin = true;
            break;
        }
    }
}
 
if (!$is_admin) {
    $feature_url = "https://sunfra.com/farm/sunfra/configuration/config_supervisor_json.php?client_id=" . urlencode($client_id);
    $feature_response = @file_get_contents($feature_url);
 
    if ($feature_response === false) {
        error_log("Feature API failure: $feature_url");
        header("Location: https://sunfra.com/farm/sunfra/index.php");
        exit;
    }
 
    $features = json_decode($feature_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Feature JSON parse error: " . json_last_error_msg());
        header("Location: https://sunfra.com/farm/sunfra/index.php");
        exit;
    }
 
    $has_feature = false;
    if (is_array($features)) {
        foreach ($features as $f) {
            if ($f['username'] === $username &&
                intval($f['client_id']) === intval($client_id) &&
                $f['feature'] === $current_feature
            ) {
                $has_feature = true;
                break;
            }
        }
    }
 
    if (!$has_feature) {
        header("Location: https://sunfra.com/farm/sunfra/index.php");
        exit;
    }
}
$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=$client_id";
$shead_response = file_get_contents($shead_url);

if ($shead_response === false) {
    echo json_encode(["error" => "Unable to fetch shead data"]);
    exit;
}

$shead_data = json_decode($shead_response, true);
if (!is_array($shead_data)) {
    echo json_encode(["error" => "Invalid JSON received"]);
    exit;
}

$shead_list = [];

foreach ($shead_data as $item) {
    if (isset($item['shead_name'])) {
        $shead_list[] = str_replace(' ', '_', $item['shead_name']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Shead Mortality</title>
  <script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
	*{
	  box-sizing: border-box;
	}
    body {
	  margin: 0;
	  padding: 0 10px;
	  font-family: 'Segoe UI', sans-serif;
	  background: linear-gradient(to right, #ADD8E6, #ADD8E6);
	  color: #333;
	}
	header {
	  background: linear-gradient(135deg, #4F46E5, #6D28D9);
	  color: white;
	  padding: 30px;
	  text-align: center;
	  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
	}

	header h1 {
	  margin: 0;
	  font-size: 32px;
	  font-weight: 600;
	  letter-spacing: 0.5px;
	}

	.container {
	  max-width: 1200px;
	  margin: 30px auto;
	  padding: 0 20px;
	}

	.filter-section {
	  display: flex;
	  justify-content: flex-end;
	  margin-bottom: 30px;
	  padding-right: 10px;
	}

	.date-filter-right {
	  display: flex;
	  align-items: center;
	  gap: 10px;
	  flex-wrap: wrap;
	}

	.date-filter-right label {
	  font-weight: 600;
	  font-size: 16px;
	  color: #4B5563;
	}

	.styled-date {
	  padding: 10px 14px;
	  border: 1px solid #cbd5e1;
	  border-radius: 10px;
	  font-size: 16px;
	  background-color: #fff;
	  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
	  transition: border-color 0.3s ease;
	}

	.styled-date:focus {
	  border-color: #4F46E5;
	  outline: none;
	  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
	}

	.card-grid {
	  display: grid;
	  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
	  gap: 25px;
	}

	.card {
	  background: linear-gradient(135deg, #ffffff, #f3f4f6);
	  border-radius: 16px;
	  padding: 24px;
	  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
	  position: relative;
	  transition: transform 0.25s ease;
	}

	.card:hover {
	  transform: translateY(-6px);
	}

	.card h3 {
	  color: #4F46E5;
	  font-size: 20px;
	  margin-bottom: 12px;
	}

	.card p {
	  margin: 6px 0;
	  color: #374151;
	  font-size: 15px;
	}

	.edit-btn {
	  position: absolute;
	  top: 20px;
	  right: 20px;
	  background-color: #10B981;
	  color: #fff;
	  padding: 6px 12px;
	  font-size: 13px;
	  border: none;
	  border-radius: 6px;
	  cursor: pointer;
	  transition: background 0.3s ease;
	}

	.edit-btn:hover {
	  background-color: #059669;
	}

	.no-data {
	  text-align: center;
	  margin-top: 40px;
	  font-size: 18px;
	  color: #888;
	}

	.add-button {
	  position: fixed;
	  bottom: 30px;
	  right: 30px;
	  background: linear-gradient(135deg, #4F46E5, #7C3AED);
	  color: white;
	  border: none;
	  padding: 16px 28px;
	  border-radius: 50px;
	  font-size: 16px;
	  font-weight: 600;
	  cursor: pointer;
	  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
	  transition: all 0.3s ease;
	  z-index: 1000;
	}

	.add-button:hover {
	  background: linear-gradient(135deg, #4338CA, #6D28D9);
	  transform: scale(1.05);
	}

	.modal {
	  display: none;
	  position: fixed;
	  z-index: 999;
	  left: 0;
	  top: 0;
	  width: 100%;
	  height: 100%;
	  backdrop-filter: blur(6px);
	  background-color: rgba(0, 0, 0, 0.4);
	}

	.modal-content {
	  background: rgba(255, 255, 255, 0.95);
	  margin: 5% auto;
	  padding: 30px;
	  border-radius: 16px;
	  max-width: 420px;
	  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
	}

	.modal-content h2 {
	  margin-top: 0;
	  margin-bottom: 20px;
	  color: #4F46E5;
	  font-size: 22px;
	}
	.modal-content select,
	.modal-content input {
	  width: 100%;
	  padding: 14px;
	  margin: 10px 0;
	  border: 1px solid #ddd;
	  border-radius: 10px;
	  font-size: 15px;
	  background: #f9fafb;
	  transition: border 0.2s;
	}
	.modal-content select:focus,
	.modal-content input:focus {
	  border-color: #7C3AED;
	  outline: none;
	}
	.modal-content select:focus {
	  border-color: #7C3AED;
	  outline: none;
	}
	.modal-content input:focus {
	  border-color: #7C3AED;
	  outline: none;
	}
	.modal-content button {
	  width: 100%;
	  padding: 14px;
	  background: linear-gradient(to right, #4F46E5, #6D28D9);
	  color: white;
	  border: none;
	  border-radius: 12px;
	  font-size: 16px;
	  font-weight: 600;
	  cursor: pointer;
	  transition: background 0.3s ease;
	}
	.modal-content button:hover {
	  background: linear-gradient(to right, #4338CA, #5B21B6);
	}

	.close {
	  float: right;
	  font-size: 24px;
	  cursor: pointer;
	  color: #999;
	}

	.close:hover {
	  color: black;
	}

	@media (max-width: 600px) {
	  .filter-section {
		flex-direction: column;
		align-items: flex-start;
	  }

	  .filter-section input {
		width: 100%;
	  }

	  .add-button {
		right: 20px;
		bottom: 20px;
		padding: 12px 24px;
		font-size: 15px;
	  }
	}.graph-section {
	  margin-top: 50px;
	  background: #fff;
	  padding: 20px;
	  border-radius: 16px;
	  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
	}

	.graph-controls {
	  display: flex;
	  justify-content: center;
	  flex-wrap: wrap;
	  gap: 10px;
	  margin-bottom: 20px;
	}

	.graph-controls button {
	  padding: 10px 16px;
	  background: linear-gradient(to right, #4F46E5, #7C3AED);
	  color: white;
	  border: none;
	  border-radius: 8px;
	  font-size: 14px;
	  cursor: pointer;
	  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
	  transition: background 0.3s ease;
	}

	.graph-controls button:hover {
	  background: linear-gradient(to right, #4338CA, #6D28D9);
	}

	.chart-wrapper {
	  max-width: 500px;
	  margin: 0 auto;
	}

	@media (max-width: 600px) {
	  #mortalityChart {
		width: 100% !important;
		height: auto !important;
	  }
	}.back-button {
	  display: inline-block;
	  margin-bottom: 1rem;
	  background-color: #3498db;
	  color: white;
	  padding: 10px 16px;
	  border-radius: 6px;
	  text-decoration: none;
	  font-weight: 600;
	  transition: background-color 0.3s ease;
	}

	.back-button:hover {
	  background-color: #217dbb;
	} .sidebar {
			position: fixed;
			top: 0;
			left: 0;
			width: 70px;
			height: 100vh;
			background-color: #016795;
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			padding-top: 10px;
			overflow-y: auto;
			transition: width 0.3s ease;
			z-index: 1000;
			box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
		  }
		  .sidebar.expanded {
			width: 250px;
		  }
		  .sidebar a {
			color: white;
			text-decoration: none;
			width: 100%;
			padding: 14px 20px;
			display: flex;
			align-items: center;
			font-size: 15px;
			transition: background-color 0.2s ease-in-out;
			white-space: nowrap;
		  }
		  .sidebar a:hover {
			background-color: #0194c7;
		  }
		  .sidebar i {
			font-size: 16px;
			min-width: 30px;
			text-align: center;
		  }
		  .label {
			margin-left: 10px;
			white-space: nowrap;
			display: none;
		  }
		  .sidebar.expanded .label {
			display: inline;
		  }
		  .toggle-btn {
			width: 100%;
			cursor: pointer;
			padding: 10px 20px;
			background: none;
			border: none;
			color: white;
			font-size: 18px;
			text-align: left;
			outline: none;
			user-select: none;
			display: flex;
			align-items: center;
		  }
		  .toggle-btn i {
			margin-right: 10px;
		  }
		  .attendance-submenu {
			display: none;
			flex-direction: column;
			background: #1e293b;
			width: 100%;
			padding-left: 40px;
			transition: all 0.3s ease;
		  }
		  .attendance-submenu button {
			background: none;
			border: none;
			color: white;
			text-align: left;
			padding: 10px 20px;
			font-size: 14px;
			cursor: pointer;
			transition: background-color 0.2s ease;
		  }
		  .attendance-submenu button:hover {
			background-color: #2563EB;
		  }.main-content {
			  margin-left: 250px;
			  transition: margin-left 0.3s;
			}

			.main-content.collapsed {
			  margin-left: 50px;
			}.content {
			  margin-left: 70px;
			  transition: margin-left 0.3s ease;
			}

			.sidebar.expanded ~ .content {
			  margin-left: 250px;
			}.content.expanded {
			  margin-left: 250px;
			}
  </style>
</head>
<body>
<div>
<div class="sidebar" id="sidebar">
  <button class="toggle-btn" id="sidebarToggleBtn" aria-label="Toggle menu" title="Toggle menu">
    <i class="fas fa-bars"></i>
    <span class="label">Menu</span>
  </button>
  <a href="https://sunfra.com/farm/sunfra/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/sunfra/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/sunfra/sensor/iot_web_page.php"><i class="fas fa-microchip"></i><span class="label">IOT</span></a>
  <a href="https://sunfra.com/farm/sunfra/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/sunfra/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-user-check"></i>
      <span class="label">Attendance <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="attendanceSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/attendance/labour_master_json_to_web.php'">👷‍♂️ Labour Details</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/attendance/labour_attendance_json_to_web.php'">📅 Labour Attendance</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/attendance/assigned_master_json_to_web.php'">📝 Assigned Master</button>
    </div>
  </div>
	<div class="attendance-dropdown">
	  <a onclick="toggleShed()" class="attendance-toggle">
		<i class="fas fa-users-cog"></i>
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-industry"></i>
      <span class="label">Feed Plant Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="feedPlantSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feed_formula/feed_formula_json_to_web.php'">⚙️ Feed Formula</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/feed_raw_material_json_to_web.php'">📥 Feed Raw Material</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/feed_to_shead_json_to_web.php'">🚚 Feed Material To Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/water_medicine_json_to_web.php'">🧪 Water Medicine</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-warehouse"></i>
      <span class="label">Egg Godown <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="eggGodownSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_godown_stock_json_to_web.php'">🥚 Egg Production From Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_godown_sale_json_to_web.php'">💰 Eggs for Sale</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_weight_json_to_web.php'">⚖️ Egg Weight</button>
    	<button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_damaged_json_to_web.php'">⚖️ Weekly Egg Damages</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-chart-line"></i>
      <span class="label">Profit & Loss <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="profitLossSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/material_cost_json_to_web.php'">Feed Material Price</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/egg_cutting_json_to_web.php'">Egg Price Per Piece</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/profit_loss_json_to_web.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/sunfra/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <!--<a href="https://sunfra.com/farm/sunfra/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>-->
  <a href="https://sunfra.com/farm/sunfra/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
  <header>
    <h1>Shead Mortality</h1>
  </header>

  <div class="container">
   <div class="filter-section">
	  <div class="date-filter-right">
		<label for="dateFilter">📅 Select Date:</label>
		<input type="date" id="dateFilter" class="styled-date" />
	  </div>
	</div>
   </div>

    <div class="card-grid" id="cardContainer"></div>
    <div class="no-data" id="noData" style="display: none;">No records found for selected date.</div>
  </div>

  <button class="add-button" id="addNewBtn">+ Add New</button>

  <div class="modal" id="addModal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
      <h2>Add New Record</h2>
      <form id="addForm">
	  <select id="sheadNo" name="sheadNo" required>
			<option value="">Select Shead</option>
			<?php
			foreach ($shead_list as $shead) {
				echo '<option value="' . htmlspecialchars($shead) . '">' . htmlspecialchars($shead) . '</option>';
			}
			?>
		</select>

	  <input type="number" id="noOfBirds" placeholder="No. of Mortality" required />
	  <button type="submit">Submit</button>
	</form>
    </div>
  </div>

  <div class="graph-section">
	  <div class="graph-controls">
		<button onclick="updateGraph('today')">Today</button>
		<button onclick="updateGraph('yesterday')">Yesterday</button>
		<button onclick="updateGraph('weekly')">Weekly</button>
		<button onclick="updateGraph('monthly')">Monthly</button>
	  </div>
	  <div class="chart-wrapper">
		<canvas id="mortalityChart"></canvas>
	  </div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

  <script>
  const clientId = "<?php echo $client_id; ?>"; 

	const apiURL = `https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_mortality_json.php?client_id=${clientId}`;
  const cardContainer = document.getElementById("cardContainer");
  const dateInput = document.getElementById("dateFilter");
  const noDataDiv = document.getElementById("noData");
  const addModal = document.getElementById("addModal");
  const addForm = document.getElementById("addForm");

  document.getElementById("addNewBtn").addEventListener("click", () => {
    addModal.style.display = "block";
    addForm.reset();
    delete addForm.dataset.editId;
    addForm.querySelector("button").textContent = "Submit";
  });

  window.onclick = function (event) {
    if (event.target === addModal) {
      addModal.style.display = "none";
    }
  };

  async function fetchData() {
    const response = await fetch(apiURL);
    const data = await response.json();
    return data[clientId] || [];
  }

  function createCard(item) {
    return `
      <div class="card">
        <button class="edit-btn" onclick='openEditForm(${JSON.stringify(item)})'>Edit</button>
        <h3>${item.sheadNo}</h3>
        <p><strong>Birds:</strong> ${item.noOfBirds}</p>
        <p><strong>Date:</strong> ${item.date}</p>
        <p><strong>Time:</strong> ${new Date(item.timestamp).toLocaleTimeString()}</p>
      </div>
    `;
  }

  function openEditForm(item) {
    addModal.style.display = "block";
    document.getElementById("sheadNo").value = item.sheadNo;
    document.getElementById("noOfBirds").value = item.noOfBirds;
    addForm.dataset.editId = item.id;
    addForm.querySelector("button").textContent = "Update";
  }

  async function displayCards(dateFilter = null) {
    const data = await fetchData();
    cardContainer.innerHTML = "";
    noDataDiv.style.display = "none";

    const filtered = dateFilter ? data.filter(item => item.date === dateFilter) : data;
    const topTen = filtered.slice(0, 10);

    if (topTen.length === 0) {
      noDataDiv.style.display = "block";
      return;
    }

    topTen.forEach(item => {
      cardContainer.innerHTML += createCard(item);
    });
  }

  dateInput.addEventListener("change", () => {
    const selectedDate = dateInput.value;
    displayCards(selectedDate);
  });

  addForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const payload = {
      id: addForm.dataset.editId || "",
      sheadNo: document.getElementById("sheadNo").value,
      noOfBirds: document.getElementById("noOfBirds").value,
      client_id: clientId 
    };

    const response = await fetch("https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_mortality_save.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    if (response.ok) {
      alert(payload.id ? "Record updated!" : "New record added!");
      addModal.style.display = "none";
      addForm.reset();
      delete addForm.dataset.editId;
      displayCards(dateInput.value);
    } else {
      alert("Error saving record.");
    }
  });

  let chart;

  function parseDate(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
  }

  async function updateGraph(range) {
    const rawData = await fetchData();
    const now = new Date();
    let filteredData = [];

    if (range === 'today') {
      const todayStr = now.toISOString().split('T')[0];
      filteredData = rawData.filter(d => d.date === todayStr);
    } else if (range === 'yesterday') {
      const yest = new Date(now);
      yest.setDate(now.getDate() - 1);
      const yDate = yest.toISOString().split('T')[0];
      filteredData = rawData.filter(d => d.date === yDate);
    } else if (range === 'weekly') {
      const start = new Date(now);
      start.setDate(now.getDate() - 6);
      filteredData = rawData.filter(d => {
        const dDate = parseDate(d.date);
        return dDate >= start && dDate <= now;
      });
    } else if (range === 'monthly') {
      const start = new Date(now);
      start.setDate(now.getDate() - 29);
      filteredData = rawData.filter(d => {
        const dDate = parseDate(d.date);
        return dDate >= start && dDate <= now;
      });
    }

    const sheadCounts = {};
    filteredData.forEach(d => {
      sheadCounts[d.sheadNo] = (sheadCounts[d.sheadNo] || 0) + parseInt(d.noOfBirds);
    });

    const labels = Object.keys(sheadCounts).sort();
    const values = labels.map(label => sheadCounts[label]);

    if (chart) chart.destroy();

    const ctx = document.getElementById('mortalityChart').getContext('2d');
    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Mortality',
          data: values,
          backgroundColor: '#7C3AED',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#4F46E5',
            titleColor: '#fff',
            bodyColor: '#fff'
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'Shead No',
              color: '#374151'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Birds',
              color: '#374151'
            },
            beginAtZero: true
          }
        }
      }
    });
  }

  window.addEventListener("DOMContentLoaded", () => {
    const today = new Date().toISOString().split("T")[0];
    dateInput.value = today;
    displayCards(today);
    updateGraph('today');
  });
  const sidebar = document.getElementById('sidebar');
const mainContent = document.querySelector('.content'); // or '.main-content'
const toggleBtn = document.getElementById('sidebarToggleBtn');

toggleBtn.addEventListener('click', () => {
  sidebar.classList.toggle('expanded');
  mainContent.classList.toggle('expanded');  // toggle expanded class for margin shift

  const icon = toggleBtn.querySelector('i');
  if (sidebar.classList.contains('expanded')) {
    icon.classList.remove('fa-bars');
    icon.classList.add('fa-times');
  } else {
    icon.classList.add('fa-bars');
    icon.classList.remove('fa-times');
  }
});

  function toggleAttendance() {
    toggleSubmenu('attendanceSubmenu');
  }
  function toggleFeedPlant() {
    toggleSubmenu('feedPlantSubmenu');
  }
  function toggleEggGodown() {
    toggleSubmenu('eggGodownSubmenu');
  }
  function toggleProfitLoss() {
    toggleSubmenu('profitLossSubmenu');
  }
  function toggleShed() {
    toggleSubmenu('shedSubmenu');
  }
  function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    if (!submenu) return;
    if (submenu.style.display === 'flex') {
      submenu.style.display = 'none';
    } else {
      submenu.style.display = 'flex';
    }
  }
</script>
  	</main>
</div>
</body>
</html>
