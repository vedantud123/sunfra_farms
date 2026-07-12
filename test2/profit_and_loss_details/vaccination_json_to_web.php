<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
$client_id = $_SESSION['client_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vaccination Cost Records</title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      padding: 20px;
      background-color: #ADD8E6;
      margin: 0;
    }
    h2 {
      text-align: center;
      color: #243c5a;
      margin-bottom: 30px;
    }
    .controls {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      align-items: center;
      gap: 16px;
      margin-bottom: 18px;
    }
    input[type="text"], input[type="number"], select {
      padding: 10px;
      border: 1.5px solid #b3c6de;
      border-radius: 8px;
      font-size: 16px;
      outline: none;
      transition: border 0.2s;
    }
    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
      border: 1.5px solid #4f8cff;
    }
    button {
      padding: 11px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 500;
      transition: background 0.22s, color 0.22s;
    }
    .add-btn {
      background: linear-gradient(90deg, #36d1c4, #5b86e5);
      color: white;
      box-shadow: 0 2px 8px rgba(54, 209, 196, 0.07);
    }
    .add-btn:hover {
      background: linear-gradient(90deg, #5b86e5, #36d1c4);
    }
    .edit-btn {
      background: linear-gradient(90deg, #5b86e5, #36d1c4);
      color: #fff;
      border: none;
    }
    .edit-btn:hover {
      background: linear-gradient(90deg, #36d1c4, #5b86e5);
      color: #fff;
    }
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: #fff;
      box-shadow: 0 8px 20px 0 rgba(44, 62, 80, 0.06), 0 1.5px 3px 0 #b3c6de19;
      border-radius: 15px;
      overflow: hidden;
      margin-top: 10px;
    }
    th, td {
      padding: 15px 14px;
      text-align: center;
      font-size: 15px;
    }
    th {
      background-color: #243c5a;
      color: #fff;
      letter-spacing: 2px;
      font-weight: bold;
      border-bottom: 3px solid #36d1c4;
    }
    tbody tr:nth-child(even) {
      background: #f5fbff;
    }
    tbody tr:hover {
      background: #e2f1fb !important;
      transition: background 0.13s;
    }
    #modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(36, 60, 90, 0.15);
      z-index: 999;
    }
    #modalContent {
      background: #fff;
      margin: 6% auto;
      padding: 34px 22px 25px 22px;
      width: 95%;
      max-width: 400px;
      border-radius: 15px;
      position: relative;
      box-shadow: 0 8px 32px 0 rgba(54, 132, 229,0.21), 0 2px 8px 0 #36413e0f;
      animation: fadeIn 0.32s cubic-bezier(.61,1,.88,1) 1;
    }
    #modalTitle {
      font-size: 22px;
      margin-bottom: 20px;
      color: #4f8cff;
      font-weight: bold;
      text-align: center;
      letter-spacing: 1px;
    }
    #dataForm input:focus, #dataForm select:focus {
      border: 1.5px solid #36d1c4;
    }
    #form-actions-row {
      display: flex;
      gap: 10px;
      margin-top: 8px;
    }
    #dataForm button[type="submit"] {
      letter-spacing: 1px;
      font-size: 16px;
      width: 100%;
      flex: 1;
    }
    #dataForm button[type="button"] {
      width: 100%;
      flex: 1;
      font-size: 16px;
    }
    #closeBtn {
      position: absolute;
      right: 16px;
      top: 16px;
      cursor: pointer;
      background: #fa5656;
      color: white;
      padding: 6px 14px;
      border-radius: 50%;
      font-weight: bold;
      font-size: 17px;
      border: none;
      box-shadow: 0 2px 10px #fa56560f;
      transition: background 0.18s;
    }
    #closeBtn:hover {
      background: #d42b2b;
    }
    #dataForm {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
    }
    .form-row {
      display: flex;
      align-items: center;
      width: 100%;
      margin-bottom: 15px;
      justify-content: flex-start;
    }
    .form-row label {
      width: 135px;  /* Fixed width for all labels */
      font-weight: 600;
      color: #243c5a;
      font-size: 15px;
      margin: 0;
      text-align: right;
      margin-right: 12px;
      flex-shrink: 0;
    }
    .form-row input,
    .form-row select {
      flex: 1;
      padding: 10px;
      font-size: 15px;
      border: 1.5px solid #b3c6de;
      border-radius: 8px;
      box-sizing: border-box;
      outline: none;
      transition: border 0.18s;
    }
    .form-row input:focus,
    .form-row select:focus {
      border-color: #36d1c4;
    }
    @media screen and (max-width: 600px) {
      .controls {
        flex-direction: column;
        align-items: stretch;
      }
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead {
        display: none;
      }
      tr {
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px;
        background-color: #fff;
      }
      td {
        text-align: left;
        padding-left: 50%;
        position: relative;
      }
      td::before {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: bold;
        color: #36d1c4;
      }
      td:nth-of-type(1)::before { content: "ID"; }
      td:nth-of-type(2)::before { content: "Vaccine Name"; }
      td:nth-of-type(3)::before { content: "Vaccine Cost"; }
      td:nth-of-type(4)::before { content: "Labour Cost"; }
      td:nth-of-type(5)::before { content: "Timestamp"; }
      td:nth-of-type(6)::before { content: "Shead Number"; }
      td:nth-of-type(7)::before { content: "Action"; }
    }
    @keyframes fadeIn {
      0%   { opacity: 0; transform: scale(0.95);}
      100% { opacity: 1; transform: scale(1);}
    }
 
    /* Notification style */
    #notification {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #36d1c4;
      color: white;
      padding: 14px 22px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(54, 209, 196, 0.7);
      font-weight: bold;
      display: none;
      z-index: 1000;
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
<h2>Vaccination Cost Details</h2>
 
<div id="notification"></div>
 
<div class="controls">
  <input type="text" id="searchInput" placeholder="Search..." onkeyup="filterTable()">
  <button class="add-btn" onclick="openForm()">Add Record</button>
</div>
<table id="vaccineTable">
  <thead>
    <tr>
      <th>Shead Number</th>
      <th>Vaccine Name</th>
      <th>Vaccine Cost</th>
      <th>Labour Cost</th>
      <th>Timestamp</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="tableBody">
    <!-- Rows go here -->
  </tbody>
</table>
 
<!-- Modal Form -->
<div id="modal">
  <div id="modalContent">
    <h3 id="modalTitle">Add Record</h3>
    <form id="dataForm" onsubmit="handleSubmit(event)">
      <!-- Hidden input for record ID -->
      <input type="hidden" id="form_id" value="">
	  <input type="hidden" id="client_id" value="<?= htmlspecialchars($client_id) ?>">
 
      <div class="form-row">
        <label for="shead_number">Shead Number:</label>
        <select id="shead_number" required>
          <option value="">Loading...</option>
        </select>
      </div>
      <div class="form-row">
        <label for="vaccine_name">Vaccine Name:</label>
        <input type="text" id="vaccine_name" required>
      </div>
      <div class="form-row">
        <label for="vaccine_cost">Vaccine Cost:</label>
        <input type="number" id="vaccine_cost" required>
      </div>
      <div class="form-row">
        <label for="labour_cost">Labour Cost:</label>
        <input type="number" id="labour_cost" required>
      </div>
      <div id="form-actions-row">
        <button type="submit" class="add-btn">Submit</button>
        <button type="button" class="edit-btn" onclick="closeForm()">Cancel</button>
      </div>
    </form>
  </div>
</div>
 </main>
 </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
 
<script>

const client_id = <?php echo json_encode($client_id); ?>;
const apiUrl = `https://sunfra.com/farm/test2/profit_and_loss_details/vaccination_json.php?client_id=${client_id}`;
const postUrl = `https://sunfra.com/farm/test2/profit_and_loss_details/vaccination_save.php`;
const sheadApiUrl = `https://sunfra.com/farm/test2/configuration/shead_number_json.php?client_id=${client_id}`;
 
async function loadSheadOptions(selectedValue = "") {
  const res = await fetch(sheadApiUrl);
  const sheadData = await res.json();
  const select = document.getElementById("shead_number");
  select.innerHTML = `<option value="">Select Shead</option>`;
  sheadData.forEach(item => {
    const opt = document.createElement("option");
    opt.value = item.shead_name;
    opt.textContent = item.shead_name;
    select.appendChild(opt);
  });
  if (selectedValue) select.value = selectedValue;
}
 
// Load vaccination records into table
async function loadTable() {
  const res = await fetch(apiUrl);
  const data = await res.json();
  const tbody = document.getElementById("tableBody");
  tbody.innerHTML = "";
  Object.values(data).flat().forEach(row => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
       <td>${row.shead_number}</td>
      <td>${row.vaccine_name}</td>
      <td>${row.vaccine_cost}</td>
      <td>${row.labour_cost}</td>
      <td>${row.timestamp}</td>
     
      <td><button class="edit-btn" onclick='editRecord(${JSON.stringify(row)})'>Edit</button></td>
    `;
    tbody.appendChild(tr);
  });
}
 
function filterTable() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const rows = document.querySelectorAll("#tableBody tr");
  rows.forEach(row => {
    row.style.display = Array.from(row.cells).some(td =>
      td.textContent.toLowerCase().includes(input)
    ) ? "" : "none";
  });
}
 
function openForm() {
  document.getElementById("modalTitle").textContent = "Add Record";
  document.getElementById("dataForm").reset();
  document.getElementById("form_id").value = "";
  document.getElementById("modal").style.display = "block";
  loadSheadOptions();
}
 
function closeForm() {
  document.getElementById("modal").style.display = "none";
}
 
function editRecord(data) {
  document.getElementById("modalTitle").textContent = "Edit Record";
  document.getElementById("form_id").value = data.id;
  document.getElementById("vaccine_name").value = data.vaccine_name;
  document.getElementById("vaccine_cost").value = data.vaccine_cost;
  document.getElementById("labour_cost").value = data.labour_cost;
  document.getElementById("modal").style.display = "block";
  loadSheadOptions(data.shead_number);
}
 
async function handleSubmit(e) {
  e.preventDefault();
  const formData = new FormData();
  formData.append("id", document.getElementById("form_id").value);
  formData.append("client_id", document.getElementById("client_id").value);
  formData.append("vaccine_name", document.getElementById("vaccine_name").value);
  formData.append("vaccine_cost", document.getElementById("vaccine_cost").value);
  formData.append("labour_cost", document.getElementById("labour_cost").value);
  formData.append("shead_number", document.getElementById("shead_number").value);
  formData.append("timestamp", new Date().toISOString().slice(0,19).replace("T"," "));
 
  const res = await fetch(postUrl, {
    method: "POST",
    body: formData
  });
 
  const result = await res.json();
  if (result.success) {
    closeForm();
    loadTable();
    showNotification(result.message || "Record successfully saved.");
  } else {
    alert("Error: " + result.error);
  }
}
 
function showNotification(message) {
  const notification = document.getElementById('notification');
  notification.textContent = message;
  notification.style.display = 'block';
 
  // Hide after 3 seconds
  setTimeout(() => {
    notification.style.display = 'none';
  }, 3000);
}
 
// Initial load of table data
loadTable();

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
 
 
