<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
if (empty($username)) {
    header("Location: ../login/login.php");
    exit;
}

$api_url = "https://sunfra.com/farm/sunfra_clients/login/farm_users_list.php";
$response = file_get_contents($api_url);

if ($response === false) {
    // API failed
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
    exit;
}

$users = json_decode($response, true);

$is_admin = false;
if (is_array($users)) {
    foreach ($users as $user) {
        if (isset($user['username']) && $user['username'] === $username) {
            if (isset($user['status']) && $user['status'] === 'admin') {
                $is_admin = true;
            }
            break;
        }
    }
}

if (!$is_admin) {
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Materials</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #ADD8E6;
      margin: 0;
      padding: 20px;
    }

    h2 {
      text-align: center;
      color: #003366;
      margin-bottom: 10px;
    }

    .top-bar {
      width: 90%;
      margin: 0 auto 10px auto;
      display: flex;
      gap: 16px;
      justify-content: flex-end;  /* Align search and button to the right; change to flex-start for left */
      align-items: center;
    }

    .top-bar input {
      padding: 8px;
      width: 200px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 15px;
    }

    .button-primary {
      padding: 8px 16px;
      background-color: #6366f1;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 500;
      transition: background 0.18s;
      font-size: 15px;
    }
    .button-primary:hover {
      background-color: #4f46e5;
    }
    .button-secondary {
      padding: 8px 16px;
      background-color: #d1d5db;
      color: #374151;
      border: 1px solid #9ca3af;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 500;
      transition: background 0.18s, color 0.18s;
      font-size: 15px;
    }
    .button-secondary:hover {
      background-color: #374151;
      color: #fff;
    }

    table {
      width: 90%;
      margin: 0 auto;
      border-collapse: collapse;
      background-color: #ffffff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    th, td {
	  padding: 12px 16px;
	  border-bottom: 1px solid #ccc;
	  border-right: 1px solid #ccc;
	  text-align: left;
	}

	/* Remove right border for the last column */
	th:last-child, td:last-child {
	  border-right: none;
	}

    th {
      background-color: #374151;
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 16px 20px;
      font-size: 15px;
      border-bottom: 2px solid #7b8794;
    }
    tr:hover {
      background-color: #f1f1f1;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 10;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: #ffffff;
      padding: 25px;
      border-radius: 10px;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      animation: fadeIn 0.3s ease-in-out;
    }
    .modal-content h3 {
      margin-top: 0;
      color: #6366f1; /* match primary button */
    }

    .modal-form-row {
      display: flex;
      align-items: center;
      margin: 12px 0;
    }
    .modal-form-row label {
      flex: 0 0 120px;
      margin-right: 10px;
      font-size: 15px;
    }
    .modal-form-row input,
    .modal-form-row select {
      flex: 1 1 auto;
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 15px;
      box-sizing: border-box;
    }

    .modal-footer {
      text-align: right;
    }
    .modal-footer button {
      margin-left: 10px;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
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
<h2>Available Materials</h2>

<div class="top-bar">
  <input type="text" id="searchInput" placeholder="Search by name..." oninput="filterMaterials()">
  <button class="button-primary" onclick="openAddModal()">+ Add Material</button>
</div>

<table id="materialsTable">
  <thead>
    <tr>
      <th>Material Name</th>
      <th>Price</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<div id="editModal" class="modal">
  <div class="modal-content">
    <h3>Edit Material</h3>
    <div class="modal-form-row">
      <label>Material Name:</label>
      <input type="text" id="editName" disabled>
    </div>
    <div class="modal-form-row">
      <label>Price:</label>
      <input type="number" id="editPrice">
    </div>
    <div class="modal-footer">
      <button class="button-secondary" onclick="closeModal('editModal')">Cancel</button>
      <button class="button-primary" onclick="saveEdit()">Save</button>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <h3>Add New Material</h3>
    <div class="modal-form-row">
      <label>Material Name:</label>
      <select id="newMaterialSelect">
        <option value="">-- Select Material --</option>
      </select>
    </div>
    <div class="modal-form-row">
      <label>Price:</label>
      <input type="number" id="newPrice">
    </div>
    <div class="modal-footer">
      <button class="button-secondary" onclick="closeModal('addModal')">Cancel</button>
      <button class="button-primary" onclick="addMaterial()">Add</button>
    </div>
  </div>
</div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
const clientId = <?php echo json_encode($client_id); ?>;
let materialsData = [];

function loadMaterials() {
  fetch(`https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/material_cost_json.php?client_id=${clientId}`)
    .then(res => res.json())
    .then(data => {
      materialsData = data[String(clientId)] || [];
      renderTable(materialsData);
    });
}

function filterMaterials() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const filtered = materialsData.filter(item => item.name.toLowerCase().includes(search));
  renderTable(filtered);
}

function renderTable(data) {
  const tbody = document.querySelector('#materialsTable tbody');
  tbody.innerHTML = '';
  data.forEach(item => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${item.name}</td>
      <td>${item.price ?? 'N/A'}</td>
      <td>
        <button class="button-primary" onclick="openEditModal(${item.id}, '${item.name}', ${item.price ?? 0})">Edit</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function loadMaterialOptions() {
  fetch(`https://sunfra.com/farm/sunfra_clients/feedrawmaterial/feed_raw_material_json.php?client_id=${clientId}`)
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById('newMaterialSelect');
      select.innerHTML = '<option value="">-- Select Material --</option>';

      const rawMaterials = data[String(clientId)] || [];

      const existingNames = materialsData.map(item => item.name.toLowerCase());

      rawMaterials.forEach(item => {
        if (!existingNames.includes(item.name.toLowerCase())) {
          const option = document.createElement('option');
          option.value = item.id;
          option.textContent = item.name;
          select.appendChild(option);
        }
      });
    });
}


function openEditModal(id, name, price) {
  document.getElementById('editId')?.remove();
  const hidden = document.createElement('input');
  hidden.type = 'hidden';
  hidden.id = 'editId';
  hidden.value = id;
  document.querySelector('#editModal .modal-content').prepend(hidden);
  document.getElementById('editName').value = name;
  document.getElementById('editPrice').value = price;
  document.getElementById('editModal').style.display = 'flex';
}

function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
  loadMaterialOptions();
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

function saveEdit() {
  const id = document.getElementById('editId').value;
  const name = document.getElementById('editName').value;
  const price = document.getElementById('editPrice').value;

  fetch('https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/material_cost_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id, name: name, price: price, client_id: clientId })
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
    closeModal('editModal');
    loadMaterials();
  });
}

function addMaterial() {
  const materialId = document.getElementById('newMaterialSelect').value;
  const price = document.getElementById('newPrice').value;
  const selectedOption = document.querySelector(`#newMaterialSelect option[value="${materialId}"]`);
  const materialName = selectedOption?.textContent.trim() || '';

  if (!materialName || !price) {
    alert('Please select material and enter price.');
    return;
  }

  const exists = materialsData.some(item => item.name.toLowerCase() === materialName.toLowerCase());
  if (exists) {
    alert(`Material "${materialName}" already exists.`);
    return;
  }

  fetch('https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/material_cost_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name: materialName, price: price, client_id: clientId })
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
    closeModal('addModal');
    loadMaterials();
  });
}

loadMaterials();
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
