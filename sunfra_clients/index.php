<?php 
session_start(); 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login/login.php");
    exit;
}

$clientName = $_SESSION['client_name'] ?? 'Yours';
$client_id = $_SESSION['client_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
    }

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
    }

    .sidebar.expanded {
      width: 270px;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      width: 100%;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      transition: background-color 0.2s ease-in-out;
    }

    .sidebar a:hover {
      background-color: #0194c7;
    }

    .sidebar i {
      font-size: 15px;
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
    }

    .content {
      margin-left: 70px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }

    .sidebar.expanded ~ .content {
      margin-left: 220px;
    }.attendance-dropdown {
	  width: 100%;
	  position: relative;
	}

	.attendance-submenu {
	  display: none;
	  flex-direction: column;
	  position: relative;
	  background: #1e293b;
	  width: 100%;
	  padding-left: 40px;
	}

	.attendance-submenu button {
	  background: none;
	  border: none;
	  color: white;
	  text-align: left;
	  padding: 10px;
	  font-size: 14px;
	  cursor: pointer;
	  transition: background 0.2s ease;
	}

	.attendance-submenu button:hover {
	  background-color: #2563EB;
	}

	.sidebar.expanded .attendance-submenu {
	  padding-left: 50px;
	}#dropdown label {
	  display: block;
	  padding: 6px 10px;
	  border-radius: 5px;
	  margin-bottom: 5px;
	  transition: background 0.2s ease;
	  cursor: pointer;
	}

	#dropdown label:hover {
	  background-color: #0194c7;
	}

	#dropdown input[type="checkbox"]:checked + span,
	#dropdown input[type="radio"]:checked + span {
	  font-weight: bold;
	}

	.graph-option input[type="checkbox"] {
	  accent-color: #016795;
	}

	.time-option input[type="radio"] {
	  accent-color: #0194c7;
	}.time-options-row,
	.graph-options-row {
	  display: flex;
	  flex-wrap: wrap;
	  gap: 10px;
	  margin-bottom: 10px;
	}

	.time-options-row label,
	.graph-options-row label {
	  background-color: #e0e0e0;
	  border-radius: 4px;
	  padding: 4px 8px;
	  display: flex;
	  align-items: center;
	  gap: 4px;
	  cursor: pointer;
	  white-space: nowrap;
	}

	.time-options-row label:hover,
	.graph-options-row label:hover {
	  background-color: #d0d0d0;
	}

  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <a class="toggle-btn" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
    <span class="label">Menu</span>
  </a>

  <a href="https://sunfra.com/farm/sunfra_clients/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>
	<div class="attendance-dropdown">
	  <a onclick="toggleAttendance()" class="attendance-toggle">
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
		<span class="label">Shed Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
	<div class="attendance-dropdown">
	  <a onclick="toggleFeedPlant()" class="attendance-toggle">
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
	  <a onclick="toggleEggGodown()" class="attendance-toggle">
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
	  <a onclick="toggleProfitLoss()" class="attendance-toggle">
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
  <!--<a href="https://sunfra.com/farm/sunfra_clients/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>-->
  <a href="https://sunfra.com/farm/sunfra_clients/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>


<div style="position: absolute; top: 20px; right: 20px;">
  <button onclick="toggleDropdown()" id="filterBtn" style="
    background-color: #016795;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
  ">
    📊 Filter Graphs
  </button>

  <div id="dropdown" style="
	  position: absolute;
	  right: 0;
	  top: 42px;
	  background-color: lightgray;
	  border: 1px solid #ccc;
	  padding: 12px;
	  border-radius: 6px;
	  display: none;
	  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
	  z-index: 999;
	  min-width: 700px;
	">
		<div style="margin-bottom: 10px; font-weight: bold; background-color: #016795; color: white; padding: 6px 10px; border-radius: 4px;">Select Format:</div>
		<div class="time-options-row">
		  <label class="time-option"><input type="radio" name="timeRange" value="today" checked> <span>Today</span></label>
		  <label class="time-option"><input type="radio" name="timeRange" value="yesterday"> <span>Yesterday</span></label>
		  <label class="time-option"><input type="radio" name="timeRange" value="weekly"> <span>Weekly</span></label>
		  <label class="time-option"><input type="radio" name="timeRange" value="monthly"> <span>Monthly</span></label>
		  <label class="time-option"><input type="radio" name="timeRange" value="yearly"> <span>Yearly</span></label>
		</div>

		<hr style="margin: 10px 0;">

		<div style="margin-bottom: 10px; font-weight: bold; background-color: #016795; color: white; padding: 6px 10px; border-radius: 4px;">Graph Options:</div>
		<div class="graph-options-row">
		  <label class="graph-option"><input type="checkbox" value="profitandloss" onchange="renderGraphs()"> <span>Profit And Loss</span></label>
		  <label class="graph-option"><input type="checkbox" value="sheadMortality" onchange="renderGraphs()"> <span>Shead Mortality</span></label>
		  <label class="graph-option"><input type="checkbox" value="eggProduction" onchange="renderGraphs()"> <span>Egg Production</span></label>
		  <label class="graph-option"><input type="checkbox" value="openingandclosingbalance" onchange="renderGraphs()"> <span>Opening And Closing Balance</span></label>
		  <label class="graph-option"><input type="checkbox" value="damage" onchange="renderGraphs()"> <span>Damage</span></label>
		  <label class="graph-option"><input type="checkbox" value="productionpercentage" onchange="renderGraphs()"> <span>Production Percentage</span></label>
		  <label class="graph-option"><input type="checkbox" value="feedintake" onchange="renderGraphs()"> <span>Feed Intake</span></label>
		  <label class="graph-option"><input type="checkbox" value="eggWeight" onchange="renderGraphs()"> <span>Egg Weight</span></label>
		  <label class="graph-option"><input type="checkbox" value="eggprice" onchange="renderGraphs()"> <span>Egg Price</span></label>
  		  <label class="graph-option"><input type="checkbox" value="livebirds" onchange="renderGraphs()"> <span>Live Birds</span></label>
		</div>
		
	</div>

</div>

<div id="graphContainer" style="margin: 100px 20px 20px 100px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;"></div>



<script>
	function toggleSidebar() {
		document.getElementById("sidebar").classList.toggle("expanded");
	}function toggleAttendance() {
	  const submenu = document.getElementById('attendanceSubmenu');
	  submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
	}
	function toggleShed() {
	  const shedSubmenu = document.getElementById('shedSubmenu');
	  shedSubmenu.style.display = shedSubmenu.style.display === 'flex' ? 'none' : 'flex';
	}function toggleFeedPlant() {
	  const submenu = document.getElementById('feedPlantSubmenu');
	  submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
	}function toggleEggGodown() {
	  const submenu = document.getElementById('eggGodownSubmenu');
	  submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
	}function toggleProfitLoss() {
	  const submenu = document.getElementById('profitLossSubmenu');
	  submenu.style.display = submenu.style.display === 'flex' ? 'none' : 'flex';
	} function toggleDropdown() {
    const dropdown = document.getElementById('dropdown');
    dropdown.classList.toggle('hidden');
  }
	function toggleDropdown() {
    const dropdown = document.getElementById("dropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
  }

 function renderGraphs() {
  const container = document.getElementById("graphContainer");
  container.innerHTML = '';

  const selectedGraphs = document.querySelectorAll('#dropdown input[type="checkbox"]:checked');
  const selectedTime = document.querySelector('#dropdown input[name="timeRange"]:checked')?.value || 'today';

  selectedGraphs.forEach(cb => {
    const div = document.createElement('div');
    div.style.background = "#fff";
    div.style.border = "1px solid #ccc";
    div.style.borderRadius = "8px";
    div.style.padding = "10px";

	const label = cb.closest('label').innerText.trim();
	const title = `${label} (${selectedTime.charAt(0).toUpperCase() + selectedTime.slice(1)})`;

	div.innerHTML = `
	  <h4 style="margin-bottom: 10px;">${title}</h4>
	  <canvas id="${cb.value}Chart" height="200"></canvas>
	`;
    container.appendChild(div);

    const ctx = div.querySelector('canvas').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], 
        datasets: [{
          label: cb.nextSibling.textContent.trim(),
          data: Array.from({ length: 5 }, () => Math.floor(Math.random() * 100)),
          borderColor: '#016795',
          backgroundColor: 'rgba(1, 103, 149, 0.1)',
          fill: true,
          tension: 0.3
        }]
      }
    });
  });
}
document.querySelectorAll('#dropdown input[name="timeRange"]').forEach(radio => {
  radio.addEventListener('change', renderGraphs);
});

  document.addEventListener('click', function(event) {
    const dropdown = document.getElementById("dropdown");
    const btn = document.getElementById("filterBtn");
    if (!dropdown.contains(event.target) && !btn.contains(event.target)) {
      dropdown.style.display = 'none';
    }
  });
</script>

</body>
</html>
