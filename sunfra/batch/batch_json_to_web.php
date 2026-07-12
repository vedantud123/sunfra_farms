<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
if (empty($username)) {
    header("Location: ../login/login.php");
    exit;
}

$api_url = "https://sunfra.com/farm/sunfra/login/farm_users_list.php";
$response = file_get_contents($api_url);

if ($response === false) {
    header("Location: https://sunfra.com/farm/sunfra/index.php");
    exit;
}

$users = json_decode($response, true);

$is_admin = false;
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

if (!$is_admin) {
    header("Location: https://sunfra.com/farm/sunfra/index.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

$json_url = "https://sunfra.com/farm/sunfra/batch/batch_json.php?client_id=" . urlencode($client_id);

$json_data = file_get_contents($json_url);
$response = json_decode($json_data, true);

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
        $name = $item['shead_name'];

        if (stripos($name, 'Shead') === 0) {
            preg_match('/\d+/', $name, $matches);
            if (!empty($matches)) {
                $shead_list[] = "Shead " . (int)$matches[0];
            }
        } else {
            $shead_list[] = $name;
        }
    }
}

$shed_data = [];
foreach ($shead_list as $shedKey) {
    $shed_data[$shedKey] = [
        'total_live_birds' => 0,
        'batch_ids' => [],
        'max_running_weeks' => 0,
    ];
}

if (!empty($response) && isset($response[$client_id])) {
    foreach ($response[$client_id] as $batch) {
        if ($batch['cullDate'] === "0000-00-00" || empty($batch['cullDate'])) {
            $shedNo = trim($batch['sheadNo']);
            $liveBirds = isset($batch['live_birds']) ? (int)$batch['live_birds'] : 0;

            $runningWeeks = 0;
            $duration = $batch['duration'] ?? '';
            if (preg_match('/(\d+)\s+week\(s\)/', $duration, $matches)) {
                $runningWeeks = (int)$matches[1];
            }

            if (preg_match('/^\d+$/', $shedNo)) {
                $shedKey = "Shead " . $shedNo;
            } else {
                $shedKey = ucfirst(strtolower($shedNo));
            }

            if (isset($shed_data[$shedKey])) {
                $shed_data[$shedKey]['total_live_birds'] += $liveBirds;
                $shed_data[$shedKey]['batch_ids'][] = $batch['batch_id'];
                $shed_data[$shedKey]['max_running_weeks'] = max(
                    $shed_data[$shedKey]['max_running_weeks'],
                    $runningWeeks
                );
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Shed-wise Live Birds & Running Weeks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
		body {
			background-color: #E6BBAD;
			font-family: 'Poppins', sans-serif;
			color: #333;
		}

		.container {
			background-color: #ADD8E6;
			border-radius: 16px;
			padding: 25px;
			box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
			margin-bottom: 20px;
		}

		.shed-card {
			border-radius: 14px;
			box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
			transition: transform 0.2s ease, box-shadow 0.2s ease;
			background: linear-gradient(145deg, #ffffff, #f0f0f0);
		}

		.shed-card:hover {
			transform: translateY(-6px);
			box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
		}

		.shed-card h5 {
			color: #0d6efd;
			font-weight: 600;
		}

		.shed-card p {
			font-size: 0.95rem;
			margin-bottom: 8px;
		}

		.chart-container {
			margin-top: 20px;
			background: #ffffff;
			border-radius: 16px;
			padding: 25px;
			box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
			overflow-x: auto;
		}

		.chart-responsive {
			position: relative;
			width: 100%;
			height: 300px;
			overflow-x: auto;
		}

		canvas {
			width: 100% !important;
			height: auto !important;
		}

		.container + .container {
			margin-top: 20px;
		}

		.form-card {
			background: #ffffff;
			border-radius: 18px;
			padding: 30px 25px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
			margin-top: 50px;
			transition: transform 0.2s ease, box-shadow 0.2s ease;
		}

		.form-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
		}

		.form-title {
			color: #00796B;
			font-weight: 600;
			margin-bottom: 20px;
		}

		.form-control {
			border-radius: 8px;
			box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
		}

		.form-control:focus {
			border-color: #00796B;
			box-shadow: 0 0 5px rgba(0, 121, 107, 0.5);
		}

		.btn-custom {
			background: linear-gradient(45deg, #00796B, #004D40);
			color: white;
			border-radius: 8px;
			padding: 10px 20px;
			font-weight: 500;
			border: none;
		}

		.btn-custom:hover {
			background: linear-gradient(45deg, #004D40, #00332e);
		}

		.btn-close {
			background-color: #f8d7da;
			border-radius: 50%;
			width: 30px;
			height: 30px;
		}

		#responseMsg .alert {
			padding: 8px 12px;
			border-radius: 8px;
			margin-top: 10px;
		}

		@media (max-width: 576px) {
			h2, h4 {
				font-size: 1.2rem;
			}

			.shed-card h5, .shed-card p {
				font-size: 0.85rem;
			}

			.shed-card {
				padding: 12px;
			}

			.form-card {
				padding: 20px 15px;
			}

			.btn-custom {
				width: 100%;
				padding: 10px 0;
			}
		}.edit-btn {
			font-size: 0.75rem;
			padding: 4px 8px;
			border-radius: 8px;
			box-shadow: 0 2px 6px rgba(0,0,0,0.1);
		}
		.edit-btn i {
			margin-right: 4px;
		}/* Sidebar Styles */
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

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="mt-4 mb-4">Batch</h2>
		<div>
			<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBatchModal">Add Batch</button>
		</div>
	</div>
	
	<div class="modal fade" id="addBatchModal" tabindex="-1" aria-labelledby="addBatchModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <form id="addBatchForm">
			
			<div class="modal-header bg-success text-white">
			  <h5 class="modal-title" id="addBatchModalLabel">Add New Batch</h5>
			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
			  <div class="mb-3">
				<label for="batch_id" class="form-label">Batch ID</label>
				<input type="text" class="form-control" id="batch_id" name="batch_id" placeholder="Enter Batch ID" required>
			  </div>

			  <div class="mb-3">
				<label for="breed" class="form-label">Breed</label>
				<input type="text" class="form-control" id="breed" name="breed" placeholder="Enter Breed" required>
			  </div>

			  <div class="mb-3">
				<label for="hatchDate" class="form-label">Hatch Date</label>
				<input type="date" class="form-control" id="hatchDate" name="hatchDate" required>
			  </div>

			  <div class="mb-3">
				<label for="noOfChicks" class="form-label">Number of Chicks</label>
				<input type="number" class="form-control" id="noOfChicks" name="noOfChicks" placeholder="Enter Number of Chicks" required>
			  </div>

			  <div class="mb-3">
				  <label for="sheadNo" class="form-label">Shed Number</label>
				  <select class="form-select" id="sheadNo" name="sheadNo" required>
					<option value="">Select Shed</option>
					<?php
					  foreach ($shead_list as $shed) {
						  echo '<option value="' . htmlspecialchars($shed) . '">' . htmlspecialchars($shed) . '</option>';
					  }
					?>
				  </select>
				</div>
			  <div class="mb-3">
				<label for="cullDate" class="form-label">Cull Date (Optional)</label>
				<input type="date" class="form-control" id="cullDate" name="cullDate">
			  </div>

			  <div class="mb-3">
				<label for="live_birds" class="form-label">Live Birds</label>
				<input type="number" class="form-control" id="live_birds" name="live_birds" placeholder="Enter Live Birds" required>
			  </div>

			  <div id="responseMsg" class="mt-2 text-center"></div>
			</div>
			<input type="hidden" name="mode" id="mode" value="add">
			<input type="hidden" name="old_batch_id" id="old_batch_id" value="">
			<input type="hidden" name="client_id" id="client_id" value="<?php echo $client_id; ?>">

			<div class="modal-footer">
			  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-success" id="saveBatchBtn">Save Batch</button>
			</div>
		  </form>
		</div>
	  </div>
	</div>

    <div class="row g-4">
		<?php foreach ($shed_data as $shedKey => $data): ?>
			<div class="col-12 col-sm-6 col-md-4 col-lg-3">
				<div class="shed-card bg-white p-3 position-relative">
				   <?php if (!empty($data['batch_ids'])): ?>
						<?php 
							$latestBatchId = end($data['batch_ids']);
							$latestBatch = null;

							if (!empty($response[$client_id])) {
								foreach ($response[$client_id] as $batch) {
									if ($batch['batch_id'] == $latestBatchId) {
										$latestBatch = $batch;
										break;
									}
								}
							}
						?>

						<?php if ($latestBatch): ?>
							<button class="btn btn-sm btn-warning position-absolute top-0 end-0 m-2 edit-btn"
								data-batchid="<?php echo $latestBatch['batch_id']; ?>"
								data-breed="<?php echo $latestBatch['breed']; ?>"
								data-hatchdate="<?php echo $latestBatch['hatchDate']; ?>"
								data-noofchicks="<?php echo $latestBatch['noOfChicks']; ?>"
								data-sheadno="<?php echo $latestBatch['sheadNo']; ?>"
								data-culldate="<?php echo $latestBatch['cullDate']; ?>"
								data-livebirds="<?php echo $latestBatch['live_birds']; ?>"
							>
								<i class="bi bi-pencil-square"></i> Edit
							</button>
						<?php endif; ?>
					<?php endif; ?>


					<h5><strong><?php echo is_numeric($shedKey) ? "Shed No: $shedKey" : $shedKey; ?></strong></h5>
					<p><strong>Total Live Birds:</strong> <span class="badge bg-success"><?php echo $data['total_live_birds']; ?></span></p>
					<p><strong>Running Weeks:</strong> <span class="badge bg-primary"><?php echo $data['max_running_weeks']; ?> Weeks</span></p>
					<p><strong>Batch IDs:</strong> 
						<?php echo (!empty($data['batch_ids'])) ? implode(', ', $data['batch_ids']) : '<span class="text-muted">None</span>'; ?>
					</p>

					<?php if ($latestBatch): ?>
						<p><strong>Hatch Date:</strong> 
							<span class="badge bg-primary">
								<?php echo date("d M, Y", strtotime($latestBatch['hatchDate'])); ?>
							</span>
						</p>
						<p><strong>No. of Chicks:</strong> 
							<span class="badge bg-warning text-dark">
								<?php echo $latestBatch['noOfChicks']; ?>
							</span>
						</p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

</div>

<div class="container mt-3">
    <div class="chart-container">
        <h4 class="mb-3">Live Birds & Running Weeks</h4>
        <div class="chart-responsive">
            <canvas id="shedChart"></canvas>
        </div>
    </div>
</div>
</main>
</div>

<script>
    const shedLabels = <?php echo json_encode(array_map(function($key) {
        return is_numeric($key) ? "$key" : $key;
    }, array_keys($shed_data))); ?>;

    const liveBirdsData = <?php echo json_encode(array_column($shed_data, 'total_live_birds')); ?>;
    const runningWeeksData = <?php echo json_encode(array_column($shed_data, 'max_running_weeks')); ?>;

    const ctx = document.getElementById('shedChart').getContext('2d');
    const shedChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: shedLabels,
            datasets: [
                {
                    label: 'Live Birds',
                    data: liveBirdsData,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Max Running Weeks',
                    data: runningWeeksData,
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
       options: {
			responsive: true,
			maintainAspectRatio: false,
			scales: {
				x: {
					ticks: {
						autoSkip: window.innerWidth <= 768 ? false : true,  
						maxRotation: window.innerWidth <= 768 ? 60 : 0,     
						minRotation: window.innerWidth <= 768 ? 30 : 0,
						color: '#000'
					}
				},
				y: {
					beginAtZero: true,
					grid: {
						color: function(context) {
							const value = context.tick.value;
							return (value % 10 === 0) ? '#FF5733' : 'rgba(0,0,0,0.1)';
						},
						lineWidth: function(context) {
							return (context.tick.value % 10 === 0) ? 0.5 : 0.5;
						}
					},
					ticks: {
						stepSize: 10,
						color: '#000'
					}
				}
			},
			plugins: {
				legend: {
					position: 'top'
				}
			}
		}
    });

   document.addEventListener('DOMContentLoaded', function() {
	const addBtn = document.querySelector('button[data-bs-target="#addBatchModal"]');
	if (addBtn) {
	  addBtn.addEventListener('click', () => {
		const form = document.getElementById('addBatchForm');
		form.reset();

		document.getElementById('mode').value = 'add';
		document.getElementById('old_batch_id').value = '';

		document.getElementById('saveBatchBtn').innerText = 'Save Batch';
		document.getElementById('addBatchModalLabel').innerText = 'Add New Batch';

		document.getElementById('responseMsg').innerHTML = '';

		const myModal = new bootstrap.modal(document.getElementById('addBatchModal'));
		myModal.show();
	  });
	}

    const openAddBtn = document.getElementById('openAddBatchModal');
    if (openAddBtn) {
        openAddBtn.addEventListener('click', function() {
            document.getElementById('addBatchForm').reset();
            document.getElementById('mode').value = 'add';
            document.getElementById('saveBatchBtn').innerText = 'Save Batch';
            document.getElementById('addBatchModalLabel').innerText = 'Add New Batch';
            new bootstrap.Modal(document.getElementById('addBatchModal')).show();
        });
    }

    document.querySelectorAll('.edit-btn').forEach(function(button) {
		button.addEventListener('click', function() {
			document.getElementById('batch_id').value = this.getAttribute('data-batchid');
			document.getElementById('breed').value = this.getAttribute('data-breed');
			document.getElementById('hatchDate').value = this.getAttribute('data-hatchdate');
			document.getElementById('noOfChicks').value = this.getAttribute('data-noofchicks');
			document.getElementById('sheadNo').value = this.getAttribute('data-sheadno');
			document.getElementById('cullDate').value = this.getAttribute('data-culldate');
			document.getElementById('live_birds').value = this.getAttribute('data-livebirds');

			document.getElementById('mode').value = 'update';
			document.getElementById('old_batch_id').value = this.getAttribute('data-batchid');

			document.getElementById('saveBatchBtn').innerText = 'Update Batch';
			document.getElementById('addBatchModalLabel').innerText = 'Edit Batch';

			var myModal = new bootstrap.Modal(document.getElementById('addBatchModal'));
			myModal.show();
		});
	});


    const batchForm = document.getElementById('addBatchForm');
    if (batchForm) {
       batchForm.addEventListener('submit', function(e) {
		e.preventDefault();

		const batchIdInput = document.getElementById('batch_id').value.trim();
		const mode = document.getElementById('mode').value; // 'add' or 'update'

		const existingBatchIDsNormalized = existingBatchIDs.map(id => String(id).trim());

		if (mode === 'add' && existingBatchIDsNormalized.includes(batchIdInput)) {
			alert(`Batch ID "${batchIdInput}" already exists! Please enter a unique Batch ID.`);
			document.getElementById('batch_id').focus();
			return;
		}
		
		const formData = new FormData(this);

		if (!formData.get('cullDate')) {
			formData.set('cullDate', '0000-00-00');
		}

		const apiUrl = 'https://sunfra.com/farm/sunfra/batch/batch_save.php';  

		fetch(apiUrl, {
			method: 'POST',
			body: formData
		})
		.then(response => response.json())
		.then(data => {
			const msgDiv = document.getElementById('responseMsg');
			if (data.status === 'success') {
				msgDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
				setTimeout(() => location.reload(), 1500);
			} else {
				msgDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
			}
		})
		.catch(error => {
			console.error('Error:', error);
			document.getElementById('responseMsg').innerHTML = `<div class="alert alert-danger">Something went wrong!</div>`;
		});
	});

    }

});
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
  
const existingBatchIDs = <?php 
    $batch_ids = [];
    if (isset($response[$client_id])) {
      foreach ($response[$client_id] as $batch) {
        $batch_ids[] = $batch['batch_id'];
      }
    }
    echo json_encode($batch_ids, JSON_HEX_TAG); 
  ?>;
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

</body>
</html>
