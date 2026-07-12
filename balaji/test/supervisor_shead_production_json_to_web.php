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
  <title>Supervisor Shead Production</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap + Chart.js -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #ADD8E6;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 16px;
    }
    h2 {
      font-size: 1.4rem;
    }
    .table th {
      background-color: #4CAF50;
      color: white;
      font-size: 0.8rem;
    }
    .table td {
      font-size: 0.8rem;
    }
    .edit-btn {
      color: #007bff;
      text-decoration: none;
    }
    .edit-btn:hover {
      text-decoration: underline;
    }
    .add-btn {
      width: 100%;
      margin-top: 10px;
    }
    @media (min-width: 576px) {
      .add-btn {
        float: right;
        width: auto;
        margin-top: 0;
      }
    }
    .card {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      padding: 15px;
      margin-bottom: 20px;
    }
	@media (min-width: 768px) {
	  #productionChart {
		max-height: 300px;
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

<div class="container mt-3">
	  	<a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="back-button">← Go Back</a>

  <h2 class="text-center mb-4">Supervisor Shead Production</h2>

  <div class="row mb-3">
    <div class="col-12 col-md-2 mb-2 mb-md-0 d-flex align-items-center">
      <label for="dateFilter" class="me-2 fw-semibold">📅 Date:</label>
      <input type="date" id="dateFilter" class="form-control" value="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="col-12 col-md-10 text-md-end">
      <a href="#" class="btn btn-success add-btn" data-bs-toggle="modal" data-bs-target="#entryModal">+ Add New Entry</a>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-bordered table-striped text-center align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Shead No</th>
            <th>Trays</th>
            <th>Loose</th>
            <th>Production</th>
            <th>Damaged</th>
            <th>Time</th>
            <th>Edit</th>
          </tr>
        </thead>
        <tbody id="dataBody">
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mt-3">
    <label for="graphFilter" class="fw-semibold mb-2 px-3 pt-3">📊 View Production Summary:</label>
    <div class="px-3">
      <select class="form-select mb-3" id="graphFilter">
        <option value="today">Today's Production</option>
        <option value="yesterday">Yesterday's Production</option>
        <option value="weekly">Weekly Production</option>
        <option value="monthly">Monthly Production</option>
        <option value="yearly">Yearly Production</option>
      </select>
    </div>
    <div class="d-flex justify-content-center pb-3 px-2">
      <div style="width: 100%; max-width: 100%; overflow-x: auto;">
        <canvas id="productionChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
	const clientId = "<?php echo $_SESSION['client_id']; ?>";

	document.addEventListener("DOMContentLoaded", function () {
	  const apiUrl = "https://sunfra.com/farm/test/supervisor_shead_production_json.php";
	  const dataBody = document.getElementById("dataBody");
	  const dateInput = document.getElementById("dateFilter");
	  const graphFilter = document.getElementById("graphFilter");
	  const chartCanvas = document.getElementById("productionChart");
	  let chart;

	  function getFormattedDate(offsetDays = 0) {
		const d = new Date();
		d.setDate(d.getDate() + offsetDays);
		return d.toISOString().split("T")[0];
	  }

	  function isWithinRange(dateStr, type) {
		  const today = new Date();
		  const target = new Date(dateStr);
		  switch (type) {
			case "today":
			  return target.toDateString() === today.toDateString();
			case "yesterday":
			  const y = new Date();
			  y.setDate(today.getDate() - 1);
			  return target.toDateString() === y.toDateString();
			case "weekly":
			  const w = new Date();
			  w.setDate(today.getDate() - 6);
			  return target >= w && target <= today;
			case "monthly":
			  const past30 = new Date();
			  past30.setDate(today.getDate() - 30);
			  return target >= past30 && target <= today;
			case "yearly":
			  return target.getFullYear() === today.getFullYear();
		  }
		  return false;
		}

	  function renderChart(data, type) {
		const sheadData = {1:0,2:0,3:0,4:0,5:0,6:0,7:0,8:0};

		data.forEach(row => {
		  if (isWithinRange(row.timestamp, type)) {
			const num = parseInt(row.sheadNo.replace(/\D/g, ""));
			if (sheadData[num] !== undefined) {
			  sheadData[num] += parseInt(row.no_of_trays);
			}
		  }
		});

		const labels = Object.keys(sheadData);
		const values = Object.values(sheadData);

		if (chart) chart.destroy();

		chart = new Chart(chartCanvas, {
		  type: 'bar',
		  data: {
			labels,
			datasets: [{
			  label: "Trays Count (Shead-wise)",
			  data: values,
			  backgroundColor: 'rgba(13, 110, 253, 0.7)',
			  borderColor: 'rgba(13, 110, 253, 1)',
			  borderWidth: 1
			}]
		  },
		  options: {
			responsive: true,
			scales: {
			  y: { beginAtZero: true }
			},
			plugins: {
			  legend: { display: false }
			}
		  }
		});
	  }

	  function populateTable(data, date) {
		const filtered = data.filter(row => row.timestamp.startsWith(date));
		dataBody.innerHTML = filtered.length === 0
		  ? `<tr><td colspan="8" class="text-danger">No data found for selected date.</td></tr>`
		  : filtered.map(row => `
			<tr>
			  <td>${row.id}</td>
			  <td>${row.sheadNo}</td>
			  <td>${row.no_of_trays}</td>
			  <td>${row.no_of_loose_eggs}</td>
			  <td>${row.production}</td>
			  <td>${row.no_of_damaged_eggs}</td>
			  <td>${row.timestamp}</td>
			  <td><a class="edit-btn" href="#" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#entryModal">Edit</a></td>
			</tr>`).join("");

		setupEditButtons();
	  }

		function fetchData(selectedDate = getFormattedDate(), rangeType = "today") {
		  fetch(apiUrl)
			.then(response => response.json())
			.then(data => {
			  const rows = data[clientId] || [];
			  populateTable(rows, selectedDate);
			  renderChart(rows, rangeType);
			})
			.catch(err => {
			  console.error(err);
			  dataBody.innerHTML = `<tr><td colspan="8" class="text-danger">Error loading data.</td></tr>`;
			});
		}

	  const today = getFormattedDate();
	  fetchData(today, "today");

	  dateInput.addEventListener("change", () => {
		fetchData(dateInput.value, graphFilter.value);
	  });

	  graphFilter.addEventListener("change", () => {
		fetchData(dateInput.value, graphFilter.value);
	  });

	  document.getElementById("entryForm").addEventListener("submit", function (e) {
		e.preventDefault();

		const form = e.target;
		const formData = new FormData(form);

		fetch("https://sunfra.com/farm/test/supervisor_shead_production_save.php", {
		  method: "POST",
		  body: formData,
		})
		  .then(response => response.json())
		  .then(result => {
			if (result.status === "success") {
			  const modal = bootstrap.Modal.getInstance(document.getElementById("entryModal"));
			  modal.hide();

			  form.reset();
			  document.getElementById("entryModalLabel").innerText = "➕ Add New Entry";
			  document.getElementById("formId").value = "";

			  fetchData(document.getElementById("dateFilter").value, document.getElementById("graphFilter").value);
			} else {
			  alert("❌ Error: " + result.message);
			}
		  })
		  .catch(error => {
			console.error("Save error:", error);
			alert("❌ Network error. Please try again.");
		  });
	  });

	});

	function setupEditButtons() {
	  document.querySelectorAll(".edit-btn").forEach(btn => {
		btn.addEventListener("click", function (e) {
		  e.preventDefault();

		  const id = this.dataset.id;
		  const row = this.closest("tr");
		  const cells = row.querySelectorAll("td");

		  document.getElementById("entryModalLabel").innerText = "✏️ Edit Entry";

		  document.getElementById("formId").value = id;
		  document.getElementById("sheadNo").value = cells[1].innerText.trim();
		  document.getElementById("no_of_trays").value = cells[2].innerText.trim();
		  document.getElementById("no_of_loose_eggs").value = cells[3].innerText.trim();
		  document.getElementById("no_of_damaged_eggs").value = cells[5].innerText.trim();
		});
	  });
	}
</script>

<div class="modal fade" id="entryModal" tabindex="-1" aria-labelledby="entryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="entryModalLabel">➕ Add New Entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="entryForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="formId">
			<input type="hidden" name="client_id" id="clientId" value="<?php echo $client_id; ?>">
          <div class="mb-2">
            <label for="sheadNo" class="form-label">Shead No</label>
            <select id="sheadNo" name="sheadNo" class="form-select" required>
              <option value="">Select option</option>
              <option value="Shead_1">Shead_1</option>
              <option value="Shead_2">Shead_2</option>
              <option value="Shead_3">Shead_3</option>
              <option value="Shead_4">Shead_4</option>
              <option value="Shead_5">Shead_5</option>
              <option value="Shead_6">Shead_6</option>
              <option value="Shead_7">Shead_7</option>
              <option value="Shead_8">Shead_8</option>
            </select>
          </div>

          <div class="mb-2">
            <label for="no_of_trays" class="form-label">No of Trays</label>
            <input type="number" name="no_of_trays" id="no_of_trays" class="form-control" required>
          </div>

          <div class="mb-2">
            <label for="no_of_loose_eggs" class="form-label">Loose Eggs</label>
            <input type="number" name="no_of_loose_eggs" id="no_of_loose_eggs" class="form-control" required>
          </div>

          <div class="mb-2">
            <label for="no_of_damaged_eggs" class="form-label">Damaged Eggs</label>
            <input type="number" name="no_of_damaged_eggs" id="no_of_damaged_eggs" class="form-control" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary w-100">✅ Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
