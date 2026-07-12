<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Litter Costing Details</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<style>
/* ==============================
   🌤 GLOBAL STYLES
============================== */
body {
  background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
  color: #1f2937;
  overflow-x: hidden;
}

/* ==============================
   🧊 GLASS CONTAINER
============================== */
.glass {
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  border: 1px solid rgba(200, 200, 200, 0.3);
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.glass:hover {
  transform: translateY(-3px);
}

/* ==============================
   📊 TABLE STYLING
============================== */
.table-hover tbody tr:hover {
  background-color: rgba(230, 247, 255, 0.8);
  transition: background 0.3s ease;
}

th {
  color: #1e3a8a;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 12px;
}

td {
  color: #1f2937;
  padding: 12px;
  vertical-align: middle;
}

.table-title {
  color: #0f172a;
}

/* ==============================
   ➕ ADD BUTTON
============================== */
.add-btn {
  background: linear-gradient(135deg, #0077b6, #00b4d8);
  color: white;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 119, 182, 0.4);
  transition: transform 0.2s ease, background 0.3s;
}

.add-btn:hover {
  background: linear-gradient(135deg, #0096c7, #48cae4);
  transform: scale(1.05);
}

/* ==============================
   🪟 MODAL (POPUP FORM)
============================== */
.modal-bg {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.55);
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeInBg 0.3s ease;
}

.modal {
  background: white;
  border-radius: 16px;
  padding: 30px;
  width: 420px;
  max-width: 90%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  animation: slideIn 0.4s ease-in-out;
}

.modal h2 {
  color: #016795;
  font-size: 1.5rem;
  margin-bottom: 15px;
  font-weight: 600;
}

.modal label {
  font-size: 14px;
  color: #374151;
  font-weight: 500;
}

.modal input,
.modal select {
  width: 100%;
  padding: 10px 12px;
  margin-top: 5px;
  margin-bottom: 15px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  transition: border 0.3s;
}

.modal input:focus,
.modal select:focus {
  border-color: #00b4d8;
  box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.25);
}

.modal button {
  background: linear-gradient(135deg, #0077b6, #00b4d8);
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal button:hover {
  background: linear-gradient(135deg, #0096c7, #48cae4);
  transform: scale(1.05);
}

@keyframes fadeInBg {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ==============================
   📚 SIDEBAR STYLING
============================== */
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
  z-index: 1001;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.15);
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
  font-size: 18px;
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

/* Sidebar toggle button */
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

/* Attendance submenu */
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

/* ==============================
   🖥️ MAIN CONTENT AREA
============================== */
.main-content {
  margin-left: 70px; /* default collapsed sidebar width */
  padding: 30px 60px;
  transition: margin-left 0.3s, padding 0.3s;
  width: calc(100% - 70px);
}

.sidebar.expanded ~ .main-content {
  margin-left: 250px;
  width: calc(100% - 250px);
}

.main-content.collapsed {
  margin-left: 70px;
  width: calc(100% - 70px);
}

.main-content.collapsed {
  margin-left: 70px;
}

/* Content margin adjustment */
.content {
  margin-left: 70px;
  transition: margin-left 0.3s ease;
}

.sidebar.expanded ~ .content {
  margin-left: 250px;
}

.content.expanded {
  margin-left: 250px;
}

/* ==============================
   ✨ ANIMATIONS
============================== */
.fade-in {
  animation: fadeIn 1s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body class="bg-gradient-to-br from-blue-200 to-blue-100 min-h-screen">
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
      <button onclick="location.href='https://sunfra.com/farm/sunfra/attendance/new_labour_master.php'">📝 Assigned Master</button>
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
      <button onclick="location.href='https://sunfra.com/farm/sunfra/egg_godown/egg_weight.php'">⚖️ Egg Weight</button>
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
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/profit_and_loss_daily.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/dashboard.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/sunfra/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/sunfra/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">

<!-- Header -->
<div class="w-full flex justify-between items-center mb-8">
  <h1 class="text-3xl font-bold tracking-wide table-title">🐔 Litter Costing Details</h1>
  <button class="add-btn px-5 py-2 rounded-lg shadow-lg" onclick="openModal()">+ Add New</button>
</div>

<!-- Table Container -->
<div class="glass w-full max-w-7xl mx-auto p-8 shadow-2xl fade-in">
  <table class="min-w-full text-base table-auto table-hover rounded-lg overflow-hidden">
    <thead class="border-b border-gray-300 bg-blue-100">
      <tr>
        <th class="py-3 text-left px-3">#</th>
        <th class="py-3 text-left px-3">Shead Name</th>
        <th class="py-3 text-left px-3">Number of Vehicles</th>
        <th class="py-3 text-left px-3">Litter Cost</th>
        <th class="py-3 text-left px-3">Date</th>
      </tr>
    </thead>
    <tbody id="data-table" class="divide-y divide-gray-200">
      <tr><td colspan="5" class="py-4 text-center text-gray-600">Loading data...</td></tr>
    </tbody>
  </table>
</div>

<!-- Modal Popup -->
<div id="modal-bg" class="modal-bg flex">
  <div class="modal">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">Add New Litter Record</h2>

    <form id="addForm" class="space-y-4">
      <div>
        <label class="block text-gray-700 mb-1">Shead Name</label>
        <select id="sheadName" class="w-full p-2 border rounded-md">
          <option value="">Select Shead</option>
        </select>
      </div>

      <div>
        <label class="block text-gray-700 mb-1">Number of Vehicles</label>
        <input type="number" id="numVehicle" class="w-full p-2 border rounded-md" required>
      </div>

      <div>
        <label class="block text-gray-700 mb-1">Litter Cost (₹)</label>
        <input type="number" id="litterCost" class="w-full p-2 border rounded-md" required>
      </div>

      <div class="flex justify-end mt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 mr-2 bg-gray-400 text-white rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save</button>
      </div>
    </form>
  </div>
</div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
const clientId = 1; // can be dynamic if needed

async function loadData() {
  const url = `https://sunfra.com/farm/sunfra/profit_and_loss_details/litter_json.php?client_id=${clientId}`;
  try {
    const res = await axios.get(url);
    const tableBody = document.getElementById('data-table');
    tableBody.innerHTML = '';

    if (res.data.status === 'success' && res.data.data.length > 0) {
      res.data.data.forEach((row, i) => {
        const tr = document.createElement('tr');
        tr.classList.add('hover:bg-blue-50');
        tr.innerHTML = `
          <td class="py-3 px-3">${i + 1}</td>
          <td class="py-3 px-3 font-medium">${row.shead_name}</td>
          <td class="py-3 px-3">${row.number_of_vehicle}</td>
          <td class="py-3 px-3 font-semibold text-green-700">₹${row.litter_cost}</td>
          <td class="py-3 px-3">${new Date(row.date).toLocaleDateString()}</td>
        `;
        tableBody.appendChild(tr);
      });
    } else {
      tableBody.innerHTML = `<tr><td colspan="5" class="py-4 text-center text-gray-500">No records found.</td></tr>`;
    }
  } catch (err) {
    console.error(err);
    document.getElementById('data-table').innerHTML = `<tr><td colspan="5" class="py-4 text-center text-red-500">Error loading data</td></tr>`;
  }
}

function openModal() {
  document.getElementById('modal-bg').style.display = 'flex';
  loadSheadNames();
}

function closeModal() {
  document.getElementById('modal-bg').style.display = 'none';
}

async function loadSheadNames() {
  const sheadSelect = document.getElementById('sheadName');
  sheadSelect.innerHTML = '<option value="">Select Shead</option>';
  try {
    const res = await axios.get(`https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=${clientId}`);
    res.data.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.shead_name;
      opt.textContent = item.shead_name;
      sheadSelect.appendChild(opt);
    });
  } catch (err) {
    console.error("Error loading shead names:", err);
  }
}

document.getElementById('addForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const shead_name = document.getElementById('sheadName').value;
  const number_of_vehicle = document.getElementById('numVehicle').value;
  const litter_cost = document.getElementById('litterCost').value;

  if (!shead_name || !number_of_vehicle || !litter_cost) {
    alert("Please fill all fields!");
    return;
  }

  try {
    const formData = new FormData();
    formData.append('shead_name', shead_name);
    formData.append('number_of_vehicle', number_of_vehicle);
    formData.append('litter_cost', litter_cost);
    formData.append('client_id', clientId);

    const res = await axios.post('https://sunfra.com/farm/sunfra/profit_and_loss_details/litter_save.php', formData);
    alert(res.data.message || "Record added successfully!");
    closeModal();
    loadData();
  } catch (err) {
    console.error("Error saving record:", err);
    alert("Error saving data!");
  }
});

loadData();

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
