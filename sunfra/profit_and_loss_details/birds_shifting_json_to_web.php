<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$username  = $_SESSION['username'] ?? '';
$client_id = $_SESSION['client_id'] ?? 0;

if ($client_id <= 0) {
    die("Invalid client ID.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Birds Shifting List</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-...omitted..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
body {
    background-color: #ADD8E6;
    margin: 0;
    font-family: Arial, sans-serif;
}

thead th {
    background: linear-gradient(90deg, #06b6d4, #3b82f6);
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Alternate row colors */
tbody tr:nth-child(even) {
    background-color: #ffffff;
}
tbody tr:nth-child(odd) {
    background-color: #f0f8ff;
}

/* Hover effect */
tbody tr:hover {
    background-color: #cce7ff;
    transition: background-color 0.3s;
}

/* Sidebar styling */
.sidebar {
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
    border-right: 2px solid #ffffff; /* vertical line */
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
}

/* Main content area */
.main-content {
    margin-left: 70px;
    transition: margin-left 0.3s;
}

.main-content.collapsed {
    margin-left: 50px;
}

.content {
    margin-left: 70px;
    transition: margin-left 0.3s ease;
    padding: 20px;
}

.sidebar.expanded ~ .content,
.content.expanded {
    margin-left: 250px;
}

/* ---------------- MOBILE RESPONSIVE ---------------- */
@media (max-width: 768px) {
    /* Keep sidebar fixed on the left */
    .sidebar {
        width: 60px;
        border-right: 2px solid #ffffff;
    }

    .sidebar.expanded {
        width: 200px;
    }

    .label {
        display: none;
    }

    .sidebar.expanded .label {
        display: inline;
    }

    .main-content,
    .content,
    .content.expanded {
        margin-left: 60px; /* adjust margin for mobile */
    }

    /* Table scrollable on small screens */
    table {
        width: 100%;
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    thead th,
    tbody td {
        padding: 10px;
        font-size: 14px;
    }
}

</style>
</head>
<body class="font-sans">
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
  <a href="https://sunfra.com/farm/sunfra/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">

<div class="container mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Birds Shifting List</h1>
        <button id="addNewBtn" class="flex items-center bg-cyan-500 hover:bg-cyan-600 text-white px-5 py-2 rounded-lg shadow transition">
			<i class="fas fa-plus mr-2"></i> Add New
		</button>
    </div>
	
	<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
		<div class="bg-white rounded-xl w-96 p-6 relative shadow-lg">
			<button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">
				<i class="fas fa-times text-lg"></i>
			</button>
			<h2 class="text-2xl font-bold mb-4 text-gray-800">Add Birds Shifting</h2>
			<form id="birdsForm" class="space-y-4">
				<div>
					<label for="sheadName" class="block text-gray-700 font-medium mb-1">Shead Name</label>
					<select id="sheadName" name="shead_name" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
						<option value="">Select Shead</option>
					</select>
				</div>
				<div>
					<label for="labourCost" class="block text-gray-700 font-medium mb-1">Labour Cost</label>
					<input type="number" step="0.01" id="labourCost" name="labour_cost" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
				</div>
				<div>
					<label for="foodCost" class="block text-gray-700 font-medium mb-1">Food Cost</label>
					<input type="number" step="0.01" id="foodCost" name="food_cost" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
				</div>

				<button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-600 text-white py-2 rounded-lg font-semibold shadow transition">
					Save
				</button>
			</form>
			<p id="formMsg" class="mt-2 text-center text-sm"></p>
		</div>
	</div>
	
    <!-- Table -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="p-4 overflow-x-auto">
            <table id="birdsTable" class="min-w-full divide-y divide-gray-200">
                <thead>
					<tr>
						<th class="px-6 py-3 rounded-tl-lg">ID</th>
						<th class="px-6 py-3">Shead Name</th>
						<th class="px-6 py-3">Labour Cost</th>
						<th class="px-6 py-3">Food Cost</th> <!-- NEW -->
						<th class="px-6 py-3 rounded-tr-lg">Date</th>
					</tr>
				</thead>
                <tbody class="text-gray-700">
                    <!-- Data dynamically injected here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script>
const clientId = <?php echo $client_id; ?>;
const API_URL = `https://sunfra.com/farm/sunfra/profit_and_loss_details/birds_shifting_json.php?client_id=${clientId}`;

async function fetchBirdsData() {
    try {
        const response = await fetch(API_URL);
        const result = await response.json();
        const tbody = document.querySelector("#birdsTable tbody");
        tbody.innerHTML = "";

        if (result.status === "success") {
            result.data.forEach(item => {
                const row = document.createElement("tr");
                row.innerHTML = `
					<td class="px-6 py-4 font-semibold text-center">${item.id}</td>
					<td class="px-6 py-4">${item.shead_name}</td>
					<td class="px-6 py-4">${item.labour_cost}</td>
					<td class="px-6 py-4">${item.food_cost}</td> <!-- NEW -->
					<td class="px-6 py-4 text-center">${item.date}</td>
				`;
                tbody.appendChild(row);
            });

            if (!$.fn.DataTable.isDataTable("#birdsTable")) {
                $('#birdsTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [5, 10, 20, 50],
                    responsive: true,
                    ordering: true,
                    columnDefs: [
						{ className: "text-center", targets: [0,3,4] }
					]
                });
            }
        } else {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-gray-500">${result.message || 'No data found.'}</td></tr>`;
        }
    } catch (error) {
        console.error("Error fetching data:", error);
        const tbody = document.querySelector("#birdsTable tbody");
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-red-500">Error loading data.</td></tr>`;
    }
}

fetchBirdsData();

const addBtn = document.getElementById('addNewBtn');
const modal = document.getElementById('addModal');
const closeModal = document.getElementById('closeModal');
const sheadSelect = document.getElementById('sheadName');
const form = document.getElementById('birdsForm');
const formMsg = document.getElementById('formMsg');

// Open modal
addBtn.addEventListener('click', async () => {
    modal.classList.remove('hidden');
    formMsg.textContent = '';

    // Fetch Shead options
    try {
        const response = await fetch(`https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=${clientId}`);
        const sheads = await response.json();

        // Clear existing options except default
        sheadSelect.innerHTML = `<option value="">Select Shead</option>`;
        sheads.forEach(item => {
            const option = document.createElement('option');
            option.value = item.shead_name;
            option.textContent = item.shead_name;
            sheadSelect.appendChild(option);
        });
    } catch (error) {
        console.error("Error loading sheads:", error);
        sheadSelect.innerHTML = `<option value="">Error loading sheads</option>`;
    }
});

// Close modal
closeModal.addEventListener('click', () => {
    modal.classList.add('hidden');
});

// Form submit
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    formMsg.textContent = 'Saving...';

    const formData = new FormData(form);
    formData.append('client_id', clientId);

    try {
        const response = await fetch('https://sunfra.com/farm/sunfra/profit_and_loss_details/birds_shifting_save.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            formMsg.className = 'mt-2 text-center text-green-600 font-semibold';
            formMsg.textContent = 'Saved successfully!';
            form.reset();
            modal.classList.add('hidden');
            fetchBirdsData(); // Refresh table
        } else {
            formMsg.className = 'mt-2 text-center text-red-600 font-semibold';
            formMsg.textContent = result.message || 'Error saving data.';
        }
    } catch (error) {
        console.error("Error saving:", error);
        formMsg.className = 'mt-2 text-center text-red-600 font-semibold';
        formMsg.textContent = 'Error saving data.';
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

</body>
</html>
