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
  <meta charset="UTF-8">
  <title>Supervisor Shead Production</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #ADD8E6;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 16px;
    }
    h2 {
      font-size: 1.4rem;
    }
    .table th {
      background-color: #4CAF50;
      color: white;
      font-size: 0.8rem;
    }
    .table td {
      font-size: 0.8rem;
    }
    .edit-btn {
      color: #007bff;
      text-decoration: none;
    }
    .edit-btn:hover {
      text-decoration: underline;
    }
    .add-btn {
      width: 100%;
      margin-top: 10px;
    }
    @media (min-width: 576px) {
      .add-btn {
        float: right;
        width: auto;
        margin-top: 0;
      }
    }
    .card {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      padding: 15px;
      margin-bottom: 20px;
    }
	@media (min-width: 768px) {
	  #productionChart {
		max-height: 300px;
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
	}.sidebar {
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
<div class="container mt-3">
  <h2 class="text-center mb-4">Supervisor Shead Production</h2>

  <div class="row mb-3">
    <div class="col-12 col-md-2 mb-2 mb-md-0 d-flex align-items-center">
      <label for="dateFilter" class="me-2 fw-semibold">📅 Date:</label>
      <input type="date" id="dateFilter" class="form-control" value="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="col-12 col-md-10 text-md-end">
      <a href="#" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#entryModal">+ Add New Entry</a>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-bordered table-striped text-center align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Shead No</th>
            <th>Trays</th>
            <th>Loose</th>
            <th>Production</th>
            <th>Damaged</th>
            <th>Time</th>
            <th>Edit</th>
          </tr>
        </thead>
        <tbody id="dataBody">
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mt-3">
    <label for="graphFilter" class="fw-semibold mb-2 px-3 pt-3">📊 View Production Summary:</label>
    <div class="px-3">
      <select class="form-select mb-3" id="graphFilter">
        <option value="today">Today's Production</option>
        <option value="yesterday">Yesterday's Production</option>
        <option value="weekly">Weekly Production</option>
        <option value="monthly">Monthly Production</option>
        <option value="yearly">Yearly Production</option>
      </select>
    </div>
    <div class="d-flex justify-content-center pb-3 px-2">
      <div style="width: 100%; max-width: 100%; overflow-x: auto;">
        <canvas id="productionChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
	const clientId = "<?php echo $_SESSION['client_id']; ?>";
	const sheadNameMap = <?php echo json_encode($shead_list); ?>;
	const validSheads = sheadNameMap;

	document.addEventListener("DOMContentLoaded", function () {
	  const apiUrl = `https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_production_json.php?client_id=${clientId}`;
	  const dataBody = document.getElementById("dataBody");
	  const dateInput = document.getElementById("dateFilter");
	  const graphFilter = document.getElementById("graphFilter");
	  const chartCanvas = document.getElementById("productionChart");
	  let chart;

	  function getFormattedDate(offsetDays = 0) {
		const d = new Date();
		d.setDate(d.getDate() + offsetDays);
		return d.toISOString().split("T")[0];
	  }

	  function isWithinRange(dateStr, type) {
		  const today = new Date();
		  const target = new Date(dateStr);
		  switch (type) {
			case "today":
			  return target.toDateString() === today.toDateString();
			case "yesterday":
			  const y = new Date();
			  y.setDate(today.getDate() - 1);
			  return target.toDateString() === y.toDateString();
			case "weekly":
			  const w = new Date();
			  w.setDate(today.getDate() - 6);
			  return target >= w && target <= today;
			case "monthly":
			  const past30 = new Date();
			  past30.setDate(today.getDate() - 30);
			  return target >= past30 && target <= today;
			case "yearly":
			  return target.getFullYear() === today.getFullYear();
		  }
		  return false;
		}

	  function renderChart(data, type) {
		  const sheadData = {};
			validSheads.forEach(shead => {
			  sheadData[shead] = 0; 
			});
		  data.forEach(row => {
			if (isWithinRange(row.timestamp, type)) {
			  const shead = row.sheadNo?.trim();

			  if (shead && validSheads.includes(shead)) {
				const trays = parseInt(row.no_of_trays) || 0;
				sheadData[shead] = (sheadData[shead] || 0) + trays;
			  }
			}
		  });

		  const labels = Object.keys(sheadData).map(key => sheadNameMap[key] || key);
		  const values = Object.values(sheadData);

		  if (chart) chart.destroy();

		  chart = new Chart(chartCanvas, {
			type: 'bar',
			data: {
			  labels,
			  datasets: [{
				label: "Trays Count (Shead-wise)",
				data: values,
				backgroundColor: 'rgba(13, 110, 253, 0.7)',
				borderColor: 'rgba(13, 110, 253, 1)',
				borderWidth: 1
			  }]
			},
			options: {
			  responsive: true,
			  scales: {
				y: { beginAtZero: true },
				x: {
				  ticks: {
					autoSkip: false,
					maxRotation: 60,
					minRotation: 30
				  }
				}
			  },
			  plugins: {
				legend: { display: false }
			  }
			}
		  });
		}

	  function populateTable(data, date) {
		const filtered = data.filter(row => row.timestamp.startsWith(date));
		dataBody.innerHTML = filtered.length === 0
		  ? `<tr><td colspan="8" class="text-danger">No data found for selected date.</td></tr>`
		  : filtered.map(row => `
			<tr>
			  <td>${row.id}</td>
			  <td>${row.sheadNo}</td>
			  <td>${row.no_of_trays}</td>
			  <td>${row.no_of_loose_eggs}</td>
			  <td>${row.production}</td>
			  <td>${row.no_of_damaged_eggs}</td>
			  <td>${row.timestamp}</td>
			  <td><a class="edit-btn" href="#" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#entryModal">Edit</a></td>
			</tr>`).join("");

		setupEditButtons();
	  }

		function fetchData(selectedDate = getFormattedDate(), rangeType = "today") {
		  fetch(apiUrl)
			.then(response => response.json())
			.then(data => {
				console.log("Fetched data:", data);

				if (Array.isArray(data)) {
					rows = data;
				} else if (data && typeof data === "object") {
					const firstKey = Object.keys(data)[0];
					const maybeArray = data[firstKey];

					if (Array.isArray(maybeArray)) {
						rows = maybeArray;
					} else {
						console.error("Expected array at first key but got:", firstKey);
						dataBody.innerHTML = `<tr><td colspan="8" class="text-danger">${data.message || "No data available."}</td></tr>`;
						return;
					}
				} else {
					console.error("Unexpected API data format:", data);
					dataBody.innerHTML = `<tr><td colspan="8" class="text-danger">Invalid response format.</td></tr>`;
					return;
				}

				populateTable(rows, selectedDate);
				renderChart(rows, rangeType);
			})
			.catch(err => {
			  console.error(err);
			  dataBody.innerHTML = `<tr><td colspan="8" class="text-danger">Error loading data.</td></tr>`;
			});
		}

	  const today = getFormattedDate();
	  fetchData(today, "today");

	  dateInput.addEventListener("change", () => {
		fetchData(dateInput.value, graphFilter.value);
	  });

	  graphFilter.addEventListener("change", () => {
		fetchData(dateInput.value, graphFilter.value);
	  });

	  document.getElementById("entryForm").addEventListener("submit", function (e) {
		e.preventDefault();

		const form = e.target;
		const formData = new FormData(form);

		fetch("https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_production_save.php", {
		  method: "POST",
		  body: formData,
		})
		  .then(response => response.json())
		  .then(result => {
			if (result.status === "success") {
			  const modal = bootstrap.Modal.getInstance(document.getElementById("entryModal"));
			  modal.hide();

			  form.reset();
			  document.getElementById("entryModalLabel").innerText = "➕ Add New Entry";
			  document.getElementById("formId").value = "";

			  fetchData(document.getElementById("dateFilter").value, document.getElementById("graphFilter").value);
			} else {
			  alert("❌ Error: " + result.message);
			}
		  })
		  .catch(error => {
			console.error("Save error:", error);
			alert("❌ Network error. Please try again.");
		  });
	  });

	});

	function setupEditButtons() {
	  document.querySelectorAll(".edit-btn").forEach(btn => {
		btn.addEventListener("click", function (e) {
		  e.preventDefault();

		  const id = this.dataset.id;
		  const row = this.closest("tr");
		  const cells = row.querySelectorAll("td");

		  document.getElementById("entryModalLabel").innerText = "✏️ Edit Entry";

		  document.getElementById("formId").value = id;
		  document.getElementById("sheadNo").value = cells[1].innerText.trim();
		  document.getElementById("no_of_trays").value = cells[2].innerText.trim();
		  document.getElementById("no_of_loose_eggs").value = cells[3].innerText.trim();
		  document.getElementById("no_of_damaged_eggs").value = cells[5].innerText.trim();
		});
	  });
	}
	document.addEventListener("DOMContentLoaded", function () {
	  const sheadSelect = document.getElementById("sheadNo");
	  const sheadSet = new Set();

	  const sheadUrl = `https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=${clientId}`;

	  fetch(sheadUrl)
		.then(res => res.json())
		.then(data => {
		  if (!Array.isArray(data)) {
			console.error("Unexpected data structure:", data);
			return;
		  }

		  data.forEach(item => {
			if (item.shead_name) {
			  const sheadName = item.shead_name.replace(/\s+/g, "_");
			  if (!sheadSet.has(sheadName)) {
				sheadSet.add(sheadName);
				const option = document.createElement("option");
				option.value = sheadName;
				option.textContent = sheadName;
				sheadSelect.appendChild(option);
			  }
			}
		  });
		})
		.catch(error => {
		  console.error("Error loading shead data:", error);
		});
	});
	const sidebar = document.getElementById('sidebar');
	const mainContent = document.querySelector('.content'); // or '.main-content'
	const toggleBtn = document.getElementById('sidebarToggleBtn');

	toggleBtn.addEventListener('click', () => {
	  sidebar.classList.toggle('expanded');
	  mainContent.classList.toggle('expanded');  

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

<div class="modal fade" id="entryModal" tabindex="-1" aria-labelledby="entryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="entryModalLabel">➕ Add New Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="entryForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="formId">
			<input type="hidden" name="client_id" id="clientId" value="<?php echo $client_id; ?>">
		<div class="mb-2">
		  <label for="sheadNo" class="form-label">Shead No</label>
		  <select id="sheadNo" name="sheadNo" class="form-select" required>
			<option value="">Select option</option>
		  </select>
		</div>

          <div class="mb-2">
            <label for="no_of_trays" class="form-label">No of Trays</label>
            <input type="number" name="no_of_trays" id="no_of_trays" class="form-control" required>
          </div>

          <div class="mb-2">
            <label for="no_of_loose_eggs" class="form-label">Loose Eggs</label>
            <input type="number" name="no_of_loose_eggs" id="no_of_loose_eggs" class="form-control" required>
          </div>

          <div class="mb-2">
            <label for="no_of_damaged_eggs" class="form-label">Damaged Eggs</label>
            <input type="number" name="no_of_damaged_eggs" id="no_of_damaged_eggs" class="form-control" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">✅ Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
</main>
</div>
</body>
</html>
