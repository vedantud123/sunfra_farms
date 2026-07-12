<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
 
$current_feature = "Egg Godown"; // Set per page
 
// 1. Login check
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
 
// 2. Admin check
$users_url = "https://sunfra.com/farm/sunfra_clients/login/farm_users_list.php";
$users_response = @file_get_contents($users_url);
 
if ($users_response === false) {
    error_log("Admin API failure: $users_url");
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
    exit;
}
 
$users = json_decode($users_response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Admin JSON parse error: " . json_last_error_msg());
    header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
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
 
// 3. Feature check (only for non-admins)
if (!$is_admin) {
    $feature_url = "https://sunfra.com/farm/sunfra_clients/configuration/config_supervisor_json.php?client_id=" . urlencode($client_id);
    $feature_response = @file_get_contents($feature_url);
 
    if ($feature_response === false) {
        error_log("Feature API failure: $feature_url");
        header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
        exit;
    }
 
    $features = json_decode($feature_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Feature JSON parse error: " . json_last_error_msg());
        header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
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
        header("Location: https://sunfra.com/farm/sunfra_clients/index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Egg Weight Records</title>
  <style>
    /* ======= CSS Styling ======== */
    body {
      font-family: Arial, sans-serif;
      background: #ADD8E6;
      margin: 0;
      padding: 20px;
      min-height: 100vh;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 28px;
    }

    .filters {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
    }

    input, select {
      padding: 8px 15px;
      border-radius: 8px;
      border: 1.5px solid #388e3c;
      background: #fff;
      font-size: 16px;
      transition: border-color 0.2s;
    }

    input:focus, select:focus {
      border-color: #17313E;
      outline: none;
    }

    h2 {
      color: #17313E;
      font-weight: 600;
      font-size: 28px;
      margin: 0;
    }

    button.add-btn, .add-btn {
      background: #17313E;
      color: #fff;
      border: none;
      border-radius: 25px;
      padding: 10px 28px;
      font-weight: 600;
      font-size: 18px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.1s ease;
      box-shadow: 0 3px 8px rgba(23, 49, 62, 0.3);
    }

    button.add-btn:hover, .add-btn:hover {
      background: #114249;
      transform: scale(1.05);
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 22px rgba(23, 49, 62, 0.14);
      overflow: hidden;
    }

    th {
      background-color: #17313E;
      color: #ffffff;
      font-size: 16px;
      font-weight: 600;
      padding: 14px 15px;
      border-bottom: 2px solid #2a4d59;
      text-transform: none;
      letter-spacing: normal;
      text-align: center;
    }

    td {
      color: #17313E;
      font-size: 15px;
      background: #f0f7f5;
      padding: 13px 15px;
      border-bottom: 1px solid #d4e2df;
      text-align: center;
      transition: background 0.3s;
    }

    tr:hover td {
      background: #d2e4df;
    }

    .edit-btn {
      padding: 7px 20px;
      background-color: #17313E;
      color: #fff;
      border: none;
      border-radius: 20px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(23, 49, 62, 0.2);
      transition: background-color 0.3s ease, transform 0.1s ease;
    }

    .edit-btn:hover {
      background-color: #114249;
      transform: scale(1.05);
    }

    /* Modal backdrop */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    /* Modal content box */
    .modal-content {
      background: #fff;
      border-radius: 10px;
      width: 90vw;
      max-width: 600px;
      padding: 20px 30px;
      position: relative;
      box-sizing: border-box;
    }

    .modal-title {
      color: #17313E;
      margin-bottom: 25px;
      font-weight: 600;
      font-size: 24px;
      text-align: center;
    }

    .modal-form {
      width: 100%;
    }

    .modal-input, select {
      width: 100%;
      padding: 8px 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1.5px solid #cccccc;
      font-size: 16px;
      box-sizing: border-box;
      transition: border-color 0.3s;
    }

    .modal-input:focus, select:focus {
      border-color: #17313E;
      outline: none;
    }

    .tray-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 25px;
    }

    .tray-grid label {
      display: block;
      font-weight: 600;
      color: #17313E;
      margin-bottom: 6px;
      font-size: 14px;
    }

    .shead-text {
      padding: 8px 12px;
      background: #f0f7f5;
      border-radius: 6px;
      font-weight: 600;
      font-size: 16px;
      color: #17313E;
      margin-bottom: 20px;
      user-select: none;
    }

    .modal-buttons {
      text-align: right;
    }

    .btn {
      padding: 10px 22px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 16px;
      color: #fff;
      transition: background-color 0.3s ease, transform 0.1s ease;
      margin-left: 8px;
    }

    .cancel-btn {
      background: #999;
    }
    .cancel-btn:hover {
      background: #666;
    }

    .submit-btn {
      background: #17313E;
    }
    .submit-btn:hover {
      background: #114249;
      transform: scale(1.05);
    }

    @media (max-width: 900px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead tr {
        display: none;
      }
      tr {
        background: #fff;
        margin-bottom: 16px;
        border-radius: 16px;
        box-shadow: 0 2px 8px #b9ccc9;
        padding: 8px 0;
      }
      td {
        position: relative;
        padding-left: 50%;
        text-align: left;
        background: #e9f0ec;
        border-bottom: none;
        margin: 0;
      }
      td:before {
        position: absolute;
        left: 15px;
        top: 13px;
        width: 45%;
        white-space: nowrap;
        font-weight: 600;
        font-size: 13px;
        color: #17313E;
        content: attr(data-label);
        text-transform: none;
        letter-spacing: normal;
      }
      .filters {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
      }
      .add-btn {
        width: 100%;
        padding: 12px 0;
        font-size: 17px;
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
  <a href="https://sunfra.com/farm/sunfra_clients/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
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
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/sunfra_clients/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
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
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
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
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
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
  <a href="https://sunfra.com/farm/sunfra_clients/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra_clients/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
<div class="header">
  <h2>Egg Weight Records</h2>
  <div class="filters">
    <input type="date" id="filterDate" />
    <input type="text" id="filterShead" placeholder="Search Shead" />
    <button class="add-btn">➕ Add</button>
  </div>
</div>

<!-- Modal -->
<div id="modal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="modalTitle">
  <div class="modal-content">
    <h3 id="modalTitle" class="modal-title">Add Record</h3>
    <form id="modalForm" class="modal-form" novalidate>
      <input type="hidden" name="id" id="modalId" />
      <input type="hidden" name="date" id="modalHiddenDate" />

      <div id="sheadContainer">
        <select name="shead_name" id="modalShead" required class="modal-input" aria-required="true">
          <option value="" disabled selected>Select Shead</option>
        </select>
        <div id="modalSheadText" class="shead-text" style="display:none;"></div>
      </div>

      <div class="tray-grid">
        <div><label for="modalTray_1">Tray 1</label><input type="number" min="0" name="tray_1" id="modalTray_1" required class="modal-input" /></div>
        <div><label for="modalTray_2">Tray 2</label><input type="number" min="0" name="tray_2" id="modalTray_2" required class="modal-input" /></div>
        <div><label for="modalTray_3">Tray 3</label><input type="number" min="0" name="tray_3" id="modalTray_3" required class="modal-input" /></div>
        <div><label for="modalTray_4">Tray 4</label><input type="number" min="0" name="tray_4" id="modalTray_4" required class="modal-input" /></div>
        <div><label for="modalTray_5">Tray 5</label><input type="number" min="0" name="tray_5" id="modalTray_5" required class="modal-input" /></div>
        <div><label for="modalTray_6">Tray 6</label><input type="number" min="0" name="tray_6" id="modalTray_6" required class="modal-input" /></div>
        <div><label for="modalTray_7">Tray 7</label><input type="number" min="0" name="tray_7" id="modalTray_7" required class="modal-input" /></div>
        <div><label for="modalTray_8">Tray 8</label><input type="number" min="0" name="tray_8" id="modalTray_8" required class="modal-input" /></div>
      </div>

      <div class="modal-buttons">
        <button type="button" id="cancelBtn" class="btn cancel-btn">Cancel</button>
        <button type="submit" class="btn submit-btn">Submit</button>
      </div>
    </form>
  </div>
</div>

<table id="dataTable" aria-label="Egg Weight Records Table">
  <thead>
    <tr>
      <th>Date</th>
      <th>Shead Name</th>
      <th>Tray 1</th>
      <th>Tray 2</th>
      <th>Tray 3</th>
      <th>Tray 4</th>
      <th>Tray 5</th>
      <th>Tray 6</th>
      <th>Tray 7</th>
      <th>Tray 8</th>
      <th>Average</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="tableBody"></tbody>
</table>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
<script>
  const client_id = <?= $client_id ?>;
  const tableBody = document.getElementById("tableBody");
  const filterDate = document.getElementById("filterDate");
  const filterShead = document.getElementById("filterShead");

  const modal = document.getElementById('modal');
  const modalForm = document.getElementById('modalForm');
  const modalTitle = document.getElementById('modalTitle');
  const cancelBtn = document.getElementById('cancelBtn');

  // Wait until modal elements are available
  let sheadSelect = null;
  let modalSheadText = null;
  let sheadContainer = null;

  window.addEventListener('DOMContentLoaded', () => {
    sheadSelect = document.getElementById('modalShead');
    modalSheadText = document.getElementById('modalSheadText');
    sheadContainer = document.getElementById('sheadContainer');
    loadSheads();
  });

  const modalHiddenDate = document.getElementById('modalHiddenDate');
  filterDate.valueAsDate = new Date();

  let sheadList = [];
  let cachedRecords = [];

  function loadSheads() {
    fetch(`https://sunfra.com/farm/sunfra_clients/configuration/shead_number_json.php?client_id=${client_id}`)
      .then(res => res.json())
      .then(data => {
        sheadList = data;
        if (sheadSelect) {
          sheadSelect.innerHTML = '<option value="" disabled selected>Select Shead</option>';
          sheadList.forEach(shead => {
            const option = document.createElement('option');
            option.value = shead.shead_name;
            option.textContent = shead.shead_name;
            sheadSelect.appendChild(option);
          });
        }
      })
      .catch(err => {
        console.error('Failed to load sheads list', err);
        if (sheadSelect) sheadSelect.innerHTML = '<option value="" disabled>Error loading sheads</option>';
      });
  }

  function fetchData() {
    fetch(`https://sunfra.com/farm/sunfra_clients/egg_godown/egg_weight_json.php?client_id=${client_id}`)
      .then(res => res.json())
      .then(data => {
        cachedRecords = Array.isArray(data) ? data : (data[client_id] || []);
        const searchDate = filterDate.value;
        const searchShead = filterShead.value.toLowerCase();
        tableBody.innerHTML = "";

        cachedRecords.forEach(record => {
          const rowDate = record.date;
          const sheadName = record.shead_name.toLowerCase();

          if ((!searchDate || rowDate === searchDate) && (!searchShead || sheadName.includes(searchShead))) {
            const tr = document.createElement("tr");
            tr.innerHTML = `
              <td data-label="Date">${record.date}</td>
              <td data-label="Shead Name">${record.shead_name}</td>
              <td data-label="Tray 1">${record.tray_1}</td>
              <td data-label="Tray 2">${record.tray_2}</td>
              <td data-label="Tray 3">${record.tray_3}</td>
              <td data-label="Tray 4">${record.tray_4}</td>
              <td data-label="Tray 5">${record.tray_5}</td>
              <td data-label="Tray 6">${record.tray_6}</td>
              <td data-label="Tray 7">${record.tray_7}</td>
              <td data-label="Tray 8">${record.tray_8}</td>
              <td data-label="Average">${record.average}</td>
              <td data-label="Action"><button class="edit-btn" onclick="editRow(${record.id})">Edit</button></td>
            `;
            tableBody.appendChild(tr);
          }
        });
      })
      .catch(err => console.error("Fetch Error:", err));
  }

  function openModal(record = null) {
    modalTitle.textContent = record ? 'Edit Record' : 'Add Record';
    modalForm.id.value = record?.id || '';

    if (record) {
      sheadContainer.innerHTML = `
        <label for="modalSheadText">Shead Name</label>
        <div id="modalSheadText" class="shead-text">${record.shead_name}</div>
      `;

      if (!document.getElementById('modalSheadHidden')) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'shead_name';
        hiddenInput.id = 'modalSheadHidden';
        modalForm.appendChild(hiddenInput);
      }
      document.getElementById('modalSheadHidden').value = record.shead_name;

      modalHiddenDate.value = record.date;

      for (let i = 1; i <= 8; i++) {
        modalForm[`tray_${i}`].value = record[`tray_${i}`];
      }
    } else {
      sheadContainer.innerHTML = `
        <label for="modalShead">Shead Name</label>
        <select name="shead_name" id="modalShead" required class="modal-input" aria-required="true">
          <option value="" disabled selected>Select Shead</option>
        </select>
      `;

      // Repopulate Shead dropdown
      const newSheadSelect = document.getElementById('modalShead');
      sheadList.forEach(shead => {
        const option = document.createElement('option');
        option.value = shead.shead_name;
        option.textContent = shead.shead_name;
        newSheadSelect.appendChild(option);
      });

      const hiddenInput = document.getElementById('modalSheadHidden');
      if (hiddenInput) hiddenInput.remove();

      for (let i = 1; i <= 8; i++) {
        modalForm[`tray_${i}`].value = '';
      }

      modalHiddenDate.value = new Date().toISOString().slice(0, 10);
    }

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeModal() {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  }

  cancelBtn.addEventListener('click', e => {
    e.preventDefault();
    closeModal();
  });

  modalForm.addEventListener('submit', e => {
    e.preventDefault();

    const formData = new FormData(modalForm);
    const data = {};
    formData.forEach((value, key) => data[key] = value);

    if (!data.date) {
      data.date = modalHiddenDate.value;
    }

    if (!data.id) {
      const duplicate = cachedRecords.find(rec =>
        rec.shead_name.toLowerCase() === data.shead_name.toLowerCase() &&
        rec.date === data.date
      );
      if (duplicate) {
        alert(`❌ A record for Shead "${data.shead_name}" on date "${data.date}" already exists.`);
        return;
      }
    }

    fetch('https://sunfra.com/farm/sunfra_clients/egg_godown/egg_weight_save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
      .then(res => res.json())
      .then(res => {
        alert(res.message);
        if (res.status === 'success') {
          closeModal();
          fetchData();
        }
      })
      .catch(err => alert('Error: ' + err));
  });

  document.querySelector('.add-btn').addEventListener('click', e => {
    e.preventDefault();
    openModal();
  });

  function editRow(id) {
    fetch(`https://sunfra.com/farm/sunfra_clients/egg_godown/egg_weight_json.php?client_id=${client_id}`)
      .then(res => res.json())
      .then(data => {
        const record = Array.isArray(data) ? data.find(r => r.id == id) : (data[client_id] || []).find(r => r.id == id);
        if (record) {
          openModal(record);
        } else {
          alert('Record not found.');
        }
      })
      .catch(err => console.error("Error fetching record for edit:", err));
  }

  filterDate.addEventListener('change', fetchData);
  filterShead.addEventListener('input', fetchData);

  // Initial load
  fetchData();
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
