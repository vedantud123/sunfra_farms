<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Labour Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
		   body {
			font-family: 'Poppins', sans-serif;
			background: linear-gradient(135deg, #e0f7fa, #ffffff);
			margin: 0;
			padding: 20px;
		}

		h1 {
			text-align: center;
			color: #333;
			margin-bottom: 20px;
		}

		.filters {
			display: flex;
			flex-wrap: wrap;
			justify-content: center;
			gap: 10px;
			margin-bottom: 20px;
		}

		.filters select,
		.filters input {
			padding: 10px 12px;
			border-radius: 8px;
			border: 1px solid #ccc;
			min-width: 180px;
			font-size: 14px;
			transition: box-shadow 0.3s ease;
		}

		.filters input:focus,
		.filters select:focus {
			outline: none;
			box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
		}

		.button-container {
			text-align: center;
			margin-bottom: 20px;
		}

		.button-container button {
			padding: 12px 25px;
			margin: 5px;
			border: none;
			border-radius: 8px;
			cursor: pointer;
			background-color: #28a745;
			color: white;
			font-size: 15px;
			transition: 0.3s ease;
		}

		.button-container button:hover {
			background-color: #218838;
		}

		.grid-container {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
			gap: 15px;
		}

		.card {
			background: white;
			border-radius: 15px;
			box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
			padding: 18px;
			transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.3s ease;
			cursor: pointer;
			position: relative;
		}

		.card:hover {
			transform: translateY(-5px);
			box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
		}

		.card:active {
			background-color: #e0f7fa;
			box-shadow: 0 0 25px rgba(0, 123, 255, 0.6);
			transform: scale(0.98);
		}

		.card h3 {
			margin-top: 0;
			color: #007bff;
			font-size: 18px;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.card p {
			margin: 6px 0;
			font-size: 14px;
		}

		.status {
			display: inline-block;
			padding: 6px 12px;
			border-radius: 20px;
			font-size: 12px;
			color: white;
		}

		.status.present {
			background-color: #28a745;
		}

		.status.absent {
			background-color: #dc3545;
		}

		.action-btn {
			margin-top: 10px;
			display: inline-block;
			padding: 8px 15px;
			background-color: #ffc107;
			color: #333;
			border-radius: 6px;
			text-decoration: none;
			font-size: 13px;
		}

		.action-btn:hover {
			background-color: #e0a800;
		}

		/* Inline edit button next to name */
		.action-btn.inline-edit {
			padding: 4px 8px;
			font-size: 12px;
			background-color: #ffc107;
			color: #333;
			border-radius: 5px;
			text-decoration: none;
			margin-left: 8px;
		}

		.action-btn.inline-edit:hover {
			background-color: #e0a800;
		}

		@media screen and (max-width: 600px) {
			.filters {
				flex-direction: column;
				align-items: stretch;
			}

			.filters select,
			.filters input {
				width: 100%;
				min-width: unset;
				box-sizing: border-box;
			}

			.card {
				padding: 12px;
				border-radius: 10px;
			}

			.card h3 {
				font-size: 16px;
				flex-wrap: wrap;
			}

			.card p {
				font-size: 12px;
				margin: 4px 0;
			}

			.status {
				font-size: 11px;
				padding: 4px 8px;
			}

			.action-btn {
				font-size: 12px;
				padding: 6px 10px;
			}

			h1 {
				font-size: 20px;
			}

			.button-container button {
				font-size: 13px;
				padding: 8px 15px;
			}
				}
		.modal {
			display: none;
			position: fixed;
			z-index: 999;
			padding-top: 60px;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgba(0, 0, 0, 0.5);
		}

		.modal-content {
			background-color: #fff;
			margin: auto;
			padding: 20px;
			border-radius: 12px;
			width: 90%;
			max-width: 400px;
			box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
			position: relative;
			animation: slideDown 0.3s ease;
		}

		@keyframes slideDown {
			from {transform: translateY(-50px); opacity: 0;}
			to {transform: translateY(0); opacity: 1;}
		}

		.modal-content h2 {
			text-align: center;
			color: #007bff;
		}

		.modal-content label {
			display: block;
			margin-top: 10px;
			font-weight: 500;
		}

		.modal-content input, .modal-content select {
			width: 100%;
			padding: 10px;
			margin-top: 5px;
			border-radius: 8px;
			border: 1px solid #ccc;
			box-sizing: border-box;
		}

		.modal-content button {
			margin-top: 15px;
			width: 100%;
			background-color: #28a745;
			color: white;
			padding: 10px;
			border: none;
			border-radius: 8px;
			cursor: pointer;
			font-size: 16px;
		}

		.modal-content button:hover {
			background-color: #218838;
		}

		.close-btn {
			position: absolute;
			top: 10px;
			right: 15px;
			color: #aaa;
			font-size: 24px;
			cursor: pointer;
		}

		.close-btn:hover {
			color: #000;
		}.top-bar {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			justify-content: space-between;
			gap: 10px;
			margin-bottom: 20px;
		}

		.filters-inline {
			display: flex;
			gap: 10px;
			align-items: center;
			flex-wrap: wrap;
			flex-grow: 1;
			justify-content: center;
		}

		.filters-inline label {
			display: flex;
			flex-direction: column;
			font-size: 14px;
		}

		.filters-inline input {
			padding: 8px 10px;
			border-radius: 8px;
			border: 1px solid #ccc;
			min-width: 150px;
		}

		.add-btn {
			background-color: #28a745;
			color: white;
			padding: 10px 18px;
			border: none;
			border-radius: 8px;
			font-size: 15px;
			cursor: pointer;
			white-space: nowrap;
		}

		.add-btn:hover {
			background-color: #218838;
		}#attendanceTableContainer {
			margin-top: 20px;
		}.popup-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(0, 0, 0, 0.5);
			display: none;
			justify-content: center;
			align-items: center;
			z-index: 1000;
		}

		.popup-content {
			background-color: #fff;
			padding: 20px;
			border-radius: 8px;
			width: 300px;
			max-width: 90%;
			text-align: center;
			position: relative;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
		}

		.popup-close {
			position: absolute;
			top: 8px;
			right: 10px;
			font-size: 20px;
			cursor: pointer;
			color: #888;
		}

		.popup-close:hover {
			color: #000;
		} .sidebar {
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
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/profit_loss_json_to_web.php'">Profit & Loss Summary</button>
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
   <h1>🌱 Labour Attendance</h1>

	<div id="formModal" class="modal">
		<div class="modal-content">
			<span class="close-btn" onclick="toggleForm()">&times;</span>
			<h2 id="formTitle">Labour Attendance</h2>
			<form id="attendanceForm" method="POST" action="https://sunfra.com/farm/test2/attendance/labour_attendance_save.php">
				<input type="hidden" id="entryId" name="id">
				<input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">

				<label for="name">Name:</label>
				<select id="name" name="name" required>
					<option value="">Loading Names...</option>
				</select>

				<label for="status">Status:</label>
				<select id="status" name="status" required>
					<option value="">Select Status</option>
					<option value="Present">Present</option>
					<option value="Absent">Absent</option>
					<option value="Present/2">Present/2</option>
				</select>

				<label for="working_place">Working Place:</label>
				<select id="working_place" name="working_place" required>
					<option value="">Loading Places...</option>
				</select>

				<button type="submit">Submit</button>
			</form>
		</div>
	</div>

    <div class="top-bar">
		<div class="filters-inline">
			<label style="display: flex; flex-direction: column; font-size: 14px;">
				Month
				<input type="month" id="monthPicker">
			</label>
			<label>
				Search
				<input type="text" id="searchName" placeholder="Search by Name...">
			</label>
		</div>

		<button class="add-btn" onclick="toggleForm()">➕ Add New Entry</button>
	</div>
	<div id="popup" class="popup-overlay">
		<div class="popup-content">
			<span class="popup-close" onclick="closePopup()">×</span>
			<h4>Location Details</h4>
			<p id="locationInfo">Loading...</p>
		</div>
	</div>
	<div id="locationPopup" style="
		display:none;
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		background: white;
		padding: 20px;
		border-radius: 10px;
		box-shadow: 0 0 15px rgba(0,0,0,0.3);
		z-index: 1000;
	">
		<p id="locationText" style="margin: 0 0 10px;"></p>
		<button onclick="closeLocationPopup()" style="
			background: #dc3545;
			color: white;
			border: none;
			padding: 6px 12px;
			border-radius: 5px;
			cursor: pointer;
		">× Close</button>
	</div>

	<div id="attendanceTableContainer" style="overflow-x:auto;"></div>
    </div>
	</main>
</div>
    <script>
	let allData = [];
	const clientId = parseInt(<?php echo json_encode($_SESSION['client_id'] ?? "0"); ?>);
	
	function renderAttendanceTable(data) {
		const container = document.getElementById('attendanceTableContainer');
		container.innerHTML = ''; 

		if (!data.length) {
			container.innerHTML = '<p>No attendance data found.</p>';
			return;
		}

		const names = [...new Set(data.map(item => item.name))].sort();
		const dates = [...new Set(data.map(item => item.date))].sort();

		const statusMap = {};
		data.forEach(item => {
			const name = item.name;
			const date = item.date;
			const status = item.status;

			if (!statusMap[name]) statusMap[name] = {};
			statusMap[name][date] = status;
		});

		let tableHTML = `
		<div style="background-color: #e0f7fa; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
			<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; min-width: 600px;">
				<thead>
					<tr style="background-color: #f1f1f1;">
						<th>Name</th>`;
		dates.forEach(date => {
			const day = new Date(date).getDate();
			tableHTML += `<th>${day}</th>`;
		});
		tableHTML += `<th>Total P</th></tr></thead><tbody>`;

		names.forEach(name => {
			let totalPresent = 0;
			tableHTML += `<tr><td><strong>${name}</strong></td>`;
			dates.forEach(date => {
				const rawStatus = statusMap[name]?.[date];
				let display = 'A';
				let color = '#dc3545';

				if (rawStatus) {
					const statusLower = rawStatus.toLowerCase();
					if (statusLower === 'present') {
						display = 'P';
						color = '#28a745';
						totalPresent += 1;
					} else if (statusLower === 'present/2') {
						display = 'P/2';
						color = '#17a2b8';
						totalPresent += 0.5;
					}
				}

				const location = data.find(item => item.name === name && item.date === date)?.location;
				const hasLocation = (display === 'P' || display === 'P/2') && location;

				const cellContent = hasLocation
					? `<span style="cursor:pointer;" onclick="showLocationPopup('${location.replace(/'/g, "\\'")}')">${display}</span>`
					: display;

				tableHTML += `<td style="text-align:center; color: ${color}; font-weight: 600; cursor: pointer;" onclick="showLocationPopup('${name}', '${date}')">${display}</td>`;

			});
			tableHTML += `<td style="text-align:center; font-weight: bold; color: #000;">${totalPresent}</td></tr>`;
		});
		tableHTML += `</tbody></table></div>`;

		container.innerHTML = tableHTML;
		tableHTML += `
		</tbody></table></div>

		<!-- Location Popup -->
		<div id="locationPopup" style="
			display: none;
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: white;
			border: 1px solid #ccc;
			box-shadow: 0 0 15px rgba(0,0,0,0.2);
			padding: 20px;
			border-radius: 10px;
			z-index: 1000;
			min-width: 300px;
			text-align: center;
		">
			<span onclick="closeLocationPopup()" style="
				position: absolute;
				top: 8px;
				right: 12px;
				cursor: pointer;
				font-size: 20px;
				color: #aaa;
			">&times;</span>
			<h4>Location Info</h4>
			<p id="locationDetails">Loading...</p>
		</div>
		<div id="popupOverlay" style="
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(0,0,0,0.3);
			z-index: 999;
		" onclick="closeLocationPopup()"></div>
		`;
	}

	function applyFilters() {
		const selectedMonth = document.getElementById('monthPicker').value;
		const searchText = document.getElementById('searchName').value.toLowerCase();

		let filteredData = allData;

		// Filter by month
		if (selectedMonth) {
			filteredData = filteredData.filter(item => {
				const date = new Date(item.date);
				const year = date.getFullYear();
				const month = String(date.getMonth() + 1).padStart(2, '0');
				const monthStr = `${year}-${month}`;
				return monthStr === selectedMonth;
			});
		}

		// Filter by name
		if (searchText) {
			filteredData = filteredData.filter(item =>
				item.name.toLowerCase().includes(searchText)
			);
		}

		renderAttendanceTable(filteredData);
	}

	Promise.all([
			fetch(`https://sunfra.com/farm/test2/attendance/labour_attendance_json.php?client_id=${clientId}`).then(res => res.json()),
			fetch(`https://sunfra.com/farm/test2/attendance/labour_master_json.php?client_id=${clientId}`).then(res => res.json())
		])
		.then(([attendanceRes, masterRes]) => {
			const rawAttendance = attendanceRes[clientId] || [];
			const labourList = masterRes[clientId] || [];

			const activeNames = labourList
				.filter(labour => (labour.status || '').toLowerCase() === 'active')
				.map(labour => labour.name.trim().toLowerCase());

			allData = rawAttendance.filter(entry =>
				activeNames.includes(entry.name.trim().toLowerCase())
			);

			document.getElementById('monthPicker').value = new Date().toISOString().slice(0, 7); 
			document.getElementById('monthPicker').addEventListener('change', applyFilters);
			applyFilters(); // call this AFTER setting data and default month

		})
		.catch(error => {
			console.error("Error loading data:", error);
			document.getElementById('attendanceTableContainer').innerHTML = '<p>Error loading data.</p>';
		});


	document.getElementById('searchName').addEventListener('input', applyFilters);

	function toggleForm() {
		const modal = document.getElementById('formModal');
		modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
	}

	window.onclick = function (event) {
		const modal = document.getElementById('formModal');
		if (event.target === modal) {
			modal.style.display = "none";
		}
	}
	document.getElementById('monthPicker').value = new Date().toISOString().slice(0, 7);
	applyFilters();

	function openEditForm(id, name, status, working_place) {
		toggleForm();
		document.getElementById('entryId').value = id;
		document.getElementById('name').value = name;
		document.getElementById('status').value = status;
		document.getElementById('working_place').value = working_place;

		document.getElementById('attendanceForm').action = 'https://sunfra.com/farm/test2/attendance/labour_attendance_save.php';
	}

		fetch(`https://sunfra.com/farm/test2/attendance/labour_master_json.php?client_id=${clientId}`)
		  .then(response => response.json())
		  .then(data => {
			const nameSelect = document.getElementById('name');
			nameSelect.innerHTML = '<option value="">Select Name</option>';

			const clientData = data[clientId] || [];

			// ✅ Filter only Active labours
			const activeLabours = clientData.filter(item =>
			  (item.status || '').toLowerCase() === 'active'
			);

			activeLabours.forEach(item => {
			  const cleanName = item.name.trim();
			  const option = document.createElement('option');
			  option.value = cleanName;
			  option.textContent = cleanName;
			  nameSelect.appendChild(option);
			});
		  })
		  .catch(error => {
			console.error('Error fetching name list:', error);
			document.getElementById('name').innerHTML = '<option value="">Error loading names</option>';
		  });
		function showLocationPopup(name, date) {
			const found = window.attendanceData.find(item => item.name === name && item.date === date);
			const location = found && found.location ? found.location : 'Location not available';

			document.getElementById('locationDetails').innerText = `Name: ${name}\nDate: ${date}\nLocation: ${location}`;
			document.getElementById('locationPopup').style.display = 'block';
			document.getElementById('popupOverlay').style.display = 'block';
		}

		function closeLocationPopup() {
			document.getElementById('locationPopup').style.display = 'none';
			document.getElementById('popupOverlay').style.display = 'none';
		}
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
		  fetch(`https://sunfra.com/farm/test2/configuration/config_location_json.php?client_id=${clientId}`)
			.then(response => response.json())
			.then(data => {
				const workingPlaceSelect = document.getElementById('working_place');
				workingPlaceSelect.innerHTML = '<option value="">Select Place</option>'; // Clear existing options

				if (data[clientId]) {
					data[clientId].forEach(item => {
						const option = document.createElement('option');
						option.value = item.location;
						option.textContent = item.location;
						workingPlaceSelect.appendChild(option);
					});
				}

				const othersOption = document.createElement('option');
				othersOption.value = 'Others';
				othersOption.textContent = 'Others';
				workingPlaceSelect.appendChild(othersOption);
			})
			.catch(error => {
				console.error('Error fetching working places:', error);
				const workingPlaceSelect = document.getElementById('working_place');
				workingPlaceSelect.innerHTML = '<option value="">Error loading places</option>';
			});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
</body>
</html>
