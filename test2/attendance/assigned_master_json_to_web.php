<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assigned Master</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 0;
    }

    h1 {
      text-align: center;
      color: #3f51b5;
      margin: 20px 0;
    }

    

    /* Table styling */
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    th, td {
        padding: 12px 15px;
        border: 1px solid #ddd;
        vertical-align: middle;
        text-align: center;
    }
    th {
        background-color: #4CBB17;
        color: white;
    }
    tr:nth-child(even) { background-color: #f9f9f9; }
    tr:hover { background-color: #e8f0fe; }

    /* Radio button styling */
    .radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
    }
    .radio-group label {
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        background: #f7f9fc;
        padding: 3px 6px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    .radio-group label:hover { background: #dbeafe; }

    /* Button */
    .btn-submit {
        display: block;
        margin: 20px auto 0;
        padding: 10px 20px;
        background: #3f51b5;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }
    .btn-submit:hover { background: #303f9f; }

    /* Status message */
    .status-msg {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        font-weight: bold;
        color: #333;
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
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/new_labour_master.php'">📝 Assigned Master</button>
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
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/profit_loss_json_to_web.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/test2/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/test2/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>
  <a href="https://sunfra.com/farm/test2/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/test2/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/test2/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>

<body>
    <main class="content main-content">

<div class="container">
    <h1>Assign People to Locations</h1>

    <form id="assignForm">
        <table id="assignTable">
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Person Assigned</th>
                    <th>Assign / Update</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" class="btn-submit" onclick="submitAllAssignments()">Submit All Assignments</button>
    </form>

    <div id="statusMsg" class="status-msg"></div>
</div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
let locations = [];
let users = [];
let assignments = {};

const client_id = <?php echo (int)$client_id; ?>;
const today = "<?php echo $today; ?>";

async function fetchJSON(url) {
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.json();
    } catch (err) {
        console.error("Fetch error:", err);
        return {};
    }
}

async function loadAssignments() {
    // Fetch locations
    const locRes = await fetchJSON(`https://sunfra.com/farm/test2/configuration/config_location_json.php?client_id=${client_id}`);
    locations = locRes[client_id] || [];

    // Fetch users
    const userRes = await fetchJSON(`https://sunfra.com/farm/test2/login/farm_users_json.php`);
users = (userRes.users || []).filter(u => u.client_id == client_id );

    // Fetch existing assignments
    const assignRes = await fetchJSON(`assigned_master_json.php?date=${today}&client_id=${client_id}`);
    assignments = {};
    if (assignRes.status === "success") {
        assignRes.assignments.forEach(r => {
            assignments[r.location] = r.person_name;
        });
    }

    renderTable();
}

function renderTable() {
    const tbody = document.querySelector('#assignTable tbody');
    tbody.innerHTML = '';

    if (locations.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align:center;color:gray;">No locations found</td></tr>`;
        return;
    }

    locations.forEach(loc => {
        const tr = document.createElement('tr');

        // Location name
        tr.innerHTML += `<td>${loc.location}</td>`;

        // Assigned person
        tr.innerHTML += `<td id="assigned_${loc.id}">${assignments[loc.location] || ''}</td>`;

        // Radio buttons for assigning users
        let radioHTML = `<div class="radio-group">`;
        if (users.length > 0) {
            users.forEach(u => {
                const checked = assignments[loc.location] === u.username ? 'checked' : '';
                radioHTML += `
                    <label>
                        <input type="radio" name="assign_${loc.id}" value="${u.username}" ${checked}
                            onchange="autoSaveAssignment('${loc.id}','${loc.location}','${u.username}')"> ${u.username}
                    </label>
                `;
            });
        } else {
            radioHTML += `<span style="color:gray;">No users available</span>`;
        }
        radioHTML += `</div>`;

        tr.innerHTML += `<td>${radioHTML}</td>`;
        tbody.appendChild(tr);
    });
}

async function autoSaveAssignment(locationId, locationName, person) {
    document.querySelectorAll(`input[name='assign_${locationId}']`).forEach(el => el.disabled = true);

    const dataToSave = [{ date: today, location: locationName, person_name: person, client_id: client_id }];

    try {
        const res = await fetch('assigned_master_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataToSave)
        });
        const data = await res.json();

        if (data.status === 'success') {
            document.getElementById(`assigned_${locationId}`).textContent = person;
        } else {
            showStatus('❌ Error saving assignment.', 'red');
        }
    } catch (err) {
        showStatus('⚠️ Network error while saving.', 'orange');
    }

    document.querySelectorAll(`input[name='assign_${locationId}']`).forEach(el => el.disabled = false);
}

async function submitAllAssignments() {
    let dataToSave = [];
    locations.forEach(loc => {
        let selected = document.querySelector(`input[name='assign_${loc.id}']:checked`);
        if (selected) {
            dataToSave.push({
                date: today,
                location: loc.location,
                person_name: selected.value,
                client_id: client_id
            });
        }
    });

    if (dataToSave.length === 0) {
        showStatus("⚠️ No assignments selected.", "orange");
        return;
    }

    try {
        const res = await fetch('assigned_master_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dataToSave)
        });
        const data = await res.json();

        if (data.status === 'success') {
            showStatus("✅ All assignments saved successfully!", "green");
            loadAssignments();
        } else {
            showStatus("❌ Error saving assignments.", "red");
        }
    } catch (err) {
        showStatus("⚠️ Network error while saving.", "orange");
    }
}

function showStatus(msg, color) {
    const el = document.getElementById('statusMsg');
    el.textContent = msg;
    el.style.color = color;
}

loadAssignments();
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

</body>
</html>


