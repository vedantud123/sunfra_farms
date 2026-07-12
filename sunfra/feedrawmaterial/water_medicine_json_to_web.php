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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Water Medicine</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
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
<body class="text-gray-800" style="background-color: #ADD8E6;">
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
  <div class="p-6">
    <div class="flex flex-wrap items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-blue-800 mb-2">Water Medicine</h1>
      <div class="flex flex-wrap items-center gap-2">
        <input type="text" id="searchName" placeholder="Search by Name" class="px-4 py-2 border border-gray-300 rounded-md" />
        <select id="shedFilter" class="px-4 py-2 border border-gray-300 rounded-md">
          <option value="">All Sheds</option>
        </select>
        <input type="date" id="filterDate" class="px-4 py-2 border border-gray-300 rounded-md" />
        <button onclick="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Entry</button>
      </div>
    </div>

    <table class="w-full bg-white rounded shadow-md overflow-hidden">
      <thead class="bg-blue-700 text-white">
        <tr>
          <th class="px-4 py-2 text-left border border-gray-300">ID</th>
          <th class="px-4 py-2 text-left border border-gray-300">Shed</th>
          <th class="px-4 py-2 text-left border border-gray-300">Material</th>
          <th class="px-4 py-2 text-left border border-gray-300">Quantity</th>
          <th class="px-4 py-2 text-left border border-gray-300">Description</th>
          <th class="px-4 py-2 text-left border border-gray-300">Timestamp</th>
          <th class="px-4 py-2 text-left border border-gray-300">Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div id="entryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded-md w-full max-w-md">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Sanitization Entry</h2>
        <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>
      <form id="entryForm">
        <input type="hidden" name="id" id="edit_id" />
		<input type="hidden" name="client_id" id="clientId" value="<?php echo $client_id; ?>">
        <div class="mb-4">
          <label class="block mb-1 font-medium">Shed Name</label>
          <select id="place" name="place" class="w-full border px-3 py-2 rounded" required></select>
        </div>
        <div class="mb-4">
          <label class="block mb-1 font-medium">Material Name</label>
          <select id="material" name="name" class="w-full border px-3 py-2 rounded" required></select>
        </div>
        <div class="mb-4">
          <label class="block mb-1 font-medium">Quantity (ml)</label>
          <input type="number" id="quantity" name="quantity" step="any" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-4">
          <label class="block mb-1 font-medium">Description</label>
          <input type="text" id="description" name="description" class="w-full border px-3 py-2 rounded" />
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Save Entry</button>
      </form>
    </div>
  </div>
  </main>
  </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
const clientId = <?php echo json_encode($client_id); ?>;
let cachedData = [];

document.getElementById("searchName").addEventListener("input", applyFilter);
document.getElementById("shedFilter").addEventListener("change", applyFilter);
document.getElementById("filterDate").addEventListener("change", applyFilter);

function applyFilter() {
  const nameFilter = document.getElementById("searchName").value.trim().toLowerCase();
  const shedFilter = document.getElementById("shedFilter").value.trim().toLowerCase();
  const dateFilter = document.getElementById("filterDate").value;
  filterAndRenderSanitizationData({ nameFilter, shedFilter, dateFilter });
}

function filterAndRenderSanitizationData(filters = {}) {
  const { nameFilter, shedFilter, dateFilter } = filters;
  const tbody = document.querySelector("tbody");
  tbody.innerHTML = "";

  const filteredData = cachedData.filter(entry => {
    const name = (entry.name || "").toLowerCase().trim();
    const place = (entry.place || "").toLowerCase().trim();
    const timestamp = entry.timestamp || "";
    return (!nameFilter || name.includes(nameFilter)) &&
           (!shedFilter || place === shedFilter) &&
           (!dateFilter || timestamp.startsWith(dateFilter));
  });

  filteredData.forEach(entry => {
    const row = document.createElement("tr");
    row.innerHTML = `
	  <td class="px-4 py-2 border border-gray-200">${entry.id}</td>
	  <td class="px-4 py-2 border border-gray-200">${entry.place}</td>
	  <td class="px-4 py-2 border border-gray-200">${entry.name}</td>
	  <td class="px-4 py-2 border border-gray-200">${entry.quantity}</td>
	  <td class="px-4 py-2 border border-gray-200">${entry.description || ""}</td>
	  <td class="px-4 py-2 border border-gray-200">${entry.timestamp}</td>
	  <td class="px-4 py-2 border border-gray-200">
		<button onclick="editEntry('${encodeURIComponent(JSON.stringify(entry))}')" class="text-blue-600 hover:underline">Edit</button>
	  </td>
	`;
    tbody.appendChild(row);
  });
}

async function loadSanitizationData(filters = {}) {
  try {
    const response = await fetch(`https://sunfra.com/farm/sunfra/feedrawmaterial/water_medicine_json.php?client_id=${clientId}`);
	const rawData = await response.json();
	cachedData = rawData[clientId] || [];
    filterAndRenderSanitizationData(filters);
  } catch (error) {
    console.error("❌ Failed to fetch data:", error);
  }
}

function openModal() {
  document.getElementById("entryModal").classList.remove("hidden");
  document.getElementById("entryForm").reset();
  document.getElementById("edit_id").value = "";
}

function closeModal() {
  document.getElementById("entryModal").classList.add("hidden");
}

function editEntry(entryJSON) {
  const entry = JSON.parse(decodeURIComponent(entryJSON));
  document.getElementById("edit_id").value = entry.id;
  document.getElementById("place").value = entry.place;
  document.getElementById("material").value = entry.name;
  document.getElementById("quantity").value = entry.quantity;
  document.getElementById("description").value = entry.description;
  document.getElementById("entryModal").classList.remove("hidden");
}

document.getElementById("entryForm").addEventListener("submit", async function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  try {
    const response = await fetch("https://sunfra.com/farm/sunfra/feedrawmaterial/water_medicine_save.php", {
      method: "POST",
      body: formData
    });
    const result = await response.json();
    alert(result.message || "Entry saved.");
	closeModal();
	await loadSanitizationData();
	applyFilter(); 
  } catch (err) {
    console.error("❌ Save error:", err);
  }
});

document.addEventListener("DOMContentLoaded", async () => {
  const shedSelect = document.getElementById("place");
  const materialSelect = document.getElementById("material");
  const shedFilter = document.getElementById("shedFilter");

  try {
    const shedRes = await fetch(`https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=${clientId}`);
    const shedData = await shedRes.json();
    shedSelect.innerHTML = `<option value="">-- Select Shed --</option>`;
    shedFilter.innerHTML = `<option value="">All Sheds</option>`;
    shedData.forEach(item => {
      const name = item.shead_name.trim();
      shedSelect.innerHTML += `<option value="${name}">${name}</option>`;
      shedFilter.innerHTML += `<option value="${name}">${name}</option>`;
    });
  } catch (err) {
    console.error("Shed load error:", err);
  }

  try {
	  const matRes = await fetch(`https://sunfra.com/farm/sunfra/feedrawmaterial/feed_raw_material_json.php?client_id=${clientId}`);
	  const matData = await matRes.json();
	  const materials = matData[clientId] || [];

	  const waterMedicines = materials
		.filter(item => item.type === "Water Medicine")
		.sort((a, b) => a.name.localeCompare(b.name)); // <-- ASC sort by name

	  materialSelect.innerHTML = `<option value="">-- Select Material --</option>`;
	  waterMedicines.forEach(item => {
		materialSelect.innerHTML += `<option value="${item.name}">${item.name}</option>`;
	  });
	} catch (err) {
	  console.error("Material load error:", err);
	}

  const today = new Date().toISOString().split("T")[0];
  document.getElementById("filterDate").value = today;
  await loadSanitizationData();
  applyFilter();
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
