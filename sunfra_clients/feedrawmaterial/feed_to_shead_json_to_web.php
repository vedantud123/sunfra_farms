<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
 
$current_feature = "Feed Plant Supervisor"; // Set per page
 
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

$shead_url = "https://sunfra.com/farm/sunfra_clients/configuration/shead_chick_grower_json.php?client_id=$client_id";
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
        $normalized = strtolower(str_replace(' ', '_', $item['shead_name']));
        $shead_list[] = $normalized;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Feed Shead Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background: #ADD8E6;
    }
    .table-container {
      margin: 30px auto;
      max-width: 900px;
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
	<div class="table-container">
		<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
		  <h2 class="mb-0">Feed Shead Records</h2>
		  
		  <div class="d-flex align-items-center gap-2">
			<input type="date" id="filter-date" class="form-control" style="max-width: 180px;" />

			<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDataModal">
			  Add New
			</button>
		  </div>
		</div>

	  <div class="table-responsive">
		<table class="table table-bordered table-hover bg-white">
		  <thead class="table-dark">
			<tr>
			  <th>ID</th>
			  <th>Shead No</th>
			  <th>Number of Tons</th>
			  <th>Date & Time</th>
			  <th>Actions</th>
			</tr>
		  </thead>
		  <tbody id="feed-data-body">
			<tr><td colspan="4" class="text-center">Loading...</td></tr>
		  </tbody>
		</table>
	  </div>
	</div>
	<div class="container my-5">
	  <h4 class="mb-3">Feed Tons by Shead (Graph)</h4>
	  <canvas id="sheadChart" height="100"></canvas>
	</div>


	<div class="modal fade" id="addDataModal" tabindex="-1" aria-labelledby="addDataLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<form id="feedForm" class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Add New Feed Entry</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		  </div>
		  <div class="modal-body">
			<input type="hidden" name="id" id="entryId" />
			<input type="hidden" name="client_id" value="<?= htmlspecialchars($client_id) ?>">
			<div class="mb-3">
			  <label for="sheadNo" class="form-label">Shead No</label>
			  <select class="form-select" id="sheadNo" name="sheadNo" required>
				<option value="">Select Shead</option>
				<?php foreach ($shead_list as $shead): ?>
				  <option value="<?= htmlspecialchars($shead) ?>"><?= htmlspecialchars($shead) ?></option>
				<?php endforeach; ?>
			  </select>
			</div>
			<div class="mb-3">
			  <label for="tons" class="form-label">Number of Tons</label>
			  <input type="number" class="form-control" id="tons" name="tons" required>
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="submit" class="btn btn-primary">Save Entry</button>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
		  </div>
		</form>
	  </div>
	</div>
	</main>
</div>
<script>
  const clientId = <?= json_encode($client_id) ?>;
  function normalizeShead(str) {
	  return str.toLowerCase().replace(/\s+/g, '_');
	}

  const API_URL = `https://sunfra.com/farm/sunfra_clients/feedrawmaterial/feed_to_shead_json.php?client_id=${clientId}`;
	const today = new Date().toISOString().split('T')[0];
	document.getElementById("filter-date").value = today;

 async function loadData() {
  const tbody = document.getElementById("feed-data-body");
  tbody.innerHTML = `<tr><td colspan="4" class="text-center">Loading...</td></tr>`;

  try {
    const res = await fetch(API_URL);
    const json = await res.json();
    const data = json[clientId] || [];

    const selectedDate = document.getElementById("filter-date").value;
    const filteredData = selectedDate
      ? data.filter(row => row.timestamp.split(" ")[0] === selectedDate)
      : data;

    if (filteredData.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center">No data available</td></tr>`;
    } else {
      tbody.innerHTML = filteredData.map(row => `
        <tr>
          <td>${row.id}</td>
		  <td>${normalizeShead(row.sheadNo)}</td>
          <td>${row.tons}</td>
          <td>${row.timestamp}</td>
          <td>
            <button class="btn btn-sm btn-warning edit-btn" 
                    data-id="${row.id}" 
                    data-shead="${normalizeShead(row.sheadNo)}" 
                    data-tons="${row.tons}">
              Edit
            </button>
          </td>
        </tr>
      `).join('');
    }

    const sheadMap = {};
    filteredData.forEach(row => {
      const key = row.sheadNo;
      sheadMap[key] = (sheadMap[key] || 0) + parseFloat(row.tons);
    });

    const chartLabels = Object.keys(sheadMap);
    const chartData = Object.values(sheadMap);

    if (window.sheadChartInstance) {
      window.sheadChartInstance.destroy();
    }

    const ctx = document.getElementById('sheadChart').getContext('2d');
    window.sheadChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [{
          label: 'Tons',
          data: chartData,
          backgroundColor: 'rgba(75, 192, 92, 0.6)',
			borderColor: 'rgba(75, 192, 92, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true },
          title: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Tons'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Shead'
            }
          }
        }
      }
    });

  } catch (error) {
    console.error("Fetch error:", error);
    tbody.innerHTML = `<tr><td colspan="4" class="text-danger text-center">Error loading data</td></tr>`;
  }
}

	document.addEventListener('click', function(e) {
	  if (e.target.classList.contains('edit-btn')) {
		const id = e.target.getAttribute('data-id');
		const shead = e.target.getAttribute('data-shead');
		const tons = e.target.getAttribute('data-tons');

		document.getElementById('entryId').value = id;
		document.getElementById('sheadNo').value = shead;
		document.getElementById('tons').value = tons;

		const modal = new bootstrap.Modal(document.getElementById('addDataModal'));
		modal.show();
	  }
	});

  document.getElementById("feedForm").addEventListener("submit", async function(e) {
    e.preventDefault();

	const formData = new FormData(this);
	const isEdit = formData.get('id'); 

    try {
      const res = await fetch("https://sunfra.com/farm/sunfra_clients/feedrawmaterial/feed_to_shead_save.php", {
        method: "POST",
        body: formData
      });

      const result = await res.json();
     if (result.status === "success") {
	  alert(isEdit ? "Entry updated successfully!" : "Data saved successfully!");
	  document.querySelector("#addDataModal .btn-close").click();
	  loadData(); 
	  this.reset();
	  document.getElementById('entryId').value = ''; 
	}
	 else {
        alert("Error: " + (result.message || "Failed to save data."));
      }
    } catch (err) {
      alert("Failed to submit form: " + err.message);
    }
  });

  loadData();
  	document.getElementById("filter-date").addEventListener("change", loadData);
	document.getElementById('addDataModal').addEventListener('hidden.bs.modal', () => {
	  document.getElementById('feedForm').reset();
	  document.getElementById('entryId').value = '';
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
