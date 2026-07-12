<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$json_url = "https://sunfra.com/farm/test/batch_json.php";
$json_data = file_get_contents($json_url);
$response = json_decode($json_data, true);

$shed_keys = array_merge(range(1, 8), ['Chick', 'Grower']);

$shed_data = [];
foreach ($shed_keys as $shedKey) {
    $shed_data[$shedKey] = [
        'total_live_birds' => 0,
        'batch_ids' => [],
        'max_running_weeks' => 0,
    ];
}

$client_id = $_SESSION['client_id'] ?? 0;

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

            if (is_numeric($shedNo) && $shedNo >= 1 && $shedNo <= 8) {
                $shed_data[(int)$shedNo]['total_live_birds'] += $liveBirds;
                $shed_data[(int)$shedNo]['batch_ids'][] = $batch['batch_id'];
                $shed_data[(int)$shedNo]['max_running_weeks'] = max(
                    $shed_data[(int)$shedNo]['max_running_weeks'],
                    $runningWeeks
                );
            }

            $shedNoLower = strtolower($shedNo);
            if ($shedNoLower === 'chick' || $shedNoLower === 'grower') {
                $shedKey = ucfirst($shedNoLower);
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
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
		}.sidebar {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 260px;
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
		  margin-left: 64px;
		}

		/* MOBILE view - sidebar off-canvas */
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
		}
	</style>
</head>
<body>

<div class="d-flex">

	<aside id="sidebar" class="sidebar bg-blue-800 text-white p-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-xl font-semibold sidebar-text"><?= htmlspecialchars($clientName) ?></h2>
		<button id="collapse-btn" class="text-white">
		  <i class="fas fa-angle-double-left"></i>
		</button>
      </div>

      <nav class="space-y-2">
        <a href="https://sunfra.com/farm/test/test_dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-home"></i> <span class="sidebar-text">Home</span>
        </a>
        <a href="https://sunfra.com/farm/test/batch_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-globe"></i> <span class="sidebar-text">Batch</span>
        </a>
        <a href="https://sunfra.com/farm/test/weighbridge_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-truck"></i> <span class="sidebar-text">WeighBridge</span>
        </a>
        <a href="https://sunfra.com/farm/test/tractor_production_mortality_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-tractor"></i> <span class="sidebar-text">Tractor Production Mortality</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_attendance.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-check"></i> <span class="sidebar-text">Attendance</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-tie"></i> <span class="sidebar-text">Shead Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-warehouse"></i> <span class="sidebar-text">Feed Plant Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-egg"></i> <span class="sidebar-text">Egg Godown Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-chart-line"></i> <span class="sidebar-text">Profit And Loss</span>
        </a>
        <a href="https://sunfra.com/farm/test/settings.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-sliders-h"></i> <span class="sidebar-text">Feature Settings</span>
        </a>
        <a href="https://sunfra.com" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-life-ring"></i> <span class="sidebar-text">Support</span>
        </a>
        <a href="https://sunfra.com/farm/test/logout.php" class="flex items-center gap-3 p-2 rounded hover:bg-red-600">
          <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text">Logout</span>
        </a>
      </nav>
    </aside>

<main class="main-content" style="width:100%;">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="mt-4 mb-4">Batch</h2>
		<div>
			<a href="https://sunfra.com/farm/test/test_dashboard.php" class="btn btn-primary me-2">Home</a>
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
				  <option value="1">Shead 1</option>
				  <option value="2">Shead 2</option>
				  <option value="3">Shead 3</option>
				  <option value="4">Shead 4</option>
				  <option value="5">Shead 5</option>
				  <option value="6">Shead 6</option>
				  <option value="7">Shead 7</option>
				  <option value="8">Shead 8</option>
				  <option value="Chick">Chick</option>
				  <option value="Grower">Grower</option>
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

            const formData = new FormData(this);

            if (!formData.get('cullDate')) {
                formData.set('cullDate', '0000-00-00');
            }

            const apiUrl = 'https://sunfra.com/farm/test/batch_save.php';  

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
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.querySelector('.main-content');
  const collapseBtn = document.getElementById('collapse-btn');

  collapseBtn?.addEventListener('click', function () {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    const icon = this.querySelector('i');
    if (icon) {
      icon.classList.toggle('fa-angle-double-left');
      icon.classList.toggle('fa-angle-double-right');
    }
  });

  menuBtn?.addEventListener('click', function () {
    sidebar.classList.toggle('show');
  });
});

</script>
</body>
</html>
