<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
$client_id = $_SESSION['client_id'];
date_default_timezone_set('Asia/Kolkata');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Labour Salaries</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #ADD8E6;
    }
    .btn-add {
      background-color: #28a745;
      color: white;
    }
    .btn-edit {
      background-color: #ffc107;
      color: black;
    }
    .btn-edit:hover {
      background-color: #e0a800;
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
  <a href="https://sunfra.com/farm/test2/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/test2/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/test2/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/test2/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-user-check"></i>
      <span class="label">Attendance <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="attendanceSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/labour_master_json_to_web.php'">👷‍♂️ Labour Details</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/labour_attendance_json_to_web.php'">📅 Labour Attendance</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/assigned_master_json_to_web.php'">📝 Assigned Master</button>
    </div>
  </div>
	<div class="attendance-dropdown">
	  <a onclick="toggleShed()" class="attendance-toggle">
		<i class="fas fa-users-cog"></i>
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-industry"></i>
      <span class="label">Feed Plant Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="feedPlantSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/feed_formula/feed_formula_json_to_web.php'">⚙️ Feed Formula</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/feed_raw_material_json_to_web.php'">📥 Feed Raw Material</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/feed_to_shead_json_to_web.php'">🚚 Feed Material To Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/water_medicine_json_to_web.php'">🧪 Water Medicine</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-warehouse"></i>
      <span class="label">Egg Godown <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="eggGodownSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_godown_stock_json_to_web.php'">🥚 Egg Production From Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_godown_sale_json_to_web.php'">💰 Eggs for Sale</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_weight.php'">⚖️ Egg Weight</button>
    	<button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_damaged_json_to_web.php'">⚖️ Weekly Egg Damages</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-chart-line"></i>
      <span class="label">Profit & Loss <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="profitLossSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/material_cost_json_to_web.php'">Feed Material Price</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/egg_cutting_json_to_web.php'">Egg Price Per Piece</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/profit_and_loss_daily.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/test2/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/test2/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/test2/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/test2/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
<div class="container py-4">
  <h2 class="text-center mb-4">Labour Salary Details</h2>

  <div class="d-flex justify-content-end align-items-center mb-3 gap-2 flex-wrap">
	  <input type="text" id="searchInput" class="form-control w-auto" placeholder="Search by name or position..." onkeyup="searchTable()">
	  <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#entryModal" onclick="openAddForm()">+ Add New</button>
	</div>

  <div id="loading" class="text-center">
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-2">Loading data...</p>
  </div>

  <div id="tableContainer" class="table-responsive" style="display:none;">
    <table class="table table-bordered table-striped text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Salary</th>
          <th>Position</th>
          <th>Edit</th>
        </tr>
      </thead>
      <tbody id="salaryTableBody"></tbody>
    </table>
  </div>
</div>

<!-- Modal for Add/Edit Form -->
<div class="modal fade" id="entryModal" tabindex="-1" aria-labelledby="entryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="salaryForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="entryModalLabel">Add / Edit Labour Salary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="form-id">
        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">

        <div class="mb-3">
          <label for="form-name" class="form-label">Select Name</label>
          <select name="name" id="form-name" class="form-select" required>
            <option value="">--Select--</option>
            <option value="ARUN KUMAR">ARUN KUMAR</option>
            <option value="B prakash">B prakash</option>
            <option value="u">u</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="form-salary" class="form-label">Salary (₹)</label>
          <input type="number" name="salary" id="form-salary" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="form-position" class="form-label">Position</label>
          <select name="position" id="form-position" class="form-select" required>
            <option value="">--Select--</option>
            <option value="Labour">Labour</option>
            <option value="Manager">Manager</option>
            <option value="Supervisor">Supervisor</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
</main>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script>
const client_id = <?php echo $client_id; ?>;
const api_url = `https://sunfra.com/farm/test2/profit_and_loss_details/labour_salary_json.php?client_id=${client_id}`;
const save_url = `https://sunfra.com/farm/test2/profit_and_loss_details/labour_salary_save.php`;
const labourMasterUrl = `https://sunfra.com/farm/test2/attendance/labour_master_json.php?client_id=${client_id}`;
let existingNames = [];
let allLabourNames = [];
let currentEditingName = "";

function fetchAndDisplayData() {
  fetch(api_url)
    .then(res => res.json())
    .then(data => {
      const result = data[client_id];
      const tbody = document.getElementById("salaryTableBody");
      tbody.innerHTML = "";
      existingNames = [];

      if (result && result.length > 0) {
        result.forEach(row => {
          existingNames.push(row.name.trim());
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${row.id}</td>
            <td>${row.name}</td>
            <td>₹${row.salary}</td>
            <td>${row.position}</td>
            <td>
              <button class="btn btn-sm btn-edit" onclick='openEditForm(${JSON.stringify(row)})'>Edit</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
        document.getElementById("loading").style.display = "none";
        document.getElementById("tableContainer").style.display = "block";
      } else {
        document.getElementById("loading").innerHTML = "<p>No data found.</p>";
      }

      fetchLabourNames();
    })
    .catch(err => {
      console.error(err);
      document.getElementById("loading").innerHTML = "<p>Error fetching data.</p>";
    });
}

function fetchLabourNames() {
  fetch(labourMasterUrl)
    .then(res => res.json())
    .then(data => {
      allLabourNames = data[client_id].map(x => x.name.trim());
      populateNameOptions();
    })
    .catch(err => {
      console.error("Failed to fetch labour master:", err);
    });
}

function populateNameOptions() {
  const select = document.getElementById("form-name");
  const selectedName = currentEditingName;

  select.innerHTML = `<option value="">--Select--</option>`;

  allLabourNames.forEach(name => {
    // Only add if not already in salary OR it is the currently editing one
    if (!existingNames.includes(name) || name === selectedName) {
      const option = document.createElement("option");
      option.value = name;
      option.textContent = name;
      if (name === selectedName) option.selected = true;
      select.appendChild(option);
    }
  });
}

function openAddForm() {
  document.getElementById("salaryForm").reset();
  document.getElementById("form-id").value = "";
  document.getElementById("entryModalLabel").innerText = "Add Labour Salary";
  currentEditingName = "";
  populateNameOptions(); // Repopulate with filtered options
}

function openEditForm(data) {
  document.getElementById("form-id").value = data.id;
  document.getElementById("form-name").value = data.name;
  document.getElementById("form-salary").value = data.salary;
  document.getElementById("form-position").value = data.position;
  document.getElementById("entryModalLabel").innerText = "Edit Labour Salary";
  currentEditingName = data.name.trim();
  populateNameOptions(); // Repopulate with editing name included

  const modal = new bootstrap.Modal(document.getElementById('entryModal'));
  modal.show();
}


document.getElementById("salaryForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch(save_url, {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    console.log("Response:", response);
    bootstrap.Modal.getInstance(document.getElementById('entryModal')).hide();
    fetchAndDisplayData(); // Refresh table
  })
  .catch(err => {
    alert("Failed to save. Try again.");
    console.error(err);
  });
});

fetchAndDisplayData(); // Load data on page load
function searchTable() {
  const input = document.getElementById("searchInput");
  const filter = input.value.toLowerCase();
  const table = document.getElementById("salaryTableBody");
  const rows = table.getElementsByTagName("tr");

  for (let row of rows) {
    const nameCell = row.cells[1]?.textContent.toLowerCase();
    const positionCell = row.cells[3]?.textContent.toLowerCase();

    if (nameCell.includes(filter) || positionCell.includes(filter)) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  }
}
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
</body>
</html>
