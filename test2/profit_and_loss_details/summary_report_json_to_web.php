<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$client_id = $_SESSION['client_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Summary Report</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #ADD8E6; 
    }
    .title {
        text-align: center;
    }
    table { 
        border-collapse: collapse; 
        width: 100%; 
        margin-top: 20px; 
    }
    th, td {
        border: 1px solid #ccc; 
        padding: 8px 12px; /* Increased padding for bigger cells */
        text-align: center; 
        white-space: nowrap; 
        background-color: white;  
    }
    th { 
        background-color: orange; 
        color: white; 
    }
    .sub-header {
        background-color: #339966;
        color: white;
        font-weight: bold;
    }
    .section-border { 
        border-right: 8px solid green !important; 
    }
    th.sticky-date, td.sticky-date { 
        position: sticky; 
        left: 0; 
        background-color: white;
        z-index: 2; 
    }
    th.sticky-date { 
        background-color: orange; 
        z-index: 3; 
    }
    .date-col { 
        min-width: 150px; 
        max-width: 150px; 
    }
    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        
        gap: 10px;
        padding: 10px 20px;
        background-color: #009cab;
        border-radius: 10px;
        border: 1px solid orange;
        margin: 0 auto;
        max-width: fit-content;
        box-shadow: 0px 2px 5px rgba(0,0,0,0.1);
    }
    
    .filter-container label {
        font-weight: bold;
        color: #0e0a05ff;
    }
    .filter-container input[type="date"] {
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .filter-container button {
        background-color: #339966;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    .filter-container button:hover {
        background-color: darkorange;
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
<h2 class="title">Summary Report</h2>

<!-- ✅ Date Filter -->
<div class="filter-container">
    <label for="fromDate">From:</label>
    <input type="date" id="fromDate">

    <label for="toDate">To:</label>
    <input type="date" id="toDate">

    <button onclick="applyFilter()">Filter</button>
</div>

<div style="min-width: 100%; overflow: visible;">
    <table id="reportTable" style="min-width: max-content; width: 100%;"></table>
</div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
const client_id = <?php echo json_encode($client_id); ?>;
let allData = {}; 

async function fetchData() {
    let url = `https://sunfra.com/farm/test2/profit_and_loss_details/summary_report_json.php?client_id=${client_id}`;
    const response = await fetch(url);
    allData = await response.json();

    const today = new Date().toISOString().split("T")[0];
    document.getElementById("fromDate").value = today;
    document.getElementById("toDate").value = today;

    if (allData[today]) {
        renderTable({ [today]: allData[today] });
    } else {
        renderTable({});
    }
}

function applyFilter() {
    const fromDate = document.getElementById("fromDate").value;
    const toDate = document.getElementById("toDate").value;

    if (!fromDate && !toDate) {
        renderTable(allData);
        return;
    }

    const from = fromDate || toDate;
    const to = toDate || fromDate;

    const filteredData = {};
    for (let date in allData) {
        if (date >= from && date <= to) {
            filteredData[date] = allData[date];
        }
    }
    renderTable(filteredData);
}

function getDisplayName(key) {
    key = key.trim();
    const numberMatch = key.match(/\d+$/);
    if (numberMatch) return numberMatch[0];
    if (/scrap/i.test(key)) return "Scrap";
    if (/average/i.test(key)) return "Avg";
    if (/total/i.test(key)) return "Total";
    return key.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
}

function formatValue(value, columnName) {
    let num = parseFloat(value);
    if (isNaN(num)) return value; // Keep non-numeric values as is

    const col = columnName.toLowerCase();

    if (col.includes("production") || col.includes("damage") || col.includes("mortality")) {
        return Math.round(num);
    }

    return value;
}


function renderTable(data) {
    const table = document.getElementById('reportTable');
    table.innerHTML = '';

    if (!data || Object.keys(data).length === 0) {
        table.innerHTML = "<tr><td colspan='100%'>No data found</td></tr>";
        return;
    }

    let sectionRow = `<tr><th class="sticky-date date-col section-border" rowspan="2">Date</th>`;
    let subHeaderRow = `<tr>`;

    const firstDate = Object.keys(data)[0];
    const sections = data[firstDate];

    for (let sectionName in sections) {
        const subKeys = Object.keys(sections[sectionName]);
        let lastSubKeyIndex = subKeys.length - 1;

        sectionRow += `<th colspan="${subKeys.length}" class="section-border">${sectionName}</th>`;

        subKeys.forEach((subKey, index) => {
            let extraClass = (index === lastSubKeyIndex) ? 'section-border' : '';
            subHeaderRow += `<th class="sub-header ${extraClass}">${getDisplayName(subKey)}</th>`;
        });
    }

    sectionRow += `</tr>`;
    subHeaderRow += `</tr>`;

    table.innerHTML += sectionRow + subHeaderRow;

    for (let date in data) {
        let row = `<tr><td class="sticky-date date-col section-border">${date}</td>`;
        for (let sectionName in data[date]) {
            const subKeys = Object.keys(data[date][sectionName]);
            subKeys.forEach((subKey, index) => {
                let value = formatValue(data[date][sectionName][subKey], subKey);
                let extraClass = (index === subKeys.length - 1) ? 'section-border' : '';
                row += `<td class="${extraClass}">${value}</td>`;
            });
        }
        row += `</tr>`;
        table.innerHTML += row;
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

fetchData();
</script>

</body>
</html>
