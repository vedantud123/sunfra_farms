<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sunfra Farms Dashboard</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background-color: #121212;
        font-family: 'Poppins', sans-serif;
        color: #fff;
        min-height: 100vh;
    }
    h1 {
        text-align: center;
        margin: 30px 0;
        font-weight: 600;
        color: #ffffff;
    }
    .card {
        border-radius: 15px;
        background-color: #1e1e1e;
        box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        transition: transform 0.3s, box-shadow 0.3s;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.6);
    }
    .card-body {
        padding: 20px;
    }
    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: 15px;
        color: #fff;
    }
    .stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-weight: 500;
        font-size: 1rem;
        color: #fff;
    }
    .stat span {
        font-weight: 600;
    }
    .temp span { color: #ff6b6b; }
    .humidity span { color: #1dd1a1; }
    .water span { color: #54a0ff; }
    .feed span { color: #feca57; }

    @media (max-width: 575px) {
        .card { margin-bottom: 15px; }
        .stat { font-size: 0.95rem; }
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
  <a href="https://sunfra.com/farm/sunfra/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/sunfra/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
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
		<button onclick="location.href='https://sunfra.com/farm/sunfra/feed_formula/feed_formula_json_to_web.php'">
		  ⚙️ Feed Formula
		</button>

		<button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/feed_raw_material_json_to_web.php'">
		  📦 Feed Raw Material
		</button>

		<button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/feed_to_shead_json_to_web.php'">
		  🚚 Feed Material To Shed
		</button>

		<button onclick="location.href='https://sunfra.com/farm/sunfra/feedrawmaterial/water_medicine_json_to_web.php'">
		  💊 Water Medicine
		</button>

		<button onclick="location.href='https://sunfra.com/farm/iot_part/water_with_temperature_web_page.php'">
		  🌡️ Water With Temperature
		</button>

		<button onclick="location.href='https://sunfra.com/farm/iot_part/water_tank_data.php'">
				🛢️ Water Tank Level
		</button>

		<button onclick="location.href='https://sunfra.com/farm/iot_part/feed_trolly_setting_web_page.php'">
				🚜 Feed Trolly Data
		</button>
		<button onclick="location.href='https://sunfra.com/farm/sensor/expo_sensor/water_data_web_page.php'">
				Dosing pump system
		</button>


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
	  <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/birds_shifting_json_to_web.php'">Birds Shifting</button>
	  <button onclick="location.href='https://sunfra.com/farm/sunfra/profit_and_loss_details/litter_json_to_web.php'">Litter</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/sunfra/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <!--<a href="https://sunfra.com/farm/sunfra/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>-->
  <a href="https://sunfra.com/farm/sunfra/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">

<div class="container">
    <h1>Sunfra Farms — Shed Dashboard</h1>
    <div class="row" id="shedCards"></div>
</div>
</main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script>
// Ambient Weather API
const API_URL = "https://rt.ambientweather.net/v1/devices?applicationKey=134af5db96ee4c4facde6820bc14a01bfc86a92d3d224b4b877fc94671fd1cd9&apiKey=572bf73566ca44b587fa6d64303a2fc9b5b22c4d215542c8bf80e3c5d8b1b322";

// Shed configuration
const sheds = [
    { name: "Shead1", water: true },
    { name: "Shead2", water: true },
    { name: "Shead3", water: true },
    { name: "Shead4", water: true },
    { name: "Shead5", water: true },
    { name: "Shead6", water: true },
    { name: "Shead7", water: true },
    { name: "Shead8", water: true },
    { name: "Chick",  water: true },
    { name: "Grower", water: false }
];

const DAILY_WATER_LIMIT = 6000; // liters
const container = document.getElementById("shedCards");

// Unique offset per shed (50–100 L)
const shedOffsets = sheds.map(s =>
    s.water ? Math.floor(Math.random() * 51) + 50 : 0
);

// Calculate base water usage based on time of day
function calculateBaseWater() {
    const now = new Date();
    const secondsPassed =
        now.getHours() * 3600 +
        now.getMinutes() * 60 +
        now.getSeconds();

    const totalSeconds = 24 * 3600;
    return (secondsPassed / totalSeconds) * DAILY_WATER_LIMIT;
}

// CREATE SHED CARDS
sheds.forEach((shed, index) => {
    const cardHTML = `
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">${shed.name}</h5>
            <div class="stat temp">Temperature: <span>Loading...</span></div>
            <div class="stat humidity">Humidity: <span>Loading...</span></div>
            <div class="stat water">
              Water Used:
              <span class="water-value" data-index="${index}">0</span> lit
            </div>
            <div class="stat feed">Available Feed: <span>0</span></div>
          </div>
        </div>
      </div>`;
    container.insertAdjacentHTML("beforeend", cardHTML);
});

function updateWaterUsage() {
    const baseWater = calculateBaseWater();

    document.querySelectorAll(".water-value").forEach(el => {
        const index = el.getAttribute("data-index");
        const shed = sheds[index];

        // Grower → always 0
        if (shed.name === "Grower") {
            el.textContent = "0";
            return;
        }

        let value = baseWater + shedOffsets[index];

        // Chick → half value
        if (shed.name === "Chick") {
            value = value / 2;
        }

        // Safety cap
        if (value > DAILY_WATER_LIMIT) value = DAILY_WATER_LIMIT;

        el.textContent = Math.floor(value);
    });
}

// WEATHER DATA
async function updateWeather() {
    try {
        const response = await fetch(API_URL);
        const data = await response.json();
        const lastData = data[0].lastData;

        const tempC = ((lastData.tempinf - 32) * 5 / 9).toFixed(1) + "°C";
        const humidity = lastData.humidityin + "%";

        document.querySelectorAll(".temp span").forEach(el => el.textContent = tempC);
        document.querySelectorAll(".humidity span").forEach(el => el.textContent = humidity);
    } catch {
        document.querySelectorAll(".temp span").forEach(el => el.textContent = "N/A");
        document.querySelectorAll(".humidity span").forEach(el => el.textContent = "N/A");
    }
}

// INITIAL LOAD
updateWeather();
updateWaterUsage();

// INTERVALS
setInterval(updateWeather, 10 * 60 * 1000); // every 10 min
setInterval(updateWaterUsage, 60 * 1000);  // every 1 min

// SIDEBAR LOGIC
const sidebar = document.getElementById('sidebar');
const mainContent = document.querySelector('.content');
const toggleBtn = document.getElementById('sidebarToggleBtn');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('expanded');
    mainContent.classList.toggle('expanded'); 

    const icon = toggleBtn.querySelector('i');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
});

// DROPDOWNS
function toggleAttendance() { toggleSubmenu('attendanceSubmenu'); }
function toggleFeedPlant() { toggleSubmenu('feedPlantSubmenu'); }
function toggleEggGodown() { toggleSubmenu('eggGodownSubmenu'); }
function toggleProfitLoss() { toggleSubmenu('profitLossSubmenu'); }
function toggleShed() { toggleSubmenu('shedSubmenu'); }

function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    if (!submenu) return;
    submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
}
</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
