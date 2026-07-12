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
  <title>Birds Weekly Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #ADD8E6;
      margin: 0;
      padding: 20px;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    h1 {
      color: #333;
    }

    .controls {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-top: 10px;
    }

    select, input[type="text"] {
      padding: 8px 12px;
      font-size: 16px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
	  background-color: #10b981; /* Green background */
	  color: white;              /* White text */
	  padding: 10px 20px;        /* Padding around text */
	  font-size: 16px;           /* Text size */
	  border: none;              /* Remove default border */
	  border-radius: 8px;        /* Rounded corners */
	  cursor: pointer;           /* Pointer cursor on hover */
	  transition: background-color 0.3s ease; /* Smooth hover transition */
	}

	button:hover {
	  background-color: #059669; /* Darker green on hover */
	}

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      margin-top: 20px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
      border-radius: 8px;
    }

    th, td {
      padding: 10px;
      text-align: center;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #4CAF50;
      color: white;
    }

    .week-title {
      margin-top: 25px;
      font-weight: bold;
      font-size: 18px;
    }

    .hidden {
      display: none;
    }

    .modal {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      visibility: hidden;
      opacity: 0;
      transition: 0.3s ease;
    }

    .modal.active {
      visibility: visible;
      opacity: 1;
    }

    .modal-content {
      background: white;
      padding: 20px 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      position: relative;
      animation: scaleIn 0.3s ease-in-out;
    }

    @keyframes scaleIn {
      from {
        transform: scale(0.8);
        opacity: 0;
      }
      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .modal-content h2 {
      margin-top: 0;
    }

    .modal-content label {
      display: block;
      margin-top: 12px;
    }

    .modal-content input[type="number"], .modal-content select {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .modal-content button, .modal-content input[type="submit"] {
      margin-top: 15px;
      margin-right: 10px;
    }

    .no-data {
      text-align: center;
      color: red;
    }

    .success {
      text-align: center;
      color: green;
      margin-top: 10px;
    }

    .error {
      text-align: center;
      color: red;
      margin-top: 10px;
    }.form-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 15px 20px;
	  }

	  .form-group {
		display: flex;
		flex-direction: column;
	  }

	  .form-group label {
		font-weight: 500;
		margin-bottom: 6px;
		color: #333;
	  }

	  .form-group input,
	  .form-group select {
		padding: 10px;
		font-size: 15px;
		border: 1px solid #ccc;
		border-radius: 8px;
		outline: none;
		background-color: #f9f9f9;
		transition: 0.3s ease;
	  }

	  .form-group input:focus,
	  .form-group select:focus {
		border-color: #4CAF50;
		background-color: #fff;
		box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
	  }

	  .form-group.full {
		grid-column: span 2;
	  }

	  .form-actions {
		grid-column: span 2;
		text-align: center;
		margin-top: 20px;
	  }

	  .submit-btn {
		background-color: #4CAF50;
		border: none;
		color: white;
		padding: 10px 25px;
		font-size: 16px;
		border-radius: 6px;
		cursor: pointer;
		margin-right: 10px;
	  }

	  .cancel-btn {
		background-color: #999;
		border: none;
		color: white;
		padding: 10px 25px;
		font-size: 16px;
		border-radius: 6px;
		cursor: pointer;
	  }

	  .submit-btn:hover {
		background-color: #45a049;
	  }

	  .cancel-btn:hover {
		background-color: #777;
	  }@media (max-width: 768px) {
		  header {
			flex-direction: column;
			align-items: flex-start;
			gap: 10px;
		  }

		  .controls {
			flex-direction: column;
			align-items: stretch;
			width: 100%;
		  }

		  .controls select,
		  .controls input[type="text"],
		  .controls button {
			width: 95%;
		  }

		  table {
			display: block;
			overflow-x: auto;
			white-space: nowrap;
		  }

		  .modal-content {
			width: 80% !important;
			padding: 15px;
		  }

		  .form-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr); 
			gap: 15px 20px;
		  }

		  .form-group {
			width: 100%;
		  }

		  .form-group.full {
			grid-column: span 2;
		  }

		  .form-group input,
		  .form-group select {
			width: 100%;
			box-sizing: border-box;
		  }

		  .form-actions {
			grid-column: span 2;
			text-align: center;
			margin-top: 20px;
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
		}.add-data-btn {
		  background-color: #10b981;
		  color: white;
		  padding: 10px 20px;
		  font-size: 16px;
		  border: none;
		  border-radius: 8px;
		  cursor: pointer;
		  transition: background-color 0.3s ease;
		}

		.add-data-btn:hover {
		  background-color: #059669;
		}
		.sidebar {
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
		}
  </style>
</head>
<body>
<div>
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
	<main class="main-content">
  <header>
  	<a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="back-button">← Go Back</a>
    <h1>Birds Weekly Report</h1>
    <div class="controls">
      <select id="monthSelect"></select>
      <input type="text" id="searchInput" placeholder="Search..">
		<button class="add-data-btn" onclick="toggleModal()">Add New Data</button>
    </div>
  </header>

  <div id="reportContainer"></div>

  <div class="modal" id="formModal">
	  <div class="modal-content">
		<h2 style="text-align:center; margin-bottom: 20px;">Add Bird Weights</h2>
		<form id="birdForm" class="form-grid">
		  <div class="form-group full">
			<label for="sheadNo">Shead No</label>
			<select id="sheadNo" name="sheadNo" required>
			  <option value="">Select Shead</option>
			  <option>Shead_1</option>
			  <option>Shead_2</option>
			  <option>Shead_3</option>
			  <option>Shead_4</option>
			  <option>Shead_5</option>
			  <option>Shead_6</option>
			  <option>Shead_7</option>
			  <option>Shead_8</option>
			  <option>Chick</option>
			  <option>Grower</option>
			</select>
		  </div>

		  <div class="form-group"><label>Bird 1</label><input type="number" name="bird1" required></div>
		  <div class="form-group"><label>Bird 2</label><input type="number" name="bird2" required></div>
		  <div class="form-group"><label>Bird 3</label><input type="number" name="bird3" required></div>
		  <div class="form-group"><label>Bird 4</label><input type="number" name="bird4" required></div>
		  <div class="form-group"><label>Bird 5</label><input type="number" name="bird5" required></div>
		  <div class="form-group"><label>Bird 6</label><input type="number" name="bird6" required></div>
		  <div class="form-group"><label>Bird 7</label><input type="number" name="bird7" required></div>
		  <div class="form-group"><label>Bird 8</label><input type="number" name="bird8" required></div>

		  <div class="form-actions">
			<input type="submit" value="Submit" class="submit-btn">
			<button type="button" onclick="toggleModal()" class="cancel-btn">Cancel</button>
			<div id="formMessage" style="margin-top:10px;"></div>
		  </div>
		</form>
	  </div>
	</div>
</main>
</div>
  <script>
    const apiUrl = "https://sunfra.com/farm/test/supervisor_birds_weight_json.php";
    const saveApi = "https://sunfra.com/farm/test/supervisor_birds_weight_save.php";

    const reportContainer = document.getElementById("reportContainer");
    const monthSelect = document.getElementById("monthSelect");
    const searchInput = document.getElementById("searchInput");
    const modal = document.getElementById("formModal");
    const birdForm = document.getElementById("birdForm");
    const formMessage = document.getElementById("formMessage");

    let groupedData = {};

    function toggleModal() {
	  modal.classList.toggle("active");
	  formMessage.innerHTML = '';
	  birdForm.reset();
	}

    function getMonthKey(dateStr) {
      const date = new Date(dateStr);
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    }

    function getWeekNumber(day) {
      return Math.ceil(day / 7);
    }

    function formatMonthYear(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleString('default', { month: 'long', year: 'numeric' });
    }

    function groupByMonthWeek(data) {
      const result = {};
      data.forEach(item => {
        const date = new Date(item.timestamp);
        const monthKey = getMonthKey(item.timestamp);
        const week = `Week ${getWeekNumber(date.getDate())}`;
        if (!result[monthKey]) result[monthKey] = {};
        if (!result[monthKey][week]) result[monthKey][week] = [];
        result[monthKey][week].push(item);
      });
      return result;
    }

    function populateMonthDropdown(months) {
      monthSelect.innerHTML = '';
      months.sort().reverse().forEach(monthKey => {
        const option = document.createElement("option");
        option.value = monthKey;
        option.textContent = formatMonthYear(`${monthKey}-01`);
        monthSelect.appendChild(option);
      });
    }

    function renderReport(monthKey) {
      reportContainer.innerHTML = '';
      const weeks = groupedData[monthKey];
      if (!weeks) {
        reportContainer.innerHTML = '<p class="no-data">No data available for this month.</p>';
        return;
      }

      Object.keys(weeks).sort().forEach(week => {
        const rows = weeks[week];
        let tableHTML = `<div class="week-title">${week}</div><table class="searchable-table">
          <thead><tr>
            <th>Shead No</th>
            <th>Bird 1</th><th>Bird 2</th><th>Bird 3</th><th>Bird 4</th>
            <th>Bird 5</th><th>Bird 6</th><th>Bird 7</th><th>Bird 8</th>
            <th>Average</th><th>Timestamp</th>
          </tr></thead><tbody>`;

        rows.forEach(row => {
          tableHTML += `<tr>
            <td>${row.sheadNo}</td>
            <td>${row.bird1}</td><td>${row.bird2}</td><td>${row.bird3}</td><td>${row.bird4}</td>
            <td>${row.bird5}</td><td>${row.bird6}</td><td>${row.bird7}</td><td>${row.bird8}</td>
            <td><strong>${row.birds_average}</strong></td>
            <td>${row.timestamp}</td>
          </tr>`;
        });

        tableHTML += '</tbody></table>';
        reportContainer.innerHTML += tableHTML;
      });
    }

    function applySearch() {
      const term = searchInput.value.toLowerCase();
      const rows = document.querySelectorAll(".searchable-table tbody tr");
      rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
      });
    }

    birdForm.addEventListener("submit", function (e) {
	  e.preventDefault();
	  const formData = new FormData(birdForm);

	  // 👇 Append client_id from PHP session
	  formData.append("client_id", "<?php echo $client_id; ?>");

	  fetch(saveApi, {
		method: "POST",
		body: formData
	  })
	  .then(res => res.text())
	  .then(response => {
		formMessage.innerHTML = `<div class="success">Data saved successfully!</div>`;
		setTimeout(() => {
		  toggleModal();
		  fetchData(); // refresh the data
		}, 1000);
	  })
	  .catch(err => {
		console.error(err);
		formMessage.innerHTML = `<div class="error">Failed to save data.</div>`;
	  });
	});

   function fetchData() {
	  fetch(apiUrl)
		.then(res => res.json())
		.then(json => {
		  const clientId = "<?php echo $client_id; ?>"; // Get PHP client_id into JS
		  const data = json[clientId] || []; // Use client_id key to get specific data
		  groupedData = groupByMonthWeek(data);
		  populateMonthDropdown(Object.keys(groupedData));
		  renderReport(monthSelect.value);
		});
	}


    fetchData();
    monthSelect.addEventListener("change", () => renderReport(monthSelect.value));
    searchInput.addEventListener("input", applySearch);
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
