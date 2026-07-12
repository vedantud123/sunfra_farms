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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<title>Egg Cutting Price</title>
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #d4f1ff, #c8e9f0);
    padding: 30px;
    margin: 0;
    color: #333;
    background-attachment: fixed;
    backdrop-filter: blur(10px);
  }
  #header {
  text-align: center;
  margin-bottom: 20px;
}
 
#action-bar {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap; /* optional: allows responsiveness */
}
 
#action-bar input {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  max-width: 250px;
}
 
#action-bar button {
  padding: 8px 15px;
  background-color: #0099cc;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}
 
#action-bar button:hover {
  background-color: #007ba7;
}
 
 
 
  #top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
  }
 
  h1 {
    color: #004080;
    font-size: 32px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    margin: 0;
  }
 
 #add-entry {
  padding: 10px 20px;
  background: linear-gradient(to right, #003366, #0059b3);
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
  margin-bottom: 0; /* ensure alignment */
}
 
#add-entry:hover {
  background: linear-gradient(to right, #0059b3, #003366);
  transform: scale(1.05);
}
 
  #searchInput {
  padding: 10px 14px;
  width: 280px;
  border: 2px solid rgba(0, 102, 204, 0.6);
  border-radius: 10px;
  outline: none;
  font-size: 16px;
  background: #e6f3ff;
  color: #003344;
  box-shadow: 0 0 8px rgba(0, 102, 204, 0.2);
  margin-bottom: 0; /* remove extra space */
}
  table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 18px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    animation: fadeIn 1s ease;
  }
 
  thead {
    background: #003366;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 16px;
  }
 
  th {
    padding: 18px;
    backdrop-filter: blur(4px);
    background: #003366;
    border-bottom: 3px solid #0066cc;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    font-weight: bold;
    color: #ffffff;
  }
 
  tbody tr:nth-child(odd) {
    background: linear-gradient(to right, #e3f2fd, #f1f8ff);
  }
 
  tbody tr:nth-child(even) {
    background: linear-gradient(to right, #f0faff, #e6faff);
  }
 
  tbody tr:hover {
    background: rgba(255, 255, 255, 0.6);
    transform: scale(1.01);
    transition: all 0.3s ease;
  }
 
  td {
    padding: 14px;
    text-align: center;
    font-size: 15px;
    border-top: 1px solid rgba(255,255,255,0.2);
  }
 
  td button {
    padding: 8px 16px;
    background: linear-gradient(to right, #003366, #0059b3);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s;
  }
 
  td button:hover {
    background: linear-gradient(to right, #0059b3, #003366);
    transform: scale(1.05);
  }
 
  /* Modal Styling */
  #form-modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(5px);
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
 
  #form-modal.active {
    display: flex;
  }
 
  #form-modal .modal-content {
    background: rgba(255, 255, 255, 0.9);
    padding: 30px;
    border-radius: 16px;
    width: 400px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    animation: fadeIn 0.4s ease-in-out;
  }
 
  #form-modal h2 {
    color: #004080;
    text-align: center;
    margin-bottom: 20px;
  }
 
.modal-content input[type="number"],
.modal-content select,
.modal-content input[type="text"] {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 14px;
  margin-bottom: 16px;
  border-radius: 10px;
  border: 1px solid #ccc;
  font-size: 15px;
  background: rgba(255, 255, 255, 0.8);
  display: block;
}
 
 
  input:focus, select:focus {
    border-color: #0059b3;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.2);
  }
 
  button[type="submit"], button[type="button"] {
    padding: 10px 18px;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
  }
 
  button[type="submit"] {
    background: #003366;
    color: white;
  }
 
  button[type="submit"]:hover {
    background: linear-gradient(to right, #0059b3, #0066cc);
    transform: scale(1.05);
  }
 
  button[type="button"] {
    background: #888;
    margin-left: 12px;
    color: #fff;
  }
 
  button[type="button"]:hover {
    background: #666;
    transform: scale(1.03);
  }
 
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
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
<div id="header">
  <h1>Egg Cutting Price List</h1>
</div>
 
<div id="action-bar">
  <input type="text" id="searchInput" placeholder="Search by Shead Name...">
  <button id="add-entry">+ Add Entry</button>
</div>
 
 
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Shead Name</th>
      <th>Cutting Price (₹)</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="table-body">
    <tr><td colspan="5">Loading...</td></tr>
  </tbody>
</table>
 
<div id="form-modal">
  <div class="modal-content">
    <h2 id="form-title">Add Entry</h2>
    <form id="entry-form">
	<input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
      <input type="hidden" id="entry-id" name="id" value="">
 
      <label>Shead Name:</label>
      <select id="shead_name_select" name="shead_name" required></select>
      <input type="text" id="shead_name_input" name="shead_name" readonly style="display:none;"><br>
 
      <label>Cutting Price:</label>
      <input type="number" id="cutting_price" name="cutting_price" step="0.01" required>
 
      <button type="submit">Submit</button>
      <button type="button" onclick="closeForm()">Cancel</button>
    </form>
  </div>
</div>
 <canvas id="priceChart" width="200" height="50" style="margin-top: 40px;"></canvas>

<script>
  const urlParams = new URLSearchParams(window.location.search);
  const client_id = "<?php echo $client_id; ?>"; 
 
  let clientData = [];
 
  const formModal = document.getElementById('form-modal');
  const form = document.getElementById('entry-form');
  const formTitle = document.getElementById('form-title');
  const sheadSelect = document.getElementById('shead_name_select');
  const sheadInput = document.getElementById('shead_name_input');
  const priceInput = document.getElementById('cutting_price');
  const idInput = document.getElementById('entry-id');
 
  document.getElementById('searchInput').addEventListener('input', function () {
    const searchTerm = this.value.trim().toLowerCase();
    renderCards(searchTerm);
  });
 
  document.getElementById('add-entry').addEventListener('click', () => {
    openForm('add');
  });
 
  function openForm(mode, data = {}) {
    formTitle.innerText = mode === 'edit' ? "Edit Entry" : "Add Entry";
    idInput.value = data.id || "";
    priceInput.value = data.cutting_price || "";
 
    if (mode === 'edit') {
      sheadSelect.style.display = 'none';
      sheadSelect.disabled = true;
 
      sheadInput.style.display = 'block';
      sheadInput.value = data.shead || '';
      sheadInput.disabled = false;
    } else {
      sheadSelect.style.display = 'block';
      sheadSelect.disabled = false;
 
      sheadInput.style.display = 'none';
      sheadInput.value = '';
      sheadInput.disabled = true;
 
      populateSheadDropdown();
    }
 
    formModal.classList.add('active');
  }
 
  function closeForm() {
    formModal.classList.remove('active');
  }
 
  function populateSheadDropdown() {
    fetch(`https://sunfra.com/farm/sunfra_clients/configuration/shead_number_json.php?client_id=${client_id}`)
      .then(res => res.json())
      .then(data => {
        sheadSelect.innerHTML = `<option value="">-- Select Shead --</option>`;
        data.forEach(item => {
          const option = document.createElement('option');
          option.value = item.shead_name;
          option.textContent = item.shead_name;
          sheadSelect.appendChild(option);
        });
      })
      .catch(err => {
        console.error("Failed to load shead list", err);
      });
  }
 
	fetch('https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/egg_cutting_json.php?client_id=${client_id}')
	  .then(response => response.json())
	  .then(data => {
		console.log("Full JSON Response:", data);
		console.log("Expected client ID:", client_id);

		const clientKey = client_id.toString();
		if (data.hasOwnProperty(clientKey)) {
		  clientData = data[clientKey];
		} else {
		  clientData = [];
		}

		renderCards();
	  })
 
	function renderCards(searchTerm = '') {
	  const tableBody = document.getElementById('table-body');
	  tableBody.innerHTML = '';

	  const filteredData = clientData.filter(item =>
		item.shead.toLowerCase().includes(searchTerm)
	  );

	  if (filteredData.length === 0) {
		tableBody.innerHTML = '<tr><td colspan="5">No records found</td></tr>';
		return;
	  }

	  filteredData.forEach((item, index) => {
		const tr = document.createElement('tr');
		tr.innerHTML = `
		  <td>${index + 1}</td>
		  <td>${item.shead}</td>
		  <td>₹${parseFloat(item.cutting_price).toFixed(2)}</td>
		  <td><button onclick='editItem(${JSON.stringify(item)})'>Edit</button></td>
		`;
		tableBody.appendChild(tr);
	  });
	  renderChart(filteredData);
	}
 
  function editItem(item) {
	  openForm('edit', item);
	}
	function reloadData() {
	  fetch(`https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/egg_cutting_json.php?client_id=${client_id}`)
		.then(response => response.json())
		.then(data => {
		  const clientKey = client_id.toString();
		  clientData = data.hasOwnProperty(clientKey) ? data[clientKey] : [];
		  renderCards(document.getElementById('searchInput').value.trim().toLowerCase());
		})
		.catch(err => {
		  console.error("Failed to reload data:", err);
		  alert("❌ Failed to reload table data.");
		});
	}

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(form);
 
    fetch('https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/egg_cutting_save.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(res => {
        alert(res.message);
        if (res.status === 'success') {
          closeForm();
          reloadData();
        }
      })
      .catch(err => {
        alert("❌ Error: " + err.message);
      });
  });
 
  reloadData();
    let chartInstance;
 
	function renderChart(data) {
	  const ctx = document.getElementById('priceChart').getContext('2d');
	 
	  const labels = data.map(item => item.shead);
	  const prices = data.map(item => parseFloat(item.cutting_price));
	 
	  // Destroy previous chart if it exists
	  if (chartInstance) {
		chartInstance.destroy();
	  }
	 
	  chartInstance = new Chart(ctx, {
		type: 'bar',
		data: {
		  labels: labels,
		  datasets: [{
			label: 'Cutting Price (₹)',
			data: prices,
			backgroundColor: 'rgba(0, 166, 255, 0.6)',
			borderColor: 'rgba(0, 120, 200, 1)',
			borderWidth: 1,
			borderRadius: 6,
		  }]
		},
		options: {
		  responsive: true,
		  plugins: {
			legend: { display: false },
			title: {
			  display: true,
			  text: 'Cutting Price by Shead',
			  font: {
				size: 18
			  }
			}
		  },
		  scales: {
			y: {
			  beginAtZero: true,
			  ticks: {
				callback: value => '₹' + value
			  }
			}
		  }
		}
	  });
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
	  document.addEventListener("DOMContentLoaded", function () {
    const clientId = "<?php echo $client_id; ?>";
    const apiUrl = "https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_production_json.php?client_id=" + clientId;

    fetch(apiUrl)
      .then(response => response.json())
      .then(data => {
        console.log("API Data:", data);
        // Do something with the data here, like display in table, etc.
      })
      .catch(error => {
        console.error("Error fetching data:", error);
      });
  });
</script>
</main>
</div>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
</body>
</html>