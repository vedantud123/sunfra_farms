<?php 
session_start(); 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login/login.php");
    exit;
}

$clientName = $_SESSION['client_name'] ?? 'Yours';
$client_id  = $_SESSION['client_id'] ?? 0;
$username   = $_SESSION['username'] ?? '';  

$api_url = "https://sunfra.com/farm/sunfra/login/farm_users_list.php";
$response = file_get_contents($api_url);

$is_admin = false;
if ($response !== false) {
    $users = json_decode($response, true);

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
}

$apiUrl = "https://rt.ambientweather.net/v1/devices?applicationKey=134af5db96ee4c4facde6820bc14a01bfc86a92d3d224b4b877fc94671fd1cd9&apiKey=572bf73566ca44b587fa6d64303a2fc9b5b22c4d215542c8bf80e3c5d8b1b322";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

$tempf = $data[0]['lastData']['tempf'] ?? null;
$feelsLikeF = $data[0]['lastData']['feelsLike'] ?? null;
$windspeedF = $data[0]['lastData']['windspeedmph'] ?? null;

$tempc = $tempf ? round(($tempf - 32) * (5 / 9), 2) : 'N/A';
$feelsLikeC = $feelsLikeF ? round(($feelsLikeF - 32) * (5 / 9), 2) : 'N/A';
$windspeed = $windspeedF ?? 'N/A';

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
    :root{
      --accent:#016795;
      --card-bg: rgba(255,255,255,0.96);
      --page-bg: #096C6C;
    }

    html,body{ height:100%; }
    body {
	  margin: 0;
	  font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	  background: linear-gradient(rgba(9,108,108,0.75), rgba(9,108,108,0.75)),
				  url("http://sunfra.com/farm/sunfra/index/poultry.jpg");
	  background-size: cover;
	  background-position: center;
	  background-attachment: fixed;
	  color: #111;
	}

    #filterBtn {
      position: fixed;
      top: 14px;
      right: 14px;
      z-index: 1200;
      background-color: var(--accent);
      color: white;
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      box-shadow: 0 6px 16px rgba(1,103,149,0.25);
    }

    #dropdown {
      position: fixed;
      top: 60px;
      right: 14px;
      z-index: 1300;
      background-color: #f7f7f7;
      color: #111;
      border-radius: 10px;
      border: 1px solid rgba(0,0,0,0.08);
      box-shadow: 0 12px 36px rgba(0,0,0,0.12);
      padding: 14px;
      width: min(700px, 95vw); /* responsive */
      max-width: 700px;
      display: none;
      max-height: 72vh;
      overflow: auto;
      -webkit-overflow-scrolling: touch;
    }

    #dropdown .section-title {
      margin-bottom: 8px;
      font-weight: 700;
      color: white;
      background: var(--accent);
      padding: 6px 10px;
      border-radius: 6px;
      display: inline-block;
    }

    #dropdown label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 8px;
      margin: 4px;
      border-radius: 6px;
      background: #ececec;
      cursor: pointer;
      user-select: none;
    }
    #dropdown label:hover { background:#e0e0e0; }
    #dropdown input[type="checkbox"], #dropdown input[type="radio"] {
      transform: scale(1.05);
      accent-color: var(--accent);
    }

    .time-options-row, .graph-options-row {
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-bottom: 12px;
    }

    #graphContainer {
	  margin: 110px 28px 28px 28px;
	  display: grid;
	  grid-template-columns: repeat(2, 1fr); 
	  gap: 24px;
	}

	.card {
	  background: var(--card-bg);
	  border-radius: 12px;
	  padding: 16px;
	  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
	  min-height: 340px;
	  display:flex;
	  flex-direction:column;
	  justify-content:flex-start;
	}

	.card h3 {
	  margin:0 0 14px 0;
	  color:var(--accent);
	  font-size:1.1rem;
	}

	.card canvas {
	  width: 100% !important;
	  height: 260px !important; 
	  display:block;
	}

	@media (max-width: 900px) {
	  #graphContainer {
		grid-template-columns: repeat(2, 1fr); 
		gap: 16px;
	  }
	  .card {
		min-height: 300px;
		padding: 14px;
	  }
	  .card canvas { height: 220px !important; }
	}

	@media (max-width: 600px) {
	  #graphContainer {
		grid-template-columns: 1fr;
		margin: 96px 12px 18px 12px;
		gap: 18px;
	  }
	  .card {
		min-height: 320px;
	  }
	  .card canvas { height: 240px !important; }
	}
	#mainContentWrapper {
	  display: flex;
	  flex-direction: column;
	  align-items: stretch; /* full width */
	  width: 100%;
	  margin: 0 auto;
	}
	#temperatureContainer {
	  margin: 28px 28px 14px 28px;
	  display: flex;
	  justify-content: center;
	  width: 100%;
	}
	.temp-card {
	  background: linear-gradient(135deg, #028090, #00a896, #02c39a);
	  border-radius: 18px;
	  padding: 20px;               
	  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
	  text-align: center;
	  width: 280px;                
	  color: #ffffff;
	  position: relative;
	  overflow: hidden;
	  transition: transform 0.25s ease, box-shadow 0.25s ease;
	}

	.temp-card:hover {
	  transform: translateY(-3px);
	  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3);
	}

	.temp-card::before {
	  content: "";
	  position: absolute;
	  top: -40px;
	  right: -40px;
	  width: 100px;
	  height: 100px;
	  background: rgba(255, 255, 255, 0.12);
	  border-radius: 50%;
	}

	/* Responsive adjustments */
	@media (max-width: 768px) {
	  .temp-card {
		width: 220px;           /* smaller width for tablets */
		padding: 16px;
	  }

	  .temp-card::before {
		top: -30px;
		right: -30px;
		width: 80px;
		height: 80px;
	  }
	}

	@media (max-width: 480px) {
	  .temp-card {
		width: 180px;           /* smaller width for phones */
		padding: 12px;
	  }

	  .temp-card::before {
		top: -20px;
		right: -20px;
		width: 60px;
		height: 60px;
	  }
	}

	.temp-card h3 {
	  margin: 0 0 16px;
	  font-size: 1.6rem;              /* bigger heading */
	  font-weight: 600;
	}

	.temp-value {
	  font-size: 3.6rem;              /* large temperature number */
	  font-weight: bold;
	  color: #ffeb3b;                 /* bright yellow */
	  margin: 16px 0;
	}

	.temp-status {
	  margin-top: 18px;
	  font-size: 1.1rem;
	  font-weight: 500;
	  color: #f1f1f1;
	}

	.temp-status span {
	  display: flex;
	  align-items: center;
	  justify-content: center;
	  gap: 6px;
	  margin-top: 6px;
	}
	#graphContainer {
	  margin: 0 28px 28px 28px;
	  display: grid;
	  grid-template-columns: repeat(2, 1fr);
	  gap: 24px;
	}
	@media (max-width: 900px) {
	  #graphContainer {
		grid-template-columns: 1fr;
		gap: 16px;
	  }
	}
	/* Graph Button */
	.graph-btn {
	  margin-top: 22px;
	  padding: 12px 24px;             /* bigger button */
	  border: none;
	  border-radius: 10px;
	  background: linear-gradient(135deg, #4facfe, #00f2fe);
	  color: #fff;
	  font-size: 15px;
	  font-weight: 600;
	  cursor: pointer;
	  transition: 0.3s;
	}
	.graph-btn:hover {
	  background: linear-gradient(135deg, #43e97b, #38f9d7);
	  transform: scale(1.05);
	}

	/* Popup Modal */
	.modal {
	  display: none;                  /* hidden by default */
	  position: fixed;
	  z-index: 1000;
	  left: 0; top: 0;
	  width: 100%; height: 100%;
	  background: linear-gradient(
		rgba(2, 128, 144, 0.85),      /* teal */
		rgba(0, 196, 255, 0.85)       /* light blue */
	  );
	  justify-content: center;
	  align-items: center;
	  transition: background 0.3s ease;
	}
	.modal-content {
	  background: #ffffff;
	  padding: 30px;
	  border-radius: 18px;
	  width: 95%;              /* wider modal */
	  max-width: 1100px;       /* more width */
	  max-height: 150vh;        /* modal will occupy up to 90% of screen height */
	  overflow-y: auto;        /* scroll if content is taller than viewport */
	  box-shadow: 0 10px 28px rgba(0,0,0,0.25);
	  animation: fadeIn 0.3s ease;
	}

	.close-btn {
	  float: right;
	  font-size: 22px;
	  cursor: pointer;
	  color: #444;
	}
	.close-btn:hover {
	  color: #e53935;
	}

	#temperatureChartWrapper {
	  margin-top: 20px;
	  background: #ffffffee;
	  border-radius: 16px;
	  box-shadow: 0 8px 26px rgba(0,0,0,0.15);
	  padding: 20px;
	  /* Remove any fixed heights from wrapper */
	}
	#temperatureChartWrapper h3 {
	  margin: 0 0 14px 0;
	  color: #028090;
	  font-size: 1.1rem;
	  font-weight: 600;
	}

	#temperatureChart {
	  width: 100% !important;
	  height: 2000px !important;  /* increase if still tight */
	}

	/* Responsive tweaks */
	@media (max-width: 600px) {
	  #temperatureContainer {
		margin: 60px 12px 16px 12px;
	  }
	  .temp-card {
		width: 100%;                   /* full width on mobile */
		padding: 20px;                 /* less padding */
	  }
	  .temp-card h3 {
		font-size: 1.3rem;
	  }
	  .temp-value {
		font-size: 2.6rem;
	  }
	  .temp-status {
		font-size: 1rem;
	  }
	  .graph-btn {
		padding: 10px 18px;
		font-size: 14px;
	  }
	  #temperatureChartWrapper {
		margin: 14px 6px;
		padding: 8px;
	  }
	  #temperatureChart {
		height: 260px !important;
	  }
	  #tempChart {
		height: 220px !important;
		min-height: 150px;
	  }
	  .modal {
		align-items: flex-end;         /* slide up effect */
		padding: 10px;
		background: linear-gradient(
		  rgba(72, 61, 139, 0.9),      /* dark slate blue */
		  rgba(123, 104, 238, 0.9)     /* soft purple */
		);
	  }
	}
	@keyframes fadeIn {
	  from { opacity: 0; transform: scale(0.9); }
	  to { opacity: 1; transform: scale(1); }
	}
	#tempChart {
	  width: 100% !important;
	  max-width: 100%;
	  height: 380px !important;   
	  max-height: 60vh;
	  min-height: 260px;
	  display: block;
	  margin: 0 auto;
	}#customDateRange {
	  display: flex;
	  gap: 16px;
	  align-items: center;
	  margin-top: 10px;
	}

	#customDateRange label {
	  display: flex;
	  flex-direction: column;
	  font-weight: 600;
	  font-size: 0.9rem;
	  color: #016795; 
	}

	#customDateRange input[type="date"] {
	  margin-top: 4px;
	  padding: 8px 12px;
	  border: 1.5px solid #016795;
	  border-radius: 8px;
	  background-color: #fff;
	  color: #111;
	  font-size: 1rem;
	  outline: none;
	  transition: border-color 0.3s ease;
	  cursor: pointer;
	  width: 160px;
	}

	/* Border color changes on focus */
	#customDateRange input[type="date"]:focus {
	  border-color: #00bfff;
	  box-shadow: 0 0 6px #00bfff88;
	}

	/* Optional: Customize the calendar icon in supported browsers */
	#customDateRange input[type="date"]::-webkit-calendar-picker-indicator {
	  cursor: pointer;
	  filter: invert(38%) sepia(88%) saturate(3702%) hue-rotate(168deg) brightness(96%) contrast(92%);
	}

	/* Responsive: stack vertically on small screens */
	@media (max-width: 500px) {
	  #customDateRange {
		flex-direction: column;
		gap: 8px;
	  }
	  #customDateRange input[type="date"] {
		width: 100%;
	  }
	}/* Modal background */
	.modal {
	  display: none; /* Hidden by default */
	  position: fixed;
	  z-index: 1000;
	  left: 0; top: 0;
	  width: 100%; height: 100%;
	  background: rgba(0, 0, 0, 0.6);
	  justify-content: center;
	  align-items: center;
	  overflow: auto;
	}

	/* Modal content box */
	.modal-content {
	  background-color: #fff;
	  padding: 20px 30px;
	  border-radius: 12px;
	  max-width: 800px;
	  width: 90%;
	  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
	  position: relative;
	  animation: slideDown 0.3s ease-out;
	}

	/* Close button */
	.close-btn {
	  position: absolute;
	  top: 12px;
	  right: 20px;
	  font-size: 28px;
	  font-weight: bold;
	  cursor: pointer;
	  color: #333;
	  transition: color 0.2s ease;
	}
	.close-btn:hover {
	  color: #e74c3c;
	}

	/* Modal heading */
	.modal-content h2 {
	  text-align: center;
	  margin-bottom: 20px;
	  font-size: 24px;
	  color: #333;
	}

	/* Date picker container */
	.modal-content div {
	  display: flex;
	  align-items: center;
	  justify-content: center;
	  gap: 10px;
	  margin-bottom: 20px;
	}

	/* Date input */
	#trendDate {
	  padding: 6px 12px;
	  font-size: 16px;
	  border-radius: 6px;
	  border: 1px solid #ccc;
	  outline: none;
	  transition: border 0.2s ease;
	}
	#trendDate:focus {
	  border-color: #007bff;
	}

	/* Load Data button */
	button {
	  padding: 7px 16px;
	  font-size: 16px;
	  border: none;
	  border-radius: 6px;
	  background-color: #007bff;
	  color: #fff;
	  cursor: pointer;
	  transition: background-color 0.2s ease;
	}
	button:hover {
	  background-color: #0056b3;
	}

	/* Chart wrapper */
	#temperatureChartWrapper {
	  width: 100%;
	  height: 400px;
	}

	/* Animation */
	@keyframes slideDown {
	  from { opacity: 0; transform: translateY(-20px); }
	  to { opacity: 1; transform: translateY(0); }
	}

	/* Responsive for small screens */
	@media (max-width: 600px) {
	  .modal-content {
		padding: 15px 20px;
		width: 95%;
	  }
	  #temperatureChartWrapper {
		height: 300px;
	  }
	  button {
		font-size: 14px;
		padding: 6px 12px;
	  }
	  #trendDate {
		font-size: 14px;
		padding: 5px 10px;
	  }
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

  <button id="filterBtn" aria-haspopup="true" aria-expanded="false">📊 Filter Graphs</button>

  <div id="dropdown" role="dialog" aria-label="Filter graphs panel">
    <div style="margin-bottom:12px;">
      <span class="section-title">Select Format:</span>
    </div>

    <div class="time-options-row">
      <label><input type="radio" name="timeRange" value="today" checked> <span>Today</span></label>
      <label><input type="radio" name="timeRange" value="yesterday"> <span>Yesterday</span></label>
      <label><input type="radio" name="timeRange" value="weekly"> <span>Weekly</span></label>
      <label><input type="radio" name="timeRange" value="monthly"> <span>Monthly</span></label>
      <label><input type="radio" name="timeRange" value="yearly"> <span>Yearly</span></label>
	  <label><input type="radio" name="timeRange" value="custom" /> Custom Range</label>
    </div>

    <hr style="margin:8px 0; border:none; border-top:1px solid rgba(0,0,0,0.06);">
	<div id="customDateRange" style="display:none;">
	  <label>From: <input type="date" id="fromDate" /></label>
	  <label>To: <input type="date" id="toDate" /></label>
	</div>

    <div style="margin-bottom:10px;"><span class="section-title">Graph Options:</span></div>
    <div class="graph-options-row">
	
      <label id="profitLossOption">
		  <input type="checkbox" value="profitandloss" onchange="renderGraphs()"> 
		  <span>Profit And Loss</span>
	  </label>
      <label><input type="checkbox" value="sheadMortality" onchange="renderGraphs()"> <span>Shead Mortality</span></label>
      <label><input type="checkbox" value="eggProduction" onchange="renderGraphs()"> <span>Egg Production</span></label>
      <label><input type="checkbox" value="eggDamage" onchange="renderGraphs()"> <span>Egg Damage</span></label>
	  <label><input type="checkbox" value="feedintake" onchange="renderGraphs()"> <span>Feed Intake</span></label>
      <label><input type="checkbox" value="eggWeight" onchange="renderGraphs()"> <span>Egg Weight</span></label>
	  <label><input type="checkbox" value="productionPercentage" onchange="renderGraphs()"> <span>Production %</span></label>
	  <label><input type="checkbox" value="openingandclosingbalance" onchange="renderGraphs()"> <span>Opening & Closing</span></label>
      <label><input type="checkbox" value="eggprice" onchange="renderGraphs()"> <span>Egg Price</span></label>
      <label><input type="checkbox" value="livebirds" onchange="renderGraphs()"> <span>Live Birds</span></label>
	  <label><input type="checkbox" value="eggSale" onchange="renderGraphs()"> <span>Egg Sale</span></label>
    </div>
  </div>
  <div id="mainContentWrapper">
		<div id="temperatureContainer">
		  <div class="temp-card">
			<h3>🌡️ Temperature</h3>
			<div class="temp-value"><?= $tempc ?> °C</div>
			<div class="temp-status">
			  <span>🤔 Feels Like: <?= $feelsLikeC ?> °C</span>
			  <span>💨 Wind Speed: <?= $windspeed ?> mph</span>
			</div>
			<button class="graph-btn" onclick="openModal()">📊 View Graph</button>
		  </div>
		</div>

		<div id="graphModal" class="modal">
		  <div class="modal-content">
			<span class="close-btn" onclick="closeModal()">&times;</span>
			<h2>📈 Temperature Trend</h2>

			<!-- Date Picker -->
			<div style="margin-bottom: 15px; text-align: center;">
			  <label for="trendDate">Select Date:</label>
			  <input type="date" id="trendDate" name="trendDate" value="<?php echo date('Y-m-d'); ?>" />
			</div>

			<div id="temperatureChartWrapper">
			  <canvas id="tempChart"></canvas>
			</div>
		  </div>
		</div>

	  <div id="graphContainer"></div>
  </div>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
const clientId = <?= $client_id ?>;
const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;

document.addEventListener("DOMContentLoaded", function() {
  if (!isAdmin) {
    const profitLossOption = document.getElementById("profitLossOption");
    if (profitLossOption) {
      profitLossOption.style.display = "none";
    }
  }
});

function toggleDropdown() {
  const dropdown = document.getElementById('dropdown');
  const btn = document.getElementById('filterBtn');
  const isOpen = dropdown.style.display === 'block';
  dropdown.style.display = isOpen ? 'none' : 'block';
  btn.setAttribute('aria-expanded', !isOpen);
}

document.addEventListener('click', (evt) => {
  const dropdown = document.getElementById('dropdown');
  const btn = document.getElementById('filterBtn');
  if (!dropdown.contains(evt.target) && !btn.contains(evt.target)) {
    dropdown.style.display = 'none';
    btn.setAttribute('aria-expanded', false);
  }
});

document.getElementById('filterBtn').addEventListener('click', (e) => {
  e.stopPropagation();
  toggleDropdown();
});

async function fetchProfitLoss(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/profit_and_loss_details/profit_loss_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url, { method: 'GET', credentials: 'same-origin' });
    const text = await res.text();
    console.log('API raw response:', text);
    try {
      return JSON.parse(text);
    } catch (parseErr) {
      console.error('JSON parse error:', parseErr);
      return null;
    }
  } catch (err) {
    console.error('Fetch error:', err);
    return null;
  }
}

async function fetchMortality(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/mortality_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Mortality API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Mortality Fetch Error:", err);
    return null;
  }
}

async function fetchEggProduction(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/egg_stock_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Egg Production API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Egg Production Fetch Error:", err);
    return null;
  }
}

async function fetchEggDamage(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/egg_damage_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Egg Damage API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Egg Damage Fetch Error:", err);
    return null;
  }
}

async function fetchFeedIntake(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/feed_intake_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    return JSON.parse(text);
  } catch (err) {
    console.error("Feed Intake Fetch Error:", err);
    return null;
  }
}

async function fetchEggWeight(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/egg_weight_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Egg Weight API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Egg Weight Fetch Error:", err);
    return null;
  }
}

async function fetchProductionPercentage(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/production_percentage_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Production Percentage API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Production Percentage Fetch Error:", err);
    return null;
  }
}

async function fetchEggSale(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/egg_sale_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Egg Sale API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Egg Sale Fetch Error:", err);
    return null;
  }
}

function getDateRange(selectedTime) {
  const today = new Date();
  let fromDate, toDate;
  if (selectedTime === 'today') {
    fromDate = toDate = today.toISOString().split('T')[0];
  } else if (selectedTime === 'yesterday') {
    const y = new Date(today); y.setDate(today.getDate() - 1);
    fromDate = toDate = y.toISOString().split('T')[0];
  } else if (selectedTime === 'weekly') {
    const end = today.toISOString().split('T')[0];
    const start = new Date(today); start.setDate(today.getDate() - 6);
    fromDate = start.toISOString().split('T')[0]; toDate = end;
  } else if (selectedTime === 'monthly') {
    const end = today.toISOString().split('T')[0];
    const start = new Date(today.getFullYear(), today.getMonth(), 1);
    fromDate = start.toISOString().split('T')[0]; toDate = end;
  } else if (selectedTime === 'yearly') {
    const end = today.toISOString().split('T')[0];
    const start = new Date(today.getFullYear(), 0, 1);
    fromDate = start.toISOString().split('T')[0]; toDate = end;
  }else if (selectedTime === 'custom') {
	  const fromDateInput = document.getElementById('fromDate').value;
	  const toDateInput = document.getElementById('toDate').value;
	  if (fromDateInput && toDateInput) {
		fromDate = fromDateInput;
		toDate = toDateInput;
	  } else {
		fromDate = toDate = (new Date()).toISOString().split('T')[0];
	  }
	}  else {
		fromDate = toDate = today.toISOString().split('T')[0];
	  }
  return { fromDate, toDate };
}

function formatDate(dateStr) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString('en-US', options);
}

async function renderGraphs() {
  const container = document.getElementById('graphContainer');
  container.innerHTML = '';

  const selectedGraphs = Array.from(document.querySelectorAll('#dropdown input[type="checkbox"]:checked'));
  const selectedTime = document.querySelector('#dropdown input[name="timeRange"]:checked')?.value || 'today';
  const { fromDate, toDate } = getDateRange(selectedTime);


  await Promise.all(selectedGraphs.map(async (cb, idx) => {
    const type = cb.value;
    let titleDateText = '';
	if (fromDate === toDate) {
		titleDateText = formatDate(fromDate);  
	} else {
		titleDateText = `${formatDate(fromDate)} to ${formatDate(toDate)}`;  
	}

	const titleText = `${cb.closest('label').innerText.trim()} (${titleDateText})`;

    const wrapper = document.createElement('div');
    wrapper.className = 'card';

    const canvasId = `${type}_canvas_${idx}_${Date.now()}`;

    wrapper.innerHTML = `<h3>${titleText}</h3><canvas id="${canvasId}"></canvas>`;
    container.appendChild(wrapper);

    const canvas = document.getElementById(canvasId);
    if (!canvas) { console.error('Canvas not found:', canvasId); return; }
    const ctx = canvas.getContext('2d');

    if (type === 'profitandloss') {
	  const apiData = await fetchProfitLoss(fromDate, toDate);

	  if (apiData && Array.isArray(apiData.data) && apiData.data.length > 0) {
		const sheadNames = apiData.data.map(d => d.shead_name);

		const profits = apiData.data.map(d => {
		  const val = (d.profit || "0").toString().replace(/,/g, "");
		  return Number(val) || 0;
		});

		const totalProfit = profits.reduce((a, b) => a + b, 0);

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		wrapper.innerHTML = `
		  <div class="graph-card">
			<h3>${titleText}</h3>
			<div class="chart-container">
			  <canvas id="${canvasId}"></canvas>
			</div>
		  </div>
		`;

		const ctx = document.getElementById(canvasId).getContext("2d");

		canvas._chartInstance = new Chart(ctx, {
		  type: 'bar',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Profit (₹)',
			  data: profits,
			  backgroundColor: profits.map(v =>
				v >= 0 ? 'rgba(40,167,69,0.85)' : 'rgba(220,53,69,0.85)'
			  ),
			  borderRadius: 6
			}]
		  },
		  options: {
			maintainAspectRatio: false,
			plugins: {
			  tooltip: {
				callbacks: {
				  label: c => `₹ ${Number(c.raw).toLocaleString()}`
				}
			  },
			  legend: { display: false }
			},
			scales: {
			  x: { ticks: { autoSkip: false } },
			  y: {
				beginAtZero: true,
				ticks: {
				  callback: v => '₹ ' + v
				}
			  }
			},
			animation: { duration: 800, easing: 'easeOutCubic' }
		  }
		});

		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = totalProfit >= 0 ? "#28a745" : "#dc3545"; 
		summary.style.color = "#fff";
		summary.style.padding = "12px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = totalProfit >= 0
		  ? `🚀 Total Profit: ₹ ${totalProfit.toLocaleString()}`
		  : `📉 Total Loss: ₹ ${Math.abs(totalProfit).toLocaleString()}`;

		wrapper.querySelector(".graph-card").appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Profit & Loss data available for selected range.</div>`;
	  }
	}
	else if (type === 'sheadMortality') {
	  const apiData = await fetchMortality(fromDate, toDate);
	  console.log("Mortality Parsed JSON:", apiData);

	  let mortalityArray = [];
	  if (Array.isArray(apiData)) {
		mortalityArray = apiData;
	  } else if (apiData && Array.isArray(apiData.records)) {
		mortalityArray = apiData.records;
	  }

	  if (Array.isArray(mortalityArray) && mortalityArray.length > 0) {
		const sheadNames = mortalityArray.map(d => d.sheadNo);
		const deaths = mortalityArray.map(d => Number(d.noOfBirds || d.totalBirds || 0));
		const totalDeaths = deaths.reduce((a, b) => a + b, 0);

		wrapper.innerHTML = "";

		const title = document.createElement("h3");
		title.innerText = titleText;
		wrapper.appendChild(title);

		const newCanvas = document.createElement("canvas");
		newCanvas.width = 400;
		newCanvas.height = 400;
		wrapper.appendChild(newCanvas);
		const ctx = newCanvas.getContext("2d");

		if (newCanvas._chartInstance) {
		  newCanvas._chartInstance.destroy();
		}

		const colors = mortalityArray.map((_, i) => {
		  const hue = (i * 40) % 360;
		  return `hsl(${hue}, 70%, 55%)`;
		});

		newCanvas._chartInstance = new Chart(ctx, {
		  type: 'doughnut',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Mortality',
			  data: deaths,
			  backgroundColor: colors,
			  borderWidth: 2
			}]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  title: { display: false }, 
			  tooltip: {
				callbacks: {
				  label: ctx => `${ctx.label}: ${ctx.raw} birds`
				}
			  },
			  legend: { display: false }
			},
			layout: { padding: 20 }
		  }
		});

		const legendDiv = document.createElement("div");
		legendDiv.style.display = "flex";
		legendDiv.style.flexWrap = "wrap";
		legendDiv.style.justifyContent = "center";
		legendDiv.style.marginTop = "15px";
		legendDiv.style.gap = "10px";

		sheadNames.forEach((name, index) => {
		  const item = document.createElement("div");
		  item.style.display = "flex";
		  item.style.alignItems = "center";
		  item.style.cursor = "pointer";
		  item.style.padding = "4px 10px";
		  item.style.border = "1px solid #ddd";
		  item.style.borderRadius = "6px";
		  item.style.background = "#f9f9f9";

		  const colorBox = document.createElement("span");
		  colorBox.style.display = "inline-block";
		  colorBox.style.width = "14px";
		  colorBox.style.height = "14px";
		  colorBox.style.background = colors[index];
		  colorBox.style.marginRight = "6px";
		  colorBox.style.borderRadius = "3px";

		  const label = document.createElement("span");
		  label.textContent = name;
		  label.style.fontSize = "13px";
		  label.style.color = "#333";

		  item.appendChild(colorBox);
		  item.appendChild(label);

		  item.onclick = function () {
			const dataset = newCanvas._chartInstance.data.datasets[0];

			if (!dataset._originalData) {
			  dataset._originalData = [...dataset.data];
			}

			if (dataset.data[index] === 0) {
			  dataset.data[index] = dataset._originalData[index];
			  colorBox.style.opacity = "1";
			  label.style.textDecoration = "none";
			} else {
			  dataset.data[index] = 0;
			  colorBox.style.opacity = "0.3";
			  label.style.textDecoration = "line-through";
			}
			newCanvas._chartInstance.update();
		  };

		  legendDiv.appendChild(item);
		});

		wrapper.appendChild(legendDiv);

		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#ff4d6d";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `☠️ Total Mortality: ${totalDeaths.toLocaleString()}`;
		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Mortality data available for selected range.</div>`;
	  }
	}
	else if (type === 'eggProduction') {
	  const apiData = await fetchEggProduction(fromDate, toDate);

	  if (Array.isArray(apiData) && apiData.length > 0) {
		const sheadNames = apiData.map(d => d.shead_name);
		const good = apiData.map(d => parseFloat(d.Good || 0));
		const small = apiData.map(d => parseFloat(d.Small || 0));
		const big = apiData.map(d => parseFloat(d.Big || 0));
		const damaged = apiData.map(d => parseFloat(d.Damaged || 0));

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		canvas._chartInstance = new Chart(ctx, {
		  type: 'bar',
		  data: {
			labels: sheadNames,
			datasets: [
			  { label: 'Good', data: good, backgroundColor: '#4caf50' },
			  { label: 'Small', data: small, backgroundColor: '#2196f3' },
			  { label: 'Big', data: big, backgroundColor: '#ff9800' },
			  { label: 'Damaged', data: damaged, backgroundColor: '#f44336' }
			]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  title: {
				display: true,
				text: 'Egg Production by Shead',
				font: { size: 18 }
			  },
			  tooltip: {
				mode: 'index',
				intersect: false
			  }
			},
			scales: {
			  x: { stacked: true },
			  y: { stacked: true, beginAtZero: true }
			},
			onClick: (evt, elements) => {
			  if (elements.length > 0) {
				const index = elements[0].index; 
				const shead = sheadNames[index];
				const details = `
				  <b>${shead}</b><br>
				  ✅ Good: ${good[index].toLocaleString()}<br>
				  📏 Small: ${small[index].toLocaleString()}<br>
				  🍳 Big: ${big[index].toLocaleString()}<br>
				  ❌ Damaged: ${damaged[index].toLocaleString()}
				`;

				//let popup = document.getElementById("eggPopup");
				if (!popup) {
				  popup = document.createElement("div");
				  popup.id = "eggPopup";
				  popup.style.position = "fixed";
				  popup.style.top = "50%";
				  popup.style.left = "50%";
				  popup.style.transform = "translate(-50%, -50%)";
				  popup.style.background = "#fff";
				  popup.style.padding = "15px";
				  popup.style.border = "2px solid #016795";
				  popup.style.borderRadius = "8px";
				  popup.style.boxShadow = "0 4px 12px rgba(0,0,0,0.2)";
				  popup.style.zIndex = "9999";
				  popup.style.maxWidth = "250px";
				  popup.style.textAlign = "left";
				  document.body.appendChild(popup);
				}
				popup.innerHTML = details + `<br><br><button onclick="document.getElementById('eggPopup').remove()">Close</button>`;
			  }
			}
		  }
		});

		const totalEggs = good.reduce((a,b)=>a+b,0) + small.reduce((a,b)=>a+b,0) + big.reduce((a,b)=>a+b,0) + damaged.reduce((a,b)=>a+b,0);
		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#016795";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `🥚 Total Eggs: ${totalEggs.toLocaleString()}`;
		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Egg Production data available for selected range.</div>`;
	  }
	}else if (type === 'eggDamage') {
	  const apiData = await fetchEggDamage(fromDate, toDate);

	  if (Array.isArray(apiData) && apiData.length > 0) {
		const sheadNames = apiData.map(d => d.shead_name);
		const trays = apiData.map(d => parseFloat(d.trays || 0));

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		canvas._chartInstance = new Chart(ctx, {
		  type: 'line',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Damaged Trays',
			  data: trays,
			  fill: false,
			  borderColor: '#e63946',   // Red line
			  backgroundColor: '#e63946',
			  tension: 0.3,             // Smooth curve
			  pointRadius: 5,
			  pointHoverRadius: 7,
			  pointStyle: 'circle'
			}]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  title: {
				display: true,
				font: { size: 18 }
			  },
			  tooltip: {
				callbacks: {
				  label: ctx => `🟥 ${ctx.dataset.label}: ${ctx.raw} trays`
				}
			  },
			  legend: { display: false }
			},
			scales: {
			  x: {
				ticks: { autoSkip: false }
			  },
			  y: {
				beginAtZero: true,
				title: {
				  display: true,
				  text: 'Trays'
				}
			  }
			},
			animation: { duration: 800, easing: 'easeOutCubic' }
		  }
		});

		const totalTrays = trays.reduce((a, b) => a + b, 0).toFixed(2);
		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#e63946";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `🥚 Total Damaged Trays: ${totalTrays}`;
		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Egg Damage data available for selected range.</div>`;
	  }
	}else if (type === 'feedintake') {
	  const apiData = await fetchFeedIntake(fromDate, toDate);
	  if (apiData && Array.isArray(apiData.data) && apiData.data.length > 0) {
		const sheadNames = apiData.data.map(d => d.shead_name);
		const avgFeed = apiData.data.map(d => parseFloat(d.average_feed_intake) || 0);

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		const colors = sheadNames.map((_, i) => {
		  const hue = (i * 40) % 360;
		  return `hsl(${hue}, 70%, 55%)`;
		});

		canvas._chartInstance = new Chart(ctx, {
		  type: 'pie',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Average Feed Intake (g)',
			  data: avgFeed,
			  backgroundColor: colors,
			  borderColor: '#fff',
			  borderWidth: 2
			}]
		  },
		  options: {
			  responsive: true,
			  maintainAspectRatio: false,
			  plugins: {
				legend: { display: false },
				tooltip: {
				  callbacks: {
					label: ctx => `${ctx.label}: ${ctx.raw.toFixed(2)} g`
				  }
				},
				title: {
				  display: true,
				  text: 'Feed Intake by Shead',
				  font: { size: 16 }
				}
			  }
			}
		});

		const legendDiv = document.createElement("div");
		legendDiv.style.display = "flex";
		legendDiv.style.flexWrap = "wrap";
		legendDiv.style.justifyContent = "center";
		legendDiv.style.marginTop = "12px";
		legendDiv.style.gap = "10px";

		sheadNames.forEach((name, index) => {
		  const item = document.createElement("div");
		  item.style.display = "flex";
		  item.style.alignItems = "center";
		  item.style.cursor = "pointer";
		  item.style.padding = "4px 10px";
		  item.style.border = "1px solid #ddd";
		  item.style.borderRadius = "6px";
		  item.style.background = "#f9f9f9";

		  const colorBox = document.createElement("span");
		  colorBox.style.display = "inline-block";
		  colorBox.style.width = "14px";
		  colorBox.style.height = "14px";
		  colorBox.style.background = colors[index];
		  colorBox.style.marginRight = "6px";
		  colorBox.style.borderRadius = "3px";

		  const label = document.createElement("span");
		  label.textContent = name;
		  label.style.fontSize = "13px";
		  label.style.color = "#333";

		  item.appendChild(colorBox);
		  item.appendChild(label);

		  item.onclick = function () {
			  const chart = canvas._chartInstance;
			  const meta = chart.getDatasetMeta(0);

			  meta.data[index].hidden = !meta.data[index].hidden;

			  if (meta.data[index].hidden) {
				colorBox.style.opacity = "0.3";
				label.style.textDecoration = "line-through";
			  } else {
				colorBox.style.opacity = "1";
				label.style.textDecoration = "none";
			  }

			  chart.update();
			};

		  legendDiv.appendChild(item);
		});

		wrapper.appendChild(legendDiv);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Feed Intake data available for selected range.</div>`;
	  }
	}
	else if (type === 'eggWeight') {
	  const apiData = await fetchEggWeight(fromDate, toDate);

	  if (apiData && Array.isArray(apiData.data) && apiData.data.length > 0) {
		const sheadNames = apiData.data.map(d => d.shead_name);
		const avgWeights = apiData.data.map(d => parseFloat(d.average_egg_weight || 0));
		const days = apiData.data.map(d => d.days);

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		canvas._chartInstance = new Chart(ctx, {
		  type: 'bar',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Avg Egg Weight (g)',
			  data: avgWeights,
			  backgroundColor: '#6a5acd', 
			  borderRadius: 8
			}]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  tooltip: {
				callbacks: {
				  label: (ctx) => 
					`${ctx.dataset.label}: ${ctx.raw.toFixed(2)} g (Days: ${days[ctx.dataIndex]})`
				}
			  },
			  title: {
				display: true,
				text: 'Average Egg Weight by Shead',
				font: { size: 18 }
			  },
			  legend: { display: false }
			},
			scales: {
			  x: { ticks: { autoSkip: false } },
			  y: {
				beginAtZero: true,
				title: {
				  display: true,
				  text: 'Weight (g)'
				}
			  }
			},
			animation: { duration: 800, easing: 'easeOutCubic' }
		  }
		});

		const overallAvg = (
		  avgWeights.reduce((a, b) => a + b, 0) / avgWeights.length
		).toFixed(2);

		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#6a5acd";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `⚖️ Overall Avg Weight: ${overallAvg} g`;

		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Egg Weight data available for selected range.</div>`;
	  }
	}
	else if (type === 'productionPercentage') {
	  const apiData = await fetchProductionPercentage(fromDate, toDate);

	  if (apiData && Array.isArray(apiData.sheads) && apiData.sheads.length > 0) {
		const sheadNames = apiData.sheads.map(d => d.shead_name);
		const percentages = apiData.sheads.map(d => parseFloat(d.average_percentage || 0));
		const overall = parseFloat(apiData.overall_average || 0);

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		canvas._chartInstance = new Chart(ctx, {
		  type: 'bar',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Production %',
			  data: percentages,
			  backgroundColor: percentages.map(p => 
				p >= 80 ? '#28a745' : p >= 50 ? '#ffc107' : '#dc3545'
			  ),
			  borderRadius: 6
			}]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  tooltip: {
				callbacks: {
				  label: ctx => `${ctx.dataset.label}: ${ctx.raw.toFixed(2)}%`
				}
			  },
			  title: {
				display: true,
				text: 'Average Production Percentage by Shead',
				font: { size: 18 }
			  },
			  legend: { display: false }
			},
			scales: {
			  x: { beginAtZero: true, max: 100, ticks: { callback: v => v + "%" } },
			  y: { ticks: { autoSkip: false } }
			},
			animation: { duration: 800, easing: 'easeOutCubic' }
		  }
		});

		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = overall >= 80 ? "#28a745" : overall >= 50 ? "#ffc107" : "#dc3545";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `📊 Overall Average: ${overall.toFixed(2)}%`;
		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Production Percentage data available for selected range.</div>`;
	  }
	}else if (type === 'eggSale') {
	  const apiData = await fetchEggSale(fromDate, toDate);
	  if (apiData && apiData.status === 'success' && Array.isArray(apiData.data) && apiData.data.length > 0) {
		wrapper.innerHTML = `<h3>${titleText}</h3>`;
		const table = document.createElement('table');
		table.style.width = '100%';
		table.style.borderCollapse = 'collapse';
		table.style.marginTop = '12px';

		table.innerHTML = `
		  <thead>
			<tr style="background-color: var(--accent); color: white;">
			  <th style="padding: 10px; text-align: left;">Sale Name</th>
			  <th style="padding: 10px; text-align: right;">Total Stock</th>
			  <th style="padding: 10px; text-align: right;">Return Eggs</th>
			  <th style="padding: 10px; text-align: right;">Current Stock</th>
			  <th style="padding: 10px; text-align: center; width: 40px;">Details</th>
			</tr>
		  </thead>
		  <tbody></tbody>
		`;

		const tbody = table.querySelector('tbody');
		wrapper.appendChild(table);

		apiData.data.forEach((sale) => {
		  const tr = document.createElement('tr');
		  tr.style.borderBottom = '1px solid #ccc';

		  const toggleCell = document.createElement('td');
		  toggleCell.style.textAlign = 'center';
		  toggleCell.style.cursor = 'pointer';
		  toggleCell.style.userSelect = 'none';
		  toggleCell.style.fontWeight = 'bold';
		  toggleCell.style.fontSize = '18px';
		  toggleCell.textContent = '▶'; 
		  toggleCell.style.width = '40px';

		  const saleNameCell = document.createElement('td');
		  saleNameCell.textContent = sale.sale_name;
		  saleNameCell.style.padding = '10px';
		  saleNameCell.style.textAlign = 'left';

		  const totalStockCell = document.createElement('td');
		  totalStockCell.textContent = sale.total_stock;
		  totalStockCell.style.padding = '10px';
		  totalStockCell.style.textAlign = 'right';

		  const returnEggsCell = document.createElement('td');
		  returnEggsCell.textContent = sale.return_eggs;
		  returnEggsCell.style.padding = '10px';
		  returnEggsCell.style.textAlign = 'right';

		  const currentStockCell = document.createElement('td');
		  currentStockCell.textContent = sale.current_stock;
		  currentStockCell.style.padding = '10px';
		  currentStockCell.style.textAlign = 'right';

		  tr.appendChild(saleNameCell);
		  tr.appendChild(totalStockCell);
		  tr.appendChild(returnEggsCell);
		  tr.appendChild(currentStockCell);
		  tr.appendChild(toggleCell);

		  const detailsRow = document.createElement('tr');
		  const detailsCell = document.createElement('td');
		  detailsCell.colSpan = 5;
		  detailsCell.style.display = 'none';
		  detailsCell.style.backgroundColor = '#f9f9f9';
		  detailsCell.style.padding = '10px';

		  const detailsTable = document.createElement('table');
		  detailsTable.style.width = '100%';
		  detailsTable.style.borderCollapse = 'collapse';

		  detailsTable.innerHTML = `
			<thead>
			  <tr style="background: #ddd;">
				<th style="padding: 6px; border: 1px solid #ccc;">Date</th>
				<th style="padding: 6px; border: 1px solid #ccc;">Shed No</th>
				<th style="padding: 6px; border: 1px solid #ccc;">Sale Qty</th>
				<th style="padding: 6px; border: 1px solid #ccc;">Type of Eggs</th>
				<th style="padding: 6px; border: 1px solid #ccc;">Remarks</th>
			  </tr>
			</thead>
		  `;

		  const detailsBody = document.createElement('tbody');
		  sale.details.forEach(d => {
			const row = document.createElement('tr');
			row.innerHTML = `
			  <td style="padding: 6px; border: 1px solid #ccc;">${d.date}</td>
			  <td style="padding: 6px; border: 1px solid #ccc;">${d.shed_no}</td>
			  <td style="padding: 6px; border: 1px solid #ccc;">${d.sale_qty}</td>
			  <td style="padding: 6px; border: 1px solid #ccc;">${d.type_of_eggs}</td>
			  <td style="padding: 6px; border: 1px solid #ccc;">${d.remarks || ""}</td>
			`;
			detailsBody.appendChild(row);
		  });
		  detailsTable.appendChild(detailsBody);

		  detailsCell.appendChild(detailsTable);
		  detailsRow.appendChild(detailsCell);

		  tbody.appendChild(tr);
		  tbody.appendChild(detailsRow);

		  toggleCell.addEventListener('click', () => {
			if (detailsCell.style.display === 'none') {
			  detailsCell.style.display = 'table-cell';
			  toggleCell.textContent = '▼'; 
			} else {
			  detailsCell.style.display = 'none';
			  toggleCell.textContent = '▶'; 
			}
		  });
		});

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Egg Sale data available for selected range.</div>`;
	  }
	}
	else {
      if (canvas._chartInstance) { canvas._chartInstance.destroy(); }
      canvas._chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
          datasets: [{
            label: cb.nextSibling?.textContent?.trim() || 'Series',
            data: Array.from({length:5}, ()=>Math.floor(Math.random()*100)),
            borderColor: '#016795',
            backgroundColor: 'rgba(1,103,149,0.12)',
            fill: true,
            tension: 0.3
          }]
        },
        options: { maintainAspectRatio:false }
      });
    }
  }));
}

document.addEventListener('DOMContentLoaded', function() {
  const isCustomSelected = document.querySelector('input[name="timeRange"]:checked').value === 'custom';
  document.getElementById('customDateRange').style.display = isCustomSelected ? 'block' : 'none';

  document.querySelectorAll('input[name="timeRange"]').forEach(radio => {
    radio.addEventListener('change', () => {
      const isCustomSelected = document.querySelector('input[name="timeRange"]:checked').value === 'custom';
      const customDateDiv = document.getElementById('customDateRange');
      if (isCustomSelected) {
        customDateDiv.style.display = 'block';
      } else {
        customDateDiv.style.display = 'none';
      }
      renderGraphs(); 
    });
  });
});

document.getElementById('fromDate').addEventListener('change', renderGraphs);
document.getElementById('toDate').addEventListener('change', renderGraphs);

renderGraphs();

async function fetchTemperature(date = null) {
  const clientId = 1; 

  if (!date) {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    date = `${yyyy}-${mm}-${dd}`;
  }

  const url = `https://sunfra.com/farm/sunfra/index/temperature_json.php?client_id=${clientId}&date=${date}`;
  
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Temperature API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Temperature Fetch Error:", err);
    return null;
  }
}

async function renderTempChart(date = null) {
  const apiData = await fetchTemperature(date);
  if (!apiData || !apiData.labels) {
    console.error("No temperature data received from API");
    return;
  }

  const temps = apiData.temperature.map(v => parseFloat(v));
  const feels = apiData.feelslike.map(v => parseFloat(v));

  const allValues = [...temps, ...feels];
  let minTemp = Math.floor(Math.min(...allValues) / 2) * 2;
  let maxTemp = Math.ceil(Math.max(...allValues) / 2) * 2;

  const ctx = document.getElementById("tempChart").getContext("2d");

  if (window.tempChartInstance) {
    window.tempChartInstance.destroy();
  }

  window.tempChartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: apiData.labels,
      datasets: [
        {
          label: "Temperature (°C)",
          data: temps,
          borderColor: "red",
          backgroundColor: "rgba(255, 0, 0, 0.2)",
          borderWidth: 1,
          pointRadius: 1,
          pointHoverRadius: 5
        },
        {
          label: "Feels Like (°C)",
          data: feels,
          borderColor: "blue",
          backgroundColor: "rgba(0, 0, 255, 0.2)",
          borderWidth: 1,
          pointRadius: 1,
          pointHoverRadius: 5
        }
      ]
    },
    options: { /* your existing Chart.js options */ }
  });
}

document.getElementById("trendDate").addEventListener("change", function() {
  const selectedDate = this.value;
  renderTempChart(selectedDate);
});

function openModal() {
  const modal = document.getElementById("graphModal");
  modal.style.display = "flex";

  const today = new Date().toISOString().split("T")[0];
  document.getElementById("trendDate").value = today;
  renderTempChart(today);
}

function closeModal() {
  document.getElementById("graphModal").style.display = "none";
}


async function renderEggSales(fromDate, toDate) {
  const container = document.getElementById('graphContainer');
  container.innerHTML = ''; 

  const apiData = await fetchEggSales(fromDate, toDate);
  if (!apiData || apiData.status !== 'success' || !apiData.data || apiData.data.length === 0) {
    container.innerHTML = '<p>No egg sale data available for selected range.</p>';
    return;
  }

  apiData.data.forEach((sale, index) => {
    const card = document.createElement('div');
    card.className = 'card';

    const header = document.createElement('div');
    header.style.display = 'flex';
    header.style.justifyContent = 'space-between';
    header.style.cursor = 'pointer';   
    header.style.alignItems = 'center';

    const title = document.createElement('h3');
    title.textContent = sale.sale_name;
    title.style.margin = '0';

    const toggleArrow = document.createElement('span');
    toggleArrow.textContent = '▶';
    toggleArrow.style.transition = 'transform 0.3s ease';
    toggleArrow.style.userSelect = 'none';

    header.appendChild(title);
    header.appendChild(toggleArrow);
    card.appendChild(header);

    const summary = document.createElement('div');
    summary.innerHTML = `
      <strong>Total Stock:</strong> ${sale.total_stock} &nbsp;&nbsp;
      <strong>Return Eggs:</strong> ${sale.return_eggs} &nbsp;&nbsp;
      <strong>Current Stock:</strong> ${sale.current_stock}
    `;
    summary.style.margin = '8px 0';
    card.appendChild(summary);

    const detailsContainer = document.createElement('div');
    detailsContainer.style.display = 'none';
    detailsContainer.style.borderTop = '1px solid #ccc';
    detailsContainer.style.paddingTop = '8px';
    detailsContainer.style.marginTop = '8px';

    const detailsTable = document.createElement('table');
    detailsTable.style.width = '100%';
    detailsTable.style.borderCollapse = 'collapse';
    detailsTable.style.fontSize = '0.9rem';

    const thead = document.createElement('thead');
    thead.innerHTML = `
      <tr style="background-color:#016795; color:#fff;">
        <th style="padding:6px; border:1px solid #ddd;">Date</th>
        <th style="padding:6px; border:1px solid #ddd;">Shed No</th>
        <th style="padding:6px; border:1px solid #ddd;">Sale Qty</th>
        <th style="padding:6px; border:1px solid #ddd;">Type of Eggs</th>
        <th style="padding:6px; border:1px solid #ddd;">Remarks</th>
      </tr>
    `;
    detailsTable.appendChild(thead);

    const tbody = document.createElement('tbody');
    (sale.details || []).forEach(detail => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="padding:6px; border:1px solid #ddd;">${detail.date}</td>
        <td style="padding:6px; border:1px solid #ddd;">${detail.shed_no}</td>
        <td style="padding:6px; border:1px solid #ddd;">${detail.sale_qty}</td>
        <td style="padding:6px; border:1px solid #ddd;">${detail.type_of_eggs}</td>
        <td style="padding:6px; border:1px solid #ddd;">${detail.remarks || ''}</td>
      `;
      tbody.appendChild(tr);
    });
    detailsTable.appendChild(tbody);
    detailsContainer.appendChild(detailsTable);
    card.appendChild(detailsContainer);

    header.addEventListener('click', () => {
      if (detailsContainer.style.display === 'none') {
        detailsContainer.style.display = 'block';
        toggleArrow.textContent = '▼'; 
      } else {
        detailsContainer.style.display = 'none';
        toggleArrow.textContent = '▶'; 
      }
    });

    container.appendChild(card);
  });
}
const sidebar = document.getElementById('sidebar');
const mainContent = document.querySelector('.content');
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