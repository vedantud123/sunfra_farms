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
 
// 3. Feature check (only for non-admins)
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

date_default_timezone_set('Asia/Kolkata');

$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_number_json.php?client_id=$client_id";
$shead_response = file_get_contents($shead_url);

if ($shead_response === false) {
    echo json_encode(["error" => "Unable to fetch shead data"]);
    exit;
}

$shead_data = json_decode($shead_response, true);
if (!is_array($shead_data)) {
    echo json_encode(["error" => "Invalid JSON received"]);
    exit;
}

$shead_list = [];
foreach ($shead_data as $shead) {
    if (isset($shead['shead_name'])) {
        $shead_list[] = $shead['shead_name'];
    }
}

$date = date('Y-m-d'); 

$api_url = "https://sunfra.com/farm/sunfra/egg_godown/egg_godown_party_name.php?client_id=$client_id";
$response = file_get_contents($api_url);

$party_names = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Egg Sales</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #ADD8E6;
      margin: 0;
      padding: 0;
    }
    header {
      background-color: #4A90E2;
      color: white;
      padding: 1rem;
      text-align: center;
    }
    .container {
      max-width: 1100px;
      margin: 2rem auto;
      padding: 1rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .top-bar {
	  display: flex;
	  align-items: center;
	  gap: 10px;
	  margin-bottom: 1rem;
	}

	.search-input {
	  flex-grow: 1;
	  max-width: 300px;
	  margin-right: auto;
	  padding: 8px;
	  border: 1px solid #ccc;
	  border-radius: 4px;
	}

	.btn-group > button {
	  margin-left: 10px;
	}

    .top-bar input {
      padding: 8px;
      width: 250px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .top-bar button {
      padding: 8px 16px;
      background-color: #4A90E2;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }
    th {
      background-color: #f0f0f0;
    }
    .edit-btn {
      padding: 5px 10px;
      background-color: #ffc107;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .edit-btn:hover {
      background-color: #e0a800;
    }.modal {
	  display: none; /* Hidden by default */
	  position: fixed;
	  z-index: 1000;
	  left: 0;
	  top: 0;
	  width: 100%;
	  height: 100%;
	  overflow: auto;
	  background-color: rgba(0,0,0,0.6);
	}

	.modal-content {
	  background-color: #fff;
	  margin: 5% auto;
	  padding: 20px;
	  border-radius: 10px;
	  width: 90%;
	  max-width: 600px;
	  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
	  position: relative;
	}

	.close {
	  position: absolute;
	  top: 10px;
	  right: 20px;
	  font-size: 24px;
	  font-weight: bold;
	  color: #333;
	  cursor: pointer;
	}.form-row {
	  display: flex;
	  gap: 20px;
	  margin-bottom: 20px;
	}
	.form-group {
	  flex: 1;
	  min-width: 100px;
	  display: flex;
	  flex-direction: column;
	}
	.form-group.full {
	  flex: 1 1 100%;
	}
	input, select, textarea {
	  width: 100%;
	  padding: 10px;
	  font-size: 1rem;
	  border: 1px solid #ccc;
	  border-radius: 5px;
	  box-sizing: border-box;
	}
	label {
	  margin-bottom: 5px;
	  font-weight: 600;
	  color: #333;
	}
	.submit-btn {
	  background-color: #3b82f6;
	  color: white;
	  font-size: 1.1rem;
	  padding: 12px;
	  border: none;
	  border-radius: 6px;
	  cursor: pointer;
	  text-align: center;
	  width: 100%;
	}
	.submit-btn:hover {
	  background-color: #2563eb;
	}.search-input {
	  flex-grow: 1;
	  max-width: 300px;
	  margin-right: auto; /* push buttons to the right */
	  padding: 8px;
	  border: 1px solid #ccc;
	  border-radius: 4px;
	}

	.btn-group > button {
	  margin-left: 10px;
	}/* Basic modal styles */
	.modal {
		position: fixed;
		z-index: 1000;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		overflow: auto;
		background-color: rgba(0, 0, 0, 0.5); /* dark overlay */
	}
	.modal-content {
		background-color: #fff;
		margin: 10% auto;
		padding: 20px;
		border: 1px solid #888;
		width: 80%;
		max-width: 500px;
		position: relative;
		border-radius: 5px;
	}
	.close {
		position: absolute;
		top: 10px;
		right: 15px;
		font-size: 28px;
		font-weight: bold;
		cursor: pointer;
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
  <header>
    <h1>Egg Godown Sale Records</h1>
  </header>

  <div class="container">
	<div class="top-bar">
	  <input type="text" id="searchInput" placeholder="Search by date, shead, type..." class="search-input">

	  <div class="btn-group">
		<input type="date" id="filterDate" class="search-input" style="width: 180px; margin-left: 10px;" />
		<button id="filterBtn" class="btn btn-primary" style="margin-left: 5px;">Filter</button>
		<button class="btn btn-success" id="addNewBtn">Add New</button>
		<button class="btn btn-warning" id="damageEggsBtn">Damage Eggs During Sale</button>
	  </div>
	</div>


    <table id="eggTable">
      <thead>
        <tr>
          <th>Date</th>
          <th>Shead</th>
          <th>Eggs</th>
          <th>Type</th>
          <th>Sale</th>
          <th>Price</th>
          <th>Remarks</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="eggBody">
        <!-- Data will load here -->
      </tbody>
    </table>
  </div>
  <!-- Modal Container -->
	<div id="entryModal" class="modal">
	  <div class="modal-content">
		<span class="close" onclick="closeModal()">&times;</span>

		<form id="entryForm" action="" method="post">
		  <input type="hidden" name="id" id="form_id">
		  <div class="form-row">
			  <div class="form-group">
				<label for="entry_date">Date</label>
				<input type="date" id="entry_date" name="entry_date" required>
			  </div>
			</div>
		  <div class="form-row">
			<div class="form-group">
			  <label for="shead_name">Shead Name</label>
			  <select id="shead_name" name="shead_name" class="form-control" required>
				<option value="">Select Shead</option>
				<?php foreach ($shead_list as $shead): ?>
				  <option value="<?= htmlspecialchars(trim($shead)) ?>"><?= htmlspecialchars(trim($shead)) ?></option>
				<?php endforeach; ?>
			  </select>
			</div>

			<div class="form-group">
			  <label for="no_of_trays">No of Trays</label>
			  <input type="number" id="no_of_trays" name="no_of_trays" required>
			</div>
		  </div>

		  <div class="form-row">
			<div class="form-group">
			  <label for="no_of_loose_eggs">Loose Eggs</label>
			  <input type="number" id="no_of_loose_eggs" name="no_of_loose_eggs">
			</div>
			<div class="form-group">
			  <label for="type_of_eggs">Type of Eggs</label>
			  <select id="type_of_eggs" name="type_of_eggs" required>
				<option value="" disabled selected>Select Type</option>
				<option value="Good">Good</option>
				<option value="Damaged">Damaged</option>
				<option value="Small">Small</option>
				<option value="Big">Big</option>
			  </select>
			</div>
		  </div>

		  <div class="form-row">
			<div class="form-group">
			  <label for="sale">Sale To</label>
			  <input type="text" id="sale" name="sale">
			</div>
			<div class="form-group">
			  <label for="sale_price">Sale Price(optional)</label>
			  <input type="text" id="sale_price" name="sale_price">
			</div>
		  </div>

		  <div class="form-row">
			<div class="form-group full">
			  <label for="remarks">Remarks(optional)</label>
			  <textarea id="remarks" name="remarks" rows="2"></textarea>
			</div>
		  </div>

		  <div class="form-row">
			<div class="form-group full">
			  <button type="submit" class="submit-btn">Submit</button>
			</div>
		  </div>
		</form>
	  </div>
	</div>
	<!-- Damage Eggs Modal -->
	<div id="damageModal" class="modal" style="display: none;">
	  <div class="modal-content">
		<span class="close" onclick="closeDamageModal()">&times;</span>
		<form id="damageForm" action="" method="post">
		<p>
		  <label for="damage_entry_date">Date:</label>
		  <input type="date" name="damage_entry_date" id="damage_entry_date" required>
		</p>
		  <p>
			<label for="damage_shead_name">Shead Name:</label>
			<select name="shead_name" id="damage_shead_name" required>
			  <option value="">Select option</option>
			  <?php foreach ($shead_list as $shead): ?>
				<option value="<?= htmlspecialchars($shead) ?>"><?= htmlspecialchars($shead) ?></option>
			  <?php endforeach; ?>
			</select>
		  </p>
		  <p>
			<label for="damage_no_of_trays">No Of Trays:</label>
			<input type="text" name="no_of_trays" id="damage_no_of_trays" required>
		  </p>
		  <p>
			<label for="damage_no_of_loose_Eggs">No Of Loose Eggs:</label>
			<input type="text" name="no_of_loose_Eggs" id="damage_no_of_loose_Eggs">
		  </p>
		  <p>
			<label for="damage_sale">Party Name:</label>
			<select name="sale" id="damage_sale" required>
			  <option value="">--Select--</option>
			  <?php
			  if (is_array($party_names)) {
				foreach ($party_names as $party) {
				  $sale_option = htmlspecialchars($party);
				  echo "<option value=\"$sale_option\">$sale_option</option>";
				}
			  } else {
				echo '<option value="">No party names found</option>';
			  }
			  ?>
			</select>
		  </p>
		  <p>
			<button type="submit">Submit</button>
		  </p>
		</form>
	</div>
	</div>
	</main>
	</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
  <script>
  let allRecords = [];
  const clientId = <?php echo $client_id; ?>;
  const apiUrl = `https://sunfra.com/farm/sunfra/egg_godown/egg_godown_sale_json.php?client_id=${clientId}`;
  const saveApiUrl = "https://sunfra.com/farm/sunfra/egg_godown/egg_godown_sale_save.php";
  const todayStr = new Date().toISOString().split('T')[0];
	document.getElementById('filterDate').value = todayStr;

	fetchEggData();


  async function fetchEggData() {
	  try {
		const response = await fetch(apiUrl);
		const data = await response.json();

		allRecords = [];
		for (const key in data) {
		  allRecords.push(...data[key]);
		}

		applyFilters();

		document.getElementById('searchInput').addEventListener('input', applyFilters);
		document.getElementById('filterBtn').addEventListener('click', applyFilters);
	  } catch (error) {
		console.error('Error fetching data:', error);
	  }
	}

  function renderTable(records) {
    const tbody = document.getElementById('eggBody');
    tbody.innerHTML = '';

    records.forEach(item => {
      const tr = document.createElement('tr');

      tr.innerHTML = `
        <td>${item.timestamp.split(' ')[0]}</td>
        <td>${item.shead_name}</td>
        <td>${item.no_of_eggs}</td>
        <td>${item.type_of_eggs}</td>
        <td>${item.sale}</td>
        <td>${item.sale_price}</td>
        <td>${item.remarks || ''}</td>
        <td>
          <button class="edit-btn" onclick='openEditModal(${JSON.stringify(item)})'>Edit</button>
        </td>
      `;

      tbody.appendChild(tr);
    });
  }

 function openEditModal(data) {
	  const modal = document.getElementById('entryModal');
	  modal.style.display = 'block';
		
	  document.getElementById('form_id').value = data.id || '';
	  document.getElementById('entry_date').value =
		data.timestamp ? data.timestamp.split(' ')[0] : new Date().toISOString().split('T')[0];
	  
	  setSheadSelect(data.shead_name);

	  if (data.no_of_eggs) {
		const [trays, loose] = data.no_of_eggs.split(".");
		document.getElementById('no_of_trays').value = parseInt(trays) || 0;
		document.getElementById('no_of_loose_eggs').value = parseInt(loose) || 0;
	  } else {
		document.getElementById('no_of_trays').value = '';
		document.getElementById('no_of_loose_eggs').value = '';
	  }

	  document.getElementById('type_of_eggs').value = data.type_of_eggs || '';
	  document.getElementById('sale').value = data.sale || '';
	  document.getElementById('sale_price').value = data.sale_price || '';
	  document.getElementById('remarks').value = data.remarks || '';
	}

	function setSheadSelect(sheadValue) {
	  const select = document.getElementById('shead_name');
	  const valueToMatch = (sheadValue || '').trim().toLowerCase();

	  for (let option of select.options) {
		if (option.value.trim().toLowerCase() === valueToMatch) {
		  select.value = option.value;
		  return;
		}
	  }

	  select.value = '';
	}



  function closeModal() {
    document.getElementById('entryModal').style.display = 'none';
  }

  document.getElementById("addNewBtn").addEventListener("click", () => {
    openEditModal({});
  });

  window.onclick = function (event) {
    const modal = document.getElementById('entryModal');
    if (event.target == modal) {
      closeModal();
    }
  }

  // 🚀 Handle Form Submission via Fetch
  document.getElementById("entryForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const id = document.getElementById("form_id").value;
    const shead_name = document.getElementById("shead_name").value;
    const no_of_trays = parseInt(document.getElementById('no_of_trays').value) || 0;
	const no_of_loose_eggs = parseInt(document.getElementById('no_of_loose_eggs').value) || 0;
	// ✅ You need to add this line
	const no_of_eggs = (no_of_trays * 30) + no_of_loose_eggs;
    const type_of_eggs = document.getElementById("type_of_eggs").value;
    const sale = document.getElementById("sale").value;
    const sale_price = document.getElementById("sale_price").value;
    const remarks = document.getElementById("remarks").value;
	const entry_date = document.getElementById("entry_date").value;

	const payload = {
	  id,
	  client_id: clientId,
	  date: entry_date,
	  shead_name,
	  no_of_eggs,
	  type_of_eggs,
	  sale,
	  sale_price,
	  remarks,
	};
    try {
      const response = await fetch(saveApiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const result = await response.json();
      if (result.success || result.status === "success") {
        alert("Entry saved successfully!");
        closeModal();
        fetchEggData(); // Refresh table
      } else {
        alert("Failed to save: " + (result.message || "Unknown error"));
      }
    } catch (error) {
      console.error("Save error:", error);
      alert("Something went wrong while saving!");
    }
  });

  document.getElementById("damageEggsBtn").addEventListener("click", () => {
		document.getElementById("damageModal").style.display = "block";

		document.getElementById("damage_entry_date").value =
			new Date().toISOString().split('T')[0];
	});

function closeDamageModal() {
    document.getElementById("damageModal").style.display = "none";
}

window.addEventListener("click", function (event) {
  const entryModal = document.getElementById("entryModal");
  const damageModal = document.getElementById("damageModal");

  if (event.target === entryModal) {
    closeModal();
  }
  if (event.target === damageModal) {
    closeDamageModal();
  }
});

  document.getElementById("damageForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const shead_name = document.getElementById("damage_shead_name").value;
    const no_of_trays = parseInt(document.getElementById("damage_no_of_trays").value) || 0;
    const no_of_loose_eggs = parseInt(document.getElementById("damage_no_of_loose_Eggs").value) || 0;
    const sale = document.getElementById("damage_sale").value;
	const entry_date = document.getElementById("damage_entry_date").value;

    const no_of_eggs = (no_of_trays * 30) + no_of_loose_eggs;

    const payload = {
	  client_id: clientId,
	  date: entry_date,
	  shead_name,
	  no_of_eggs,
	  sale,
	};

    try {
      const response = await fetch("https://sunfra.com/farm/sunfra/egg_godown/egg_godown_sale_damage_save.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const result = await response.json();
      if (result.success || result.status === "success") {
        alert("Damage egg entry saved successfully!");
        closeDamageModal();
        fetchEggData(); 
      } else {
        alert("Failed to save damage entry: " + (result.message || "Unknown error"));
      }
    } catch (error) {
      console.error("Damage save error:", error);
      alert("Error while saving damage entry.");
    }
  });const sidebar = document.getElementById('sidebar');
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
	  function applyFilters() {
		  const query = document.getElementById('searchInput').value.toLowerCase();
		  const filterDateStr = document.getElementById('filterDate').value;
		  const filterDate = filterDateStr ? new Date(filterDateStr) : null;

		  const filtered = allRecords.filter(item => {
			// Search filtering
			const matchesSearch = !query || (
			  (item.timestamp && item.timestamp.toLowerCase().includes(query)) ||
			  (item.shead_name && item.shead_name.toLowerCase().includes(query)) ||
			  (item.type_of_eggs && item.type_of_eggs.toLowerCase().includes(query)) ||
			  (item.sale && item.sale.toLowerCase().includes(query))
			);
			if (!matchesSearch) return false;

			// Date filtering (if date selected)
			if (!filterDate) return true; // no date filter

			if (!item.timestamp) return false;
			const itemDateStr = item.timestamp.split(' ')[0];
			const itemDate = new Date(itemDateStr);

			return itemDate.getFullYear() === filterDate.getFullYear() &&
				   itemDate.getMonth() === filterDate.getMonth() &&
				   itemDate.getDate() === filterDate.getDate();
		  });

		  renderTable(filtered);
		}

</script>

</body>
</html>
