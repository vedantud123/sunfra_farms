<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$current_feature = "Egg Godown"; // Set per page

// 1. Login check
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

// 2. Admin check
$users_url = "https://sunfra.com/farm/sunfra_clients/login/farm_users_list.php";
$users_response = @file_get_contents($users_url);

if ($users_response === false) {
    error_log("Admin API failure: $users_url");
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
    exit;
}

$users = json_decode($users_response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Admin JSON parse error: " . json_last_error_msg());
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
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

// 3. Feature check (only for non-admins)
if (!$is_admin) {
    $feature_url = "https://sunfra.com/farm/sunfra_clients/configuration/config_supervisor_json.php?client_id=" . urlencode($client_id);
    $feature_response = @file_get_contents($feature_url);

    if ($feature_response === false) {
        error_log("Feature API failure: $feature_url");
        header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
        exit;
    }

    $features = json_decode($feature_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Feature JSON parse error: " . json_last_error_msg());
        header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
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
        header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
        exit;
    }
}

// ✅ Passed all checks → load UI data
$shead_url = "https://sunfra.com/farm/sunfra_clients/configuration/shead_number_json.php?client_id=$client_id";
$shead_response = @file_get_contents($shead_url);
$shead_list = [];

if ($shead_response !== false) {
    $data = json_decode($shead_response, true);
    if (is_array($data)) {
        foreach ($data as $s) {
            if (isset($s['shead_name'])) {
                $shead_list[] = $s['shead_name'];
            }
        }
    } else {
        error_log("Shead JSON parse failure for client_id $client_id");
    }
} else {
    error_log("Shead API failure: $shead_url");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Weekly Egg Damage Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
		  .dropdown-menu {
		  max-height: 300px;
		  overflow-y: auto;
		  z-index: 1050;
		  width: 100% !important;
		  left: 0 !important;
		  right: auto !important;
		  transform: translate3d(0px, 38px, 0px) !important;
		  padding: 0.5rem;
		  box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
		}

		.dropdown-menu li {
		  list-style-type: none;
		  padding: 0;
		  margin: 0;
		}

		.dropdown-menu .form-check {
		  display: flex;
		  align-items: center;
		  padding: 6px 10px;
		  white-space: normal;
		  margin: 0;
		}

		.form-check-input {
		  margin-right: 8px;
		}

		.dropdown-toggle::after {
		  float: right;
		  margin-top: 8px;
		}

		@media (max-width: 768px) {
		  .dropdown-menu {
			max-height: 220px;
			font-size: 14px;
		  }

		  .form-check-label {
			font-size: 14px;
		  }
		}.dropdown-menu .form-check-label {
		  width: 100%;
		  padding-left: 6px;
		  font-size: 15px;
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
<body style="background-color: #ADD8E6;">
<div>
<div class="sidebar" id="sidebar">
  <button class="toggle-btn" id="sidebarToggleBtn" aria-label="Toggle menu" title="Toggle menu">
    <i class="fas fa-bars"></i>
    <span class="label">Menu</span>
  </button>
  <a href="https://sunfra.com/farm/sunfra_clients/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-user-check"></i>
      <span class="label">Attendance <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="attendanceSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/attendance/labour_master_json_to_web.php'">👷‍♂️ Labour Details</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/attendance/labour_attendance_json_to_web.php'">📅 Labour Attendance</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/attendance/assigned_master_json_to_web.php'">📝 Assigned Master</button>
    </div>
  </div>
	<div class="attendance-dropdown">
	  <a onclick="toggleShed()" class="attendance-toggle">
		<i class="fas fa-users-cog"></i>
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-industry"></i>
      <span class="label">Feed Plant Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="feedPlantSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feed_formula/feed_formula_json_to_web.php'">⚙️ Feed Formula</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feedrawmaterial/feed_raw_material_json_to_web.php'">📥 Feed Raw Material</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feedrawmaterial/feed_to_shead_json_to_web.php'">🚚 Feed Material To Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/feedrawmaterial/water_medicine_json_to_web.php'">🧪 Water Medicine</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-warehouse"></i>
      <span class="label">Egg Godown <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="eggGodownSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_godown_stock_json_to_web.php'">🥚 Egg Production From Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_godown_sale_json_to_web.php'">💰 Eggs for Sale</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_weight_json_to_web.php'">⚖️ Egg Weight</button>
    	<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/egg_godown/egg_damaged_json_to_web.php'">⚖️ Weekly Egg Damages</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-chart-line"></i>
      <span class="label">Profit & Loss <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="profitLossSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/material_cost_json_to_web.php'">Feed Material Price</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/egg_cutting_json_to_web.php'">Egg Price Per Piece</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/profit_loss_json_to_web.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/sunfra_clients/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
<div class="container mt-4">
  <h2 class="mb-4 text-center">Weekly Egg Damage Report</h2>

  <div class="row g-3 mb-4">
    <!-- Month multi-select -->
    <div class="col-md-6 col-sm-12">
      <label class="form-label">Select Months</label>
      <div class="dropdown w-100">
       <button id="monthDropdownBtn" class="btn dropdown-toggle w-100" style="background-color: #1FACCC; color: black;" type="button" data-bs-toggle="dropdown">
		  Select Months
		</button>
        <ul class="dropdown-menu w-100 start-0" id="monthDropdownMenu"></ul>
      </div>
    </div>

    <!-- Shead dropdown -->
    <div class="col-md-6 col-sm-12">
      <label class="form-label">Select Sheads</label>
      <div class="dropdown w-100">
        <button id="sheadDropdownBtn" class="btn dropdown-toggle w-100" style="background-color: #1FACCC; color: black;" type="button" data-bs-toggle="dropdown">
		  Select Sheads
		</button>
        <ul class="dropdown-menu w-100 start-0" id="sheadDropdownMenu"></ul>
      </div>
    </div>
  </div>

  <div id="resultArea"></div>
</div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
	const client_id = <?php echo json_encode($_SESSION['client_id'] ?? 0); ?>;
	const sheadList = <?= json_encode($shead_list) ?>;

	function getTrayCount(eggs) {
	  const wholeTrays = Math.floor(eggs / 30);
	  const remainder = eggs % 30;
	  return `${wholeTrays}.${remainder.toString().padStart(2, '0')}`;
	}

	function renderSheadCheckboxes() {
	  const menu = document.getElementById("sheadDropdownMenu");
	  menu.classList.add("dropdown-menu-checkboxes");

	  menu.innerHTML = `
		  <div class="px-3 py-2">
			<div class="form-check mb-2">
			  <input class="form-check-input" type="checkbox" id="selectAllSheads">
			  <label class="form-check-label fw-bold" for="selectAllSheads">Select All</label>
			</div>
			<hr class="dropdown-divider">
			<div class="d-flex flex-column gap-1">
			  ${sheadList.map((shead, i) => `
				<div class="form-check">
				  <input class="form-check-input shead-checkbox" type="checkbox" id="shead${i}" value="${shead}">
				  <label class="form-check-label" for="shead${i}">${shead}</label>
				</div>
			  `).join('')}
			</div>
		  </div>
		`;

	  document.getElementById("selectAllSheads").addEventListener("change", function () {
		document.querySelectorAll(".shead-checkbox").forEach(cb => cb.checked = this.checked);
		updateSheadDropdownButton();
		fetchData();
	  });

	  menu.querySelectorAll(".shead-checkbox").forEach(cb => {
		cb.addEventListener("change", () => {
		  const all = document.querySelectorAll(".shead-checkbox");
		  const checked = document.querySelectorAll(".shead-checkbox:checked");
		  document.getElementById("selectAllSheads").checked = all.length === checked.length;
		  updateSheadDropdownButton();
		  fetchData();
		});
	  });

	  menu.addEventListener('click', e => e.stopPropagation());
	}

	function renderMonthCheckboxes() {
	  const monthMenu = document.getElementById("monthDropdownMenu");
	  monthMenu.classList.add("dropdown-menu-checkboxes");

	  const now = new Date();
	  const months = [];

	  for (let i = 0; i < 12; i++) {
		const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
		const value = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
		const label = date.toLocaleString('default', { month: 'long', year: 'numeric' });
		months.push({ value, label });
	  }

		 monthMenu.innerHTML = `
		  <div class="px-2">
			<div class="form-check mb-2">
			  <input class="form-check-input" type="checkbox" id="selectAllMonths">
			  <label class="form-check-label fw-bold" for="selectAllMonths">Select All</label>
			</div>
			<hr class="dropdown-divider">
			<div class="d-flex flex-column gap-1">
			  ${months.map((m, i) => `
				<div class="form-check">
				  <input class="form-check-input month-checkbox" type="checkbox" id="month${i}" value="${m.value}">
				  <label class="form-check-label" for="month${i}">${m.label}</label>
				</div>
			  `).join("")}
			</div>
		  </div>
		`;

	  document.getElementById("selectAllMonths").addEventListener("change", function () {
		const isChecked = this.checked;
		document.querySelectorAll(".month-checkbox").forEach(cb => cb.checked = isChecked);
		updateMonthDropdownButton();
		fetchData();
	  });

	  monthMenu.querySelectorAll(".month-checkbox").forEach(cb => {
		cb.addEventListener("change", () => {
		  const all = document.querySelectorAll(".month-checkbox");
		  const checked = document.querySelectorAll(".month-checkbox:checked");
		  document.getElementById("selectAllMonths").checked = all.length === checked.length;
		  updateMonthDropdownButton();
		  fetchData();
		});
	  });

	  monthMenu.addEventListener('click', e => e.stopPropagation());
	}

	function updateSheadDropdownButton() {
	  const selected = document.querySelectorAll(".shead-checkbox:checked");
	  const btn = document.getElementById("sheadDropdownBtn");
	  btn.textContent = selected.length > 0 ? `${selected.length} Shead(s) Selected` : "Select Sheads";
	}

	function updateMonthDropdownButton() {
	  const selected = document.querySelectorAll(".month-checkbox:checked");
	  const btn = document.getElementById("monthDropdownBtn");
	  btn.textContent = selected.length > 0 ? `${selected.length} Month(s) Selected` : "Select Months";
	}

	function fetchData() {
	  const selectedMonths = Array.from(document.querySelectorAll(".month-checkbox:checked")).map(cb => cb.value);
	  const selectedSheads = Array.from(document.querySelectorAll(".shead-checkbox:checked")).map(cb => cb.value);

	  const resultArea = document.getElementById("resultArea");
	  resultArea.innerHTML = "";

	  if (selectedMonths.length === 0 || selectedSheads.length === 0) {
		resultArea.innerHTML = `<div class="alert alert-info">Please select at least one month and one shead.</div>`;
		return;
	  }

	  const monthQuery = selectedMonths.map(encodeURIComponent).join(",");
	  const sheadQuery = selectedSheads.map(encodeURIComponent).join(",");

	  fetch(`get_weekly_damaged_eggs.php?months=${monthQuery}&sheads=${sheadQuery}&client_id=${client_id}`)
		.then(res => res.json())
		.then(data => {
		  if (!data || data.length === 0) {
			resultArea.innerHTML = `<div class="alert alert-warning">No records found.</div>`;
			return;
		  }

		  const grouped = {};
		  data.forEach(item => {
			const shead = item["Shead Name"];
			if (!grouped[shead]) grouped[shead] = [];
			grouped[shead].push(item);
		  });

		  let html = "";

		  Object.entries(grouped).forEach(([sheadName, items]) => {
			const rows = items.map(item => `
			  <tr>
				<td>${item["Month"]}</td>
				<td>${item["Week"]}</td>
				<td>${getTrayCount(item["Production Damaged"])}</td>
				<td>${getTrayCount(item["Sale Damaged"])}</td>
				<td><strong>${getTrayCount(item["Total Damaged"])}</strong></td>
				<td style="color: #d9534f; font-weight: bold;">${getTrayCount(item["100_Trays_Damage"])}</td>
			  </tr>
			`).join('');

			html += `
			  <div class="mb-5">
				<h5 class="text-primary mb-3">${sheadName}</h5>
				<div class="table-responsive">
				  <table class="table table-bordered table-striped">
					<thead class="table-dark">
					  <tr>
						<th>Month</th>
						<th>Week</th>
						<th>Production</th>
						<th>Sale</th>
						<th>Total</th>
						<th>Avg. Damage</th>
					  </tr>
					</thead>
					<tbody>${rows}</tbody>
				  </table>
				</div>
			  </div>
			`;
		  });

		  resultArea.innerHTML = html;
		})
		.catch(err => {
		  console.error("Error fetching data", err);
		  resultArea.innerHTML = `<div class="alert alert-danger">Failed to load data.</div>`;
		});
	}

	document.addEventListener("DOMContentLoaded", function () {
	  renderSheadCheckboxes();
	  renderMonthCheckboxes();

	  if (window.innerWidth <= 768) {
		document.body.style.zoom = "80%";
	  }
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
