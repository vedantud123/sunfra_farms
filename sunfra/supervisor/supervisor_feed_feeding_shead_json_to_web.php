<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
 
$current_feature = "Shead Supervisor"; // Set per page
 
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
$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=$client_id";
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

foreach ($shead_data as $item) {
    if (isset($item['shead_name'])) {
        $shead_list[] = str_replace(' ', '_', $item['shead_name']);
    }
}

$box_api_url = "https://sunfra.com/farm/sunfra/configuration/config_shead_box_json.php?client_id=" . $client_id;

$box_json = file_get_contents($box_api_url);

$box_data = json_decode($box_json, true);

$shead_box_list = [];

if (isset($box_data[$client_id])) {
    foreach ($box_data[$client_id] as $box_entry) {
        $shead_box_list[] = $box_entry['box_numbers'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor Feed Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       html, body {
			background: linear-gradient(135deg, #74ebd5, #ACB6E5);
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			margin: 0;
			padding: 0;
			min-height: 100vh;
			height: 100%;
			overflow-x: hidden;
		}body::after {
			content: "";
			display: block;
			height: 1px;
		}
		.dashboard-container {
			background: white;
			border-radius: 15px;
			box-shadow: 0 8px 20px rgba(0,0,0,0.2);
			padding: 15px;
			margin: 10px;
		}

		.table thead {
			background-color: #007BFF;
			color: white;
		}

		.table tbody tr:hover {
			background-color: #f9f9f9;
		}

		.form-control, .form-select {
			border-radius: 10px;
		}

		.btn-custom,
		#filterBtn,
		#addNewEntry {
			padding: 6px 10px;
			font-size: 13px;
			width: 100%; 
			border-radius: 20px;
		}

		#dateFilter {
			font-size: 13px;
			padding: 6px 10px;
		}

		h2 {
			font-size: 20px;
			text-align: center;
		}

		.table th, .table td {
			font-size: 12px;
			padding: 4px 6px;
		}

		.row.mb-3 {
			margin-bottom: 10px !important;
		}

		@media (min-width: 768px) {
			#filterBtn,
			#addNewEntry {
				padding: 6px 16px;
				font-size: 14px;
				width: auto;
				min-width: 120px;
			}

			#dateFilter {
				font-size: 14px;
				padding: 6px 12px;
			}

			h2 {
				font-size: 26px;
				text-align: center;
			}

			.table th, .table td {
				font-size: 14px;
				padding: 8px 10px;
			}
		}.chart-container {
		  display: flex;
		  justify-content: center;
		  align-items: center;
		  padding: 10px;
		  overflow-x: auto;
		  max-width: 100%;
		}
		.chart-card {
		  width: 100%;
		  max-width: 1000px;
		}
		.card-body {
		  padding: 28px;
		  position: relative;
		  height: 300px; 
		}

		#feedChart {
		  width: 100% !important;
		  height: 100% !important;
		}


		@media (max-width: 768px) {
			.card-title {
				font-size: 16px;
			}

			#feedChart {
				height: 250px !important;
			}
		}.back-button {
		  display: inline-block;
		  margin-bottom: 1rem;
		  background-color: #3498db;
		  color: white;
		  padding: 10px 16px;
		  border-radius: 6px;
		  text-decoration: none;
		  font-weight: 600;
		  transition: background-color 0.3s ease;
		}

		.back-button:hover {
		  background-color: #217dbb;
		}.sidebar {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 250px;
		  height: 100vh;
		  background: #0d6efd;
		  color: #fff;
		  padding: 20px 10px;
		  transition: width 0.3s;
		  overflow-y: auto;
		  z-index: 1050;
		}

		.sidebar.collapsed {
		  width: 50px !important;
		  padding: 20px 0 !important;
		}

		.sidebar.collapsed .sidebar-text {
		  display: none !important;
		}

		.main-content {
		  margin-left: 250px;
		  transition: margin-left 0.3s;
		}

		.main-content.collapsed {
		  margin-left: 50px;
		}

		@media (max-width: 768px) {
		  .sidebar {
			position: fixed;
			left: 0; top: 0;
			height: 100vh;
			width: 250px;
			transform: translateX(-100%);
			transition: transform 0.3s;
			z-index: 1100;
			background: #0d6efd;
		  }
		  .sidebar.show {
			transform: translateX(0);
		  }
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
		}@media (max-width: 768px) {
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
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
  <!--<a href="https://sunfra.com/farm/sunfra/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>-->
  <a href="https://sunfra.com/farm/sunfra/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
<div class="container-fluid">
    <div class="dashboard-container">

        <h2 class="mb-4 text-center text-primary"><i class="fas fa-seedling"></i> Supervisor Feed Dashboard</h2>

        <div class="row g-2 align-items-end mb-3 flex-wrap">
			<div class="col-12 col-md-4">
				<label for="dateFilter" class="form-label">Select Date <small class="text-muted">(YYYY-MM-DD)</small></label>
				<input type="date" id="dateFilter" class="form-control" />
			</div>

			<div class="col-6 col-md-4 d-flex">
				<button id="filterBtn" class="btn btn-success w-10">
					<i class="fas fa-filter"></i> Filter
				</button>
			</div>

			<div class="col-6 col-md-4 d-flex justify-content-md-end">
				<button id="addNewEntry" class="btn btn-primary w-10">
					<i class="fas fa-plus-circle"></i> Add New
				</button>
			</div>
		</div>


        <div class="table-responsive" style="max-height: 500px;">
            <table class="table table-bordered table-hover text-center">
               <thead>
				  <tr>
					<th>Shead</th>

					<?php
					$box_api_url = "https://sunfra.com/farm/sunfra/configuration/config_shead_box_json.php?client_id=" . $client_id;
					$box_json = file_get_contents($box_api_url);
					$box_data = json_decode($box_json, true);
					$shead_box_list = [];

					if (isset($box_data[$client_id])) {
						foreach ($box_data[$client_id] as $box_entry) {
							$box_number = $box_entry['box_numbers'];
							$shead_box_list[] = $box_number;
							echo "<th>$box_number</th>";
						}
					}
					?>
					<th>Total</th>
					<th>Date</th>
					<th>Action</th>
				  </tr>
				</thead>
                <tbody id="dataBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="entryModal" tabindex="-1" aria-labelledby="entryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="entryForm">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="entryModalLabel">Add / Edit Entry</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="entryId" name="id">
			<input type="hidden" id="client_id" name="client_id" value="<?php echo $client_id; ?>">
         <div class="mb-3">
			<label for="sheadNo" class="form-label">Shead No</label>
			<select class="form-select" id="sheadNo" name="sheadNo" required>
				<option value="">Select Shead</option>
				<?php
				foreach ($shead_list as $shead_name) {
					echo "<option value=\"$shead_name\">$shead_name</option>";
				}
				?>
			</select>
		</div>
          <div class="row">
			<div id="boxInputs">
			  <?php foreach ($shead_box_list as $box) { ?>
				<div class="col-6 mb-3">
				  <label for="<?php echo $box; ?>"><?php echo $box; ?></label>
				  <input type="text" class="form-control" id="<?php echo $box; ?>" name="<?php echo $box; ?>">
				  </div>
			  <?php } ?>
			  </div>
			</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="chart-container mt-4 mb-5">
  <div class="card shadow chart-card">
    <div class="card-body">
      <h5 class="card-title text-center text-primary"><i class="fas fa-chart-bar"></i> Total Feed Per Shead (Today)</h5>
      <canvas id="feedChart" height="150"></canvas>
    </div>
  </div>
</div>
</main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

   <script>
	let fullData = [];
	const clientId = <?php echo json_encode($client_id); ?>;
	let boxList = [];
	 const sheadLabels = <?php echo json_encode($shead_list); ?>;

	$(document).ready(function () {
		const today = new Date().toISOString().split('T')[0];
		$('#dateFilter').val(today);
		loadData(today);

		$('#filterBtn').click(function () {
			loadData($('#dateFilter').val());
		});

		$('#addNewEntry').click(function () {
			$('#entryModalLabel').text('Add New Entry');
			$('#entryForm')[0].reset();
			$('#sheadNo').prop('readonly', false);
			$('#entryDate').val(today);
			$('#entryDate').prop('readonly', false);
			$('#boxInputs').empty();

			$('#boxInputs').empty();
				for (let i = 0; i < boxList.length; i += 2) {
					let box1 = boxList[i];
					// Check if there's a second box for this row
					let box2 = boxList[i + 1];

					// Build inputs for Box 1 and optionally Box 2
					let row = `<div class="row">
					  <div class="col-6 mb-2">
						<label for="${box1}">${box1}</label>
						<input type="number" step="0.01" class="form-control" id="${box1}" name="${box1}" value="0">
					  </div>`;

					if (box2) {
						row += `
					  <div class="col-6 mb-2">
						<label for="${box2}">${box2}</label>
						<input type="number" step="0.01" class="form-control" id="${box2}" name="${box2}" value="0">
					  </div>`;
					}
					row += '</div>';

					$('#boxInputs').append(row);
				}

			const myModal = new bootstrap.Modal(document.getElementById('entryModal'));
			myModal.show();
		});

		$('#entryForm').submit(function (e) {
			e.preventDefault();

			const sheadNo = $('#sheadNo').val();
			const date = $('#entryDate').val();

			let payload = {
				sheadNo,
				date,
				client_id: clientId
			};

			boxList.forEach(boxNum => {
				payload[boxNum] = parseFloat($('#' + boxNum).val()) || 0;
			});

			$.ajax({
				url: 'https://sunfra.com/farm/sunfra/supervisor/supervisor_feed_feeding_shead_save.php',
				method: 'POST',
				data: payload,
				dataType: 'json',
				success: function (response) {
					if (response.status === "success") {
						alert(response.message);
						$('#entryModal').modal('hide');
						loadData($('#dateFilter').val());
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function (xhr) {
					console.error("Submission error:", xhr.responseText);
					alert('Error while submitting data.');
				}
			});
		});
	});

	function loadData(filterDate = '') {
		const configUrl = "https://sunfra.com/farm/sunfra/configuration/config_shead_box_json.php?client_id=" + clientId;
		const dataUrl = "https://sunfra.com/farm/sunfra/supervisor/supervisor_feed_feeding_shead_json.php?client_id=" + clientId;

		$.getJSON(configUrl, function (boxConfigResponse) {
			boxList = [];
			if (boxConfigResponse[clientId]) {
				boxConfigResponse[clientId].forEach(entry => {
					boxList.push(entry.box_numbers);
				});
			}

			$.getJSON(dataUrl, function (response) {
				fullData = response[clientId] || [];
				let rows = '';
				const feedByShead = {};

				fullData.forEach(function (item) {
					const rowDate = item.date;
					if (filterDate === '' || filterDate === rowDate) {
						let total = parseFloat(item.Total) || 0;
						let boxCells = '';

						boxList.forEach(boxNum => {
							const value = parseFloat(item[boxNum]) || 0;
							boxCells += `<td>${value}</td>`;
						});

						if (!feedByShead[item.sheadNo]) {
							feedByShead[item.sheadNo] = 0;
						}
						feedByShead[item.sheadNo] += total;

						rows += `<tr>
							<td>${item.sheadNo}</td>
							${boxCells}
							<td>${total.toFixed(2)}</td>
							<td>${item.date}</td>
							<td>
								<button class="btn btn-sm btn-primary" onclick="openEditModal('${item.sheadNo}', '${item.date}')">Edit</button>
							</td>
						</tr>`;
					}
				});

				if (!rows) {
					rows = '<tr><td colspan="100%" class="text-center text-danger">No data for selected date.</td></tr>';
				}

				let boxHeaders = '';
				boxList.forEach(boxNum => {
					boxHeaders += `<th>Box ${boxNum}</th>`;
				});

				const tableHead = `
					<tr>
						<th>Shead No</th>
						${boxHeaders}
						<th>Total</th>
						<th>Date</th>
						<th>Actions</th>
					</tr>
				`;

				$('#dataHead').html(tableHead);
				$('#dataBody').html(rows);
				renderChart(feedByShead);
			}).fail(function (xhr) {
				console.error("Data load error:", xhr.responseText);
				$('#dataBody').html('<tr><td colspan="100%" class="text-center text-danger">Error fetching data.</td></tr>');
			});
		}).fail(function (xhr) {
			console.error("Box config load error:", xhr.responseText);
			$('#dataBody').html('<tr><td colspan="100%" class="text-center text-danger">Error loading box configuration.</td></tr>');
		});
	}

	function openEditModal(sheadNo, date) {
		const item = fullData.find(entry => entry.sheadNo == sheadNo && entry.date == date);

		if (item) {
			$('#entryModalLabel').text('Edit Entry');
			$('#sheadNo').val(item.sheadNo).prop('readonly', true);
			$('#entryDate').val(item.date).prop('readonly', true);
			$('#boxInputs').empty();

			boxList.forEach(boxNum => {
				const boxValue = item[boxNum] || 0;
				$('#boxInputs').empty();
				for (let i = 0; i < boxList.length; i += 2) {
					let box1 = boxList[i];
					let box2 = boxList[i + 1];
					let v1 = item[box1] || 0;
					let v2 = box2 ? (item[box2] || 0) : null;

					let row = `<div class="row">
					  <div class="col-6 mb-2">
						<label for="${box1}">${box1}</label>
						<input type="number" step="0.01" class="form-control" id="${box1}" name="${box1}" value="${v1}">
					  </div>`;

					if (box2) {
						row += `<div class="col-6 mb-2">
						<label for="${box2}">${box2}</label>
						<input type="number" step="0.01" class="form-control" id="${box2}" name="${box2}" value="${v2}">
					  </div>`;
					}
					row += '</div>';
					$('#boxInputs').append(row);
				}
			});

			const myModal = new bootstrap.Modal(document.getElementById('entryModal'));
			myModal.show();
		} else {
			alert('Data not found for this Shead and Date.');
		}
	}

	let feedChart;
	function renderChart(feedData) {
    const ctx = document.getElementById('feedChart').getContext('2d');

    const values = sheadLabels.map(label => feedData[label] || 0);

    if (feedChart) feedChart.destroy();

    feedChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sheadLabels,
            datasets: [{
                label: 'Total Feed Per Shead',
                data: values,
                backgroundColor: sheadLabels.map((_, i) => [
                    '#4bc0c0', '#36a2eb', '#ffcd56', '#ff6384',
                    '#9966ff', '#00c49f', '#ff9f40', '#b19cd9',
                    '#c45850', '#66bb6a'
                ][i % 10]), // Dynamically repeat colors if needed
                borderColor: 'rgba(0,0,0,0.1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Feed (Kg)'
                    }
                }
            }
        }
    });
}
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
