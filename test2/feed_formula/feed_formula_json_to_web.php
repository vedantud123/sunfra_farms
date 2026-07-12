<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
$client_id = $_SESSION['client_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Feed Formula Comparison</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    th, td { white-space: nowrap; }.sidebar {
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
<body class="bg-[#ADD8E6] text-gray-800 min-h-screen p-6 font-sans">

<div class="max-w-7xl mx-auto">
  <div class="flex justify-between items-center mb-4">
    <div></div>
    <button onclick="handleUpdateClick()" class="bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white font-semibold px-5 py-2 rounded-xl shadow-md transition duration-300">
      🔄 Update
    </button>
  </div>

  <h1 class="text-4xl font-bold text-center mb-12 text-blue-700 drop-shadow">🐥 Feed Formula Overview</h1>

  <div id="feed-formula-table" class="mb-16"></div>
  <div id="feed-medicine-table"></div>
</div>

<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-[#ADD8E6] p-6 rounded-lg shadow-2xl max-w-4xl w-full h-[80vh] overflow-auto relative">

    <button type="button" id="addNewMaterialBtn" class="mb-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
      + Add New Material
    </button>

    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold text-blue-700">🛠 Update Feed Data</h2>
      <button onclick="closeModal()" class="text-gray-500 hover:text-red-500 text-xl font-bold">&times;</button>
    </div>

    <form id="updateForm">
      <div id="modalContent" class="space-y-6"></div>
      <div class="mt-6 flex justify-end">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md">
          💾 Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Add New Material Modal -->
<div id="newMaterialModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden">
  <div class="bg-white p-6 rounded-lg shadow-2xl max-w-md w-full relative">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold text-blue-700">Add New Material</h3>
      <button onclick="closeNewMaterialModal()" class="text-gray-500 hover:text-red-500 text-xl font-bold">&times;</button>
    </div>
    <form id="newMaterialForm">
      <div class="mb-4">
        <label class="block font-medium text-gray-700 mb-1" for="newMaterialType">Type</label>
        <select id="newMaterialType" name="type" required class="w-full px-3 py-2 border border-gray-400 rounded-md">
          <option value="" disabled selected>Select Type</option>
          <option value="Feed_Formula">Feed Formula</option>
          <option value="Feed_Medicine">Feed Medicine</option>
        </select>
      </div>
      <div class="mb-4">
        <label class="block font-medium text-gray-700 mb-1" for="newMaterialName">Material Name</label>
        <select id="newMaterialName" name="material" required class="w-full px-3 py-2 border border-gray-400 rounded-md">
		  <option value="" disabled selected>Select Material</option>
		  <!-- Options will be dynamically populated -->
		</select>
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded font-semibold">
          Add Material
        </button>
      </div>
    </form>
  </div>
</div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script>
const clientId = <?= json_encode($client_id) ?>;
let originalData = [];
window.handleUpdateClick = async function() {
  try {
    const [data, weeks] = await Promise.all([fetchMainData(), fetchRunningWeeks()]);
    console.log("Fetched data:", data);
    console.log("Fetched weeks:", weeks);
    openModal(data, weeks);
  } catch (error) {
    console.error("Error fetching data or weeks:", error);
    alert("Failed to load data for editing.");
  }
};

async function fetchMainData() {
  const res = await fetch(`https://sunfra.com/farm/test2/feed_formula/feed_formula_json.php?client_id=${clientId}`);
  const json = await res.json();
  // Use the right key, usually clientId
  return json[clientId] || [];
}


async function fetchRunningWeeks() {
  const res = await fetch(`https://sunfra.com/farm/test2/batch/batch_week_json.php?client_id=${clientId}`);
  const json = await res.json();
  return json[clientId] || [];
}

function extractAllMaterials(data, type) {
  if (!Array.isArray(data)) {
    console.error("extractAllMaterials was called with data:", data);
    return [];
  }
  const materials = new Set();
  data.forEach(shead => {
    const sheadKey = Object.keys(shead)[0];
    const items = shead[sheadKey][type] || {};
    Object.keys(items).forEach(mat => materials.add(mat));
  });
  return Array.from(materials).sort();
}


function generateTable(weeks, data, type, title, containerId) {
  const materials = extractAllMaterials(data, type);
  const sheads = data.map(shead => Object.keys(shead)[0]);

  let table = `<h2 class="text-3xl font-semibold text-blue-600 mb-6">${title}</h2>`;
  table += `
    <div class="overflow-auto rounded-lg shadow-xl border border-gray-300 bg-white">
      <table class="min-w-full text-sm text-gray-700 table-fixed">
        <thead class="bg-blue-100 sticky top-0 z-10">
          <tr class="bg-blue-200">
            <th class="p-2 text-left font-semibold border border-gray-300 text-blue-900">Running Week</th>`;
  weeks.forEach(wk => {
    const val = wk.Running_week ?? "N/A";
    table += `<td class="p-2 text-center font-semibold text-yellow-700 border border-gray-300">${val}</td>`;
  });
  table += `</tr>
        <tr>
          <th class="p-3 text-left font-semibold border border-gray-300 bg-blue-100">Material</th>`;
  sheads.forEach(shead => {
    table += `<th class="p-3 text-center font-semibold border border-gray-300 bg-blue-100">${shead.replace('_', ' ').toUpperCase()}</th>`;
  });
  table += `</tr>
        </thead><tbody>`;

  materials.forEach(material => {
    table += `<tr class="hover:bg-blue-50 transition-all">
      <td class="p-3 border border-gray-300 font-medium text-blue-800 bg-white">${material}</td>`;
    data.forEach(shead => {
      const sheadKey = Object.keys(shead)[0];
      const items = shead[sheadKey][type] || {};
      const value = items[material] ?? "-";
      table += `<td class="p-3 text-center border border-gray-300 bold bg-white">${value}</td>`;
    });
    table += `</tr>`;
  });

  table += `</tbody></table></div>`;
  document.getElementById(containerId).innerHTML = table;
}

async function renderTables() {
  const [data, weeks] = await Promise.all([
    fetchMainData(),
    fetchRunningWeeks()
  ]);
  generateTable(weeks, data, "Feed_Formula", "Feed Formula", "feed-formula-table");
  generateTable(weeks, data, "Feed_Medicine", "Feed Medicine", "feed-medicine-table");
}

function openModal(data, weeks) {
  originalData = data;
  const container = document.getElementById("modalContent");
  container.innerHTML = "";

  const sheads = data.map(sheadObj => Object.keys(sheadObj)[0]);

  let feedFormulaMaterials = new Set();
  let feedMedicineMaterials = new Set();

  data.forEach(sheadObj => {
    const sheadKey = Object.keys(sheadObj)[0];
    const feedFormula = sheadObj[sheadKey]["Feed_Formula"] || {};
    const feedMedicine = sheadObj[sheadKey]["Feed_Medicine"] || {};

    Object.keys(feedFormula).forEach(m => feedFormulaMaterials.add(m));
    Object.keys(feedMedicine).forEach(m => feedMedicineMaterials.add(m));
  });

  feedFormulaMaterials = Array.from(feedFormulaMaterials).sort();
  feedMedicineMaterials = Array.from(feedMedicineMaterials).sort();

  function generateEditableTable(title, typeMaterials, type) {
    let tableHtml = `
  <h4 class="text-xl font-semibold text-blue-700 mb-3">${title}</h4>
  <div class="overflow-auto border border-gray-300 rounded bg-white shadow-md mb-8">
    <table class="min-w-full text-gray-800 text-sm table-fixed">
      <thead class="bg-blue-100 sticky top-0 z-10">
        <tr>
          <th class="border border-gray-300 p-2 text-left font-semibold">Running Week</th>`;

    sheads.forEach((shead, idx) => {
      let runningWeek = "N/A";
      if (weeks && weeks[idx] && weeks[idx].Running_week !== undefined) {
        runningWeek = weeks[idx].Running_week;
      }
      tableHtml += `<th class="border border-gray-300 p-2 text-center font-semibold text-yellow-700">${runningWeek}</th>`;
    });

    tableHtml += `</tr><tr>
          <th class="border border-gray-300 p-2 text-left font-semibold">Material</th>`;

    sheads.forEach(shead => {
      tableHtml += `<th class="border border-gray-300 p-2 text-center font-semibold">${shead.toUpperCase()}</th>`;
    });

    tableHtml += `</tr></thead><tbody>`;

    typeMaterials.forEach(material => {
      tableHtml += `<tr class="hover:bg-blue-50 transition-all">
        <td class="border border-gray-300 p-2 font-medium">${material}</td>`;

      sheads.forEach(shead => {
        const currSheadData = data.find(d => Object.keys(d)[0] === shead);
        let val = "";
        if (currSheadData && currSheadData[shead][type]) {
          val = currSheadData[shead][type][material];
          if (val === undefined || val === null) val = "";
        }
        const inputId = `${shead}_${type}_${material}`.replace(/\s+/g, "_");
        tableHtml += `<td class="border border-gray-300 p-1 text-center">
          <input type="number" step="0.01" id="${inputId}" data-shead="${shead}" data-type="${type}" data-material="${material}" value="${val}" 
            class="w-full px-1 py-1 text-center border border-gray-300 rounded focus:outline-none focus:ring 
            ${type === 'Feed_Formula' ? 'focus:ring-blue-300' : 'focus:ring-purple-300'}" />
        </td>`;
      });

      tableHtml += `</tr>`;
    });

    tableHtml += `</tbody></table></div>`;

    return tableHtml;
  }

  container.innerHTML += generateEditableTable("Feed Formula", feedFormulaMaterials, "Feed_Formula");
  container.innerHTML += generateEditableTable("Feed Medicine", feedMedicineMaterials, "Feed_Medicine");
  document.getElementById("updateModal").classList.remove("hidden");
}

function closeModal() {
  document.getElementById("updateModal").classList.add("hidden");
}

function closeNewMaterialModal() {
  document.getElementById("newMaterialModal").classList.add("hidden");
  document.getElementById("newMaterialForm").reset();
}

document.getElementById("addNewMaterialBtn").addEventListener("click", async () => {
  await populateMaterialSelect();  // fetch and populate datalist options just before showing modal
  document.getElementById("newMaterialModal").classList.remove("hidden");
});

document.getElementById("newMaterialForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const type = document.getElementById("newMaterialType").value;
  const material = document.getElementById("newMaterialName").value.trim();

  if (!type || !material) {
    alert("Please select type and enter material name.");
    return;
  }

  try {
    const apiMaterials = await fetchAvailableRawMaterials();

    const isInApi = apiMaterials.includes(material);
    if (!isInApi) {
      alert(`❌ "${material}" is not a valid material from raw materials list.`);
      return;
    }

    const currentData = originalData; // Already available globally
    const allCurrentMaterials = new Set();

    currentData.forEach(sheadObj => {
      const sheadKey = Object.keys(sheadObj)[0];
      const items = sheadObj[sheadKey][type] || {};
      Object.keys(items).forEach(mat => allCurrentMaterials.add(mat.trim()));
    });

    if (allCurrentMaterials.has(material)) {
      alert(`⚠️ "${material}" already exists in the current feed data.`);
      return;
    }

    const res = await fetch("https://sunfra.com/farm/test2/feed_formula/feed_formula_new_material.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ client_id: clientId, type, material }),
    });

    const result = await res.json();

    if (result.success || (result.message && result.message.toLowerCase().includes("success"))) {
      alert(result.message || "Material added successfully!");
      closeNewMaterialModal();

      const [data, weeks] = await Promise.all([fetchMainData(), fetchRunningWeeks()]);
      openModal(data, weeks);
    } else {
      alert(result.message || "Failed to add material.");
    }
  } catch (error) {
    alert("❌ Error adding material.");
    console.error(error);
  }
});

document.getElementById("updateForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const updated = originalData.map(sheadObj => {
    const sheadKey = Object.keys(sheadObj)[0];
    const original = sheadObj[sheadKey];
    const newFormula = {};
    const newMedicine = {};

    for (let mat in original["Feed_Formula"] || {}) {
      const inputId = `${sheadKey}_Feed_Formula_${mat}`.replace(/\s+/g, "_");
      const input = document.getElementById(inputId);
      if (input) {
        newFormula[mat] = parseFloat(input.value) || 0;
      }
    }

    for (let med in original["Feed_Medicine"] || {}) {
      const inputId = `${sheadKey}_Feed_Medicine_${med}`.replace(/\s+/g, "_");
      const input = document.getElementById(inputId);
      if (input) {
        newMedicine[med] = parseFloat(input.value) || 0;
      }
    }

    return {
      [sheadKey]: {
        "Feed_Formula": newFormula,
        "Feed_Medicine": newMedicine
      }
    };
  });

  try {
    const res = await fetch("https://sunfra.com/farm/test2/feed_formula/feed_formula_update.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ client_id: clientId, data: updated })
    });
    const result = await res.json();

    alert(result.message || "✅ Data updated successfully!");
    closeModal();
    renderTables();
  } catch (err) {
    alert("❌ Error saving data");
    console.error(err);
  }
});
async function fetchAvailableRawMaterials() {
  const res = await fetch(`https://sunfra.com/farm/test2/feedrawmaterial/feed_raw_material_json.php?client_id=${clientId}`);
  const json = await res.json();
  const rawMaterials = json[clientId] || [];

  return rawMaterials
    .filter(item => item.type === "Feed Medicine" || item.type === "Raw Material")
    .map(item => item.name.trim());
}
async function populateMaterialSelect() {
  const materials = await fetchAvailableRawMaterials();

  const select = document.getElementById("newMaterialName");
  if (!select) return;

  select.innerHTML = '<option value="" disabled selected>Select Material</option>'; 

  materials.forEach(mat => {
    const option = document.createElement("option");
    option.value = mat;
    option.textContent = mat;
    select.appendChild(option);
  });
}
fetchAvailableRawMaterials();
populateMaterialSelect();
renderTables();
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
