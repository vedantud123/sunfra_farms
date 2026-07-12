<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor Feed Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
		}
    </style>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar { transition: all 0.3s ease; }
    .sidebar a:hover { background-color: #2b6cb0; color: white; }
    .collapsed { width: 64px !important; }
    .collapsed .sidebar-text { display: none; }
    .collapsed nav a { justify-content: center; }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .sidebar.show { display: block; }
    }
  </style>
</head>
<?php session_start(); ?>
<?php $clientName = $_SESSION['client_name'] ?? 'Yours'; ?>
<body class="bg-gray-100 text-gray-800">

<div class="container-fluid">
    <div class="dashboard-container">
		<a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="back-button">← Go Back</a>

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
						<th>ID</th>
						<th>Shead</th>
						<th>Box 1</th>
						<th>Box 2</th>
						<th>Box 3</th>
						<th>Box 4</th>
						<th>Box 5</th>
						<th>Box 6</th>
						<th>Box 7</th>
						<th>Box 8</th>
						<th>Box 9</th>
						<th>Box 10</th>
						<th>Total</th>
						<th>Time</th>
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
              <option value="Shead_1">Shead_1</option>
              <option value="Shead_2">Shead_2</option>
              <option value="Shead_3">Shead_3</option>
              <option value="Shead_4">Shead_4</option>
              <option value="Shead_5">Shead_5</option>
              <option value="Shead_6">Shead_6</option>
              <option value="Shead_7">Shead_7</option>
              <option value="Shead_8">Shead_8</option>
              <option value="Chick">Chick</option>
              <option value="Grower">Grower</option>
            </select>
          </div>

          <div class="row">
            <?php for ($i = 1; $i <= 10; $i++) { ?>
              <div class="col-6 mb-3">
                <label for="Box_<?php echo $i; ?>">Box <?php echo $i; ?></label>
                <input type="text" class="form-control" id="Box_<?php echo $i; ?>" name="Box_<?php echo $i; ?>">
              </div>
            <?php } ?>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

   <script>
	let fullData = [];
	const clientId = <?php echo json_encode($client_id); ?>;

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
			$('#entryId').val('');
			for (let i = 1; i <= 10; i++) {
				$('#Box_' + i).val(0);
			}
			const myModal = new bootstrap.Modal(document.getElementById('entryModal'));
			myModal.show();
		});

		$('#entryForm').submit(function (e) {
			e.preventDefault();

			// Validate Box fields
			let isValid = true;
			for (let i = 1; i <= 10; i++) {
				const val = $('#Box_' + i).val();
				if (val !== '' && isNaN(val)) {
					alert(`Box ${i} must be a number.`);
					isValid = false;
					break;
				}
			}
			if (!isValid) return;

			$.ajax({
				url: 'https://sunfra.com/farm/test/supervisor_feed_feeding_shead_save.php',
				method: 'POST',
				data: $('#entryForm').serialize(),
				dataType: 'json',
				success: function (response) {
					if (response.status === "success") {
						alert(response.message);
						window.location.href = 'https://sunfra.com/farm/test/supervisor_feed_feeding_shead_json_to_web.php';
					} else if (response.status === "exists") {
						alert(response.message);
					} else {
						alert('Error: ' + response.message);
					}
				},
				error: function (xhr, status, error) {
					console.error("Submission error:", error);
					console.error("Response:", xhr.responseText);
					alert('Error while submitting data.');
				}
			});
		});
	});

	function loadData(filterDate = '') {
		$.ajax({
			url: "https://sunfra.com/farm/test/supervisor_feed_feeding_shead_json.php?client_id=" + clientId,
			method: "GET",
			dataType: "json",
			success: function (response) {
				fullData = response[clientId] || [];

				let rows = '';
				const feedByShead = {};

				fullData.forEach(function (item) {
					const rowDate = item.timestamp.substring(0, 10);
					if (filterDate === '' || filterDate === rowDate) {

						let total = 0;
						for (let i = 1; i <= 10; i++) {
							total += parseFloat(item['Box_' + i]) || 0;
						}

						if (!feedByShead[item.sheadNo]) {
							feedByShead[item.sheadNo] = 0;
						}
						feedByShead[item.sheadNo] += total;

						rows += `<tr>
							<td>${item.id}</td>
							<td>${item.sheadNo}</td>
							<td>${item.Box_1}</td>
							<td>${item.Box_2}</td>
							<td>${item.Box_3}</td>
							<td>${item.Box_4}</td>
							<td>${item.Box_5}</td>
							<td>${item.Box_6}</td>
							<td>${item.Box_7}</td>
							<td>${item.Box_8}</td>
							<td>${item.Box_9}</td>
							<td>${item.Box_10}</td>
							<td>${total.toFixed(2)}</td>
							<td>${item.timestamp}</td>
							<td>
								<button class="btn btn-sm btn-success" onclick="openEditModal(${item.id})">
									<i class="fas fa-edit"></i> Edit
								</button>
							</td>
						</tr>`;
					}
				});

				if (!rows) {
					rows = '<tr><td colspan="15" class="text-center text-danger">No data for selected date.</td></tr>';
				}

				$('#dataBody').html(rows);
				renderChart(feedByShead);
			},
			error: function (xhr, status, error) {
				console.error("Data load error:", error);
				console.error("Response:", xhr.responseText);
				$('#dataBody').html('<tr><td colspan="15" class="text-center text-danger">Error fetching data.</td></tr>');
			}
		});
	}

	function openEditModal(id) {
		$('#entryModalLabel').text('Edit Entry');
		const item = fullData.find(entry => entry.id == id);
		if (item) {
			$('#entryId').val(item.id);
			$('#sheadNo').val(item.sheadNo);
			for (let i = 1; i <= 10; i++) {
				$('#Box_' + i).val(item['Box_' + i]);
			}
			const myModal = new bootstrap.Modal(document.getElementById('entryModal'));
			myModal.show();
		} else {
			alert('Data not found for this ID.');
		}
	}

	let feedChart;
	function renderChart(feedData) {
		const ctx = document.getElementById('feedChart').getContext('2d');

		const sheadLabels = [
			"Shead_1", "Shead_2", "Shead_3", "Shead_4",
			"Shead_5", "Shead_6", "Shead_7", "Shead_8",
			"Chick", "Grower"
		];

		const values = sheadLabels.map(label => feedData[label] || 0);

		if (feedChart) feedChart.destroy();

		feedChart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: sheadLabels,
				datasets: [{
					label: 'Total Feed Per Shead',
					data: values,
					backgroundColor: [
						'#4bc0c0', '#36a2eb', '#ffcd56', '#ff6384',
						'#9966ff', '#00c49f', '#ff9f40', '#b19cd9',
						'#c45850', '#66bb6a'
					],
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
</script>


<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
