<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tractor Production Mortality</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f3f4f6;
      margin: 0;
      padding: 0;
    }

    .header {
      background: linear-gradient(90deg, #4f46e5, #3b82f6);
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 28px;
      font-weight: bold;
    }

    .container {
      padding: 15px;
      max-width: 1200px;
      margin: auto;
    }

    .filter-section {
      margin-bottom: 15px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      justify-content: space-between;
    }

    .filter-left {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

	.filter-right {
	  display: flex;
	  justify-content: flex-end;  
	  width: auto;
	  margin-bottom: 0;
	}

	@media (max-width: 600px) {
	  .filter-right {
		justify-content: center;   
		width: 100%;
		margin-bottom: 10px;
		order: -1;
	  }

	  .add-btn {
		width: 100%;  
	  }
	}

	.add-btn {
	  padding: 12px 20px;
	  font-size: 16px;
	  border-radius: 8px;
	  background-color: #10b981;
	  color: white;
	  border: none;
	  cursor: pointer;
	  transition: background-color 0.3s;
	}

	.add-btn:hover {
	  background-color: #059669;
	}

    input[type="date"], input[type="number"], input[type="text"] {
      padding: 10px 14px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background-color: white;
      box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
      font-size: 14px;
      color: #374151;
    }

    .button, .add-btn, .edit-btn {
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
      font-size: 14px;
    }

    .button { background-color: #3b82f6; color: white; }
    .button:hover { background-color: #2563eb; }

    .add-btn { background-color: #10b981; color: white; }
    .add-btn:hover { background-color: #059669; }

    .edit-btn { background-color: #f59e0b; color: white; font-size: 12px; padding: 6px 10px; }
    .edit-btn:hover { background-color: #d97706; }

    .summary-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .card {
      padding: 16px;
      border-radius: 10px;
      color: white;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .card:nth-child(1) { background: #3b82f6; }
    .card:nth-child(2) { background: #10b981; }
    .card:nth-child(3) { background: #f59e0b; }
    .card:nth-child(4) { background: #ef4444; }

    .table-container {
      overflow-x: auto;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    table { width: 100%; border-collapse: collapse; min-width: 750px; }
    th, td { padding: 10px 12px; text-align: center; border-bottom: 1px solid #f1f1f1; font-size: 14px; }
    th { background-color: #e5e7eb; font-weight: bold; }
    tbody tr:nth-child(even) { background-color: #f9fafb; }
    tbody tr:hover { background-color: #e0f2fe; }

    @media (max-width: 400px) {
      .filter-section { flex-direction: column; align-items: center; }
      .filter-left, .filter-right { width: 100%; justify-content: center; }
      .filter-right { order: -1; margin-bottom: 10px; }
    }
	 .modal {
		display: none;
		position: fixed;
		top: 0; left: 0; right: 0; bottom: 0;
		background-color: rgba(0, 0, 0, 0.5);
		z-index: 999;
		justify-content: center;
		align-items: center;
		padding: 10px;
	  }
	  .modal-content {
		background-color: white;
		padding: 20px;
		border-radius: 10px;
		max-width: 500px;
		width: 100%;
		box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
	  }
	  .modal-content h2 {
		margin-top: 0;
		text-align: center;
		font-size: 22px;
		margin-bottom: 15px;
	  }
	  .form-group {
		display: flex;
		flex-direction: column;
		margin-bottom: 12px;
	  }
	  .form-group label {
		margin-bottom: 6px;
		font-size: 14px;
		color: #374151;
	  }
	  .form-group input {
		padding: 10px;
		border: 1px solid #d1d5db;
		border-radius: 6px;
		font-size: 14px;
	  }

	  .form-buttons {
		display: flex;
		justify-content: space-between;
		gap: 10px;
		margin-top: 15px;
	  }

	  .form-buttons button {
		flex: 1;
		padding: 10px;
		border: none;
		border-radius: 6px;
		cursor: pointer;
		font-size: 14px;
	  }

	  .submit-btn {
		background-color: #10b981;
		color: white;
	  }

	  .submit-btn:hover {
		background-color: #059669;
	  }

	  .close-btn {
		background-color: #ef4444;
		color: white;
	  }

	  .close-btn:hover {
		background-color: #dc2626;
	  }

	  @media (max-width: 500px) {
		.modal-content {
		  padding: 15px;
		}

		.form-buttons {
		  flex-direction: column;
		}
	  }
	  @media (max-width: 500px) {
		  .add-btn {
			width: 100%;
		  }
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

<div class="header">🥚 Tractor Production Mortality
</div>
<div style="text-align: left; padding: 10px 20px;">
  <button onclick="window.location.href='https://sunfra.com/farm/test/test_dashboard.php';"
          style="padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">
    ← Back
  </button>
</div>


<div class="container">
  <div class="filter-section">
    <div class="filter-right">
  <button class="add-btn" onclick="openAddModal()">+ Add New</button>
  
</div>

    <div class="filter-left">
      <input type="date" id="dateFilter">
      <button class="button" onclick="filterByDate()">Filter</button>
    </div>
  </div>

  <div class="summary-cards">
    <div class="card"><h3>Total Production</h3><p id="totalProduction">0</p></div>
    <div class="card"><h3>Total Egg Trays</h3><p id="totalEggTrays">0</p></div>
    <div class="card"><h3>Total Loose Eggs</h3><p id="totalLooseEggs">0</p></div>
    <div class="card"><h3>Total Mortality</h3><p id="totalMortality">0</p></div>
  </div>

  <div class="table-container">
    <table id="productionTable">
      <thead>
        <tr>
          <th>Date</th>
          <th>Shed No</th>
          <th>Production</th>
          <th>Egg Trays</th>
          <th>Loose Eggs</th>
          <th>Mortality</th>
          <th>Batch ID</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<style>
  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    justify-content: center;
    align-items: center;
    padding: 10px;
  }

  .modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  }

  .modal-content h2 {
    margin-top: 0;
    text-align: center;
    font-size: 22px;
    margin-bottom: 15px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 12px;
  }

  .form-group label {
    margin-bottom: 6px;
    font-size: 14px;
    color: #374151;
  }

  .form-group input {
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
  }

  .form-buttons {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 15px;
  }

  .form-buttons button {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
  }

  .submit-btn {
    background-color: #10b981;
    color: white;
  }

  .submit-btn:hover {
    background-color: #059669;
  }

  .close-btn {
    background-color: #ef4444;
    color: white;
  }

  .close-btn:hover {
    background-color: #dc2626;
  }

  @media (max-width: 500px) {
    .modal-content {
      padding: 15px;
    }

    .form-buttons {
      flex-direction: column;
    }
  }
  @media (min-width: 700px) {
  .modal-content {
    max-width: 700px;
  }
}

</style>

<div class="modal" id="dataModal">
  <div class="modal-content">
    <h2>Add / Edit Data</h2>
    <form id="dataForm">
      <input type="hidden" name="id" id="formId">

      <div class="form-group">
        <label for="formDate">Date</label>
        <input type="date" name="date" id="formDate" required>
      </div>

      <div class="form-group">
        <label for="formSheadNo">Shed No</label>
        <input type="number" name="sheadNo" id="formSheadNo" required placeholder="Enter Shed Number">
      </div>

      <div class="form-group">
        <label for="formEggTrays">Egg Trays</label>
        <input type="number" name="eggTrays" id="formEggTrays" required placeholder="Enter Egg Trays">
      </div>

      <div class="form-group">
        <label for="formLooseEggs">Loose Eggs</label>
        <input type="number" name="looseEggs" id="formLooseEggs" required placeholder="Enter Loose Eggs">
      </div>

      <div class="form-group">
        <label for="formMortality">Mortality</label>
        <input type="number" name="mortality" id="formMortality" required placeholder="Enter Mortality">
      </div>

      <div class="form-buttons">
        <button type="submit" class="submit-btn">Submit</button>
        <button type="button" class="close-btn" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>
</main>
</div>

<script>
	const clientId = <?php echo json_encode($client_id); ?>;
	let allData = [];
	let editingId = null;

	function fetchData() {
	  fetch('https://sunfra.com/farm/test/tractor_production_mortality_json.php')
		.then(response => response.json())
		.then(data => {
		  allData = data[clientId] || [];
		  setTodayDate();
		  filterByDate();
		});
	}

	function setTodayDate() {
	  const today = new Date().toISOString().split('T')[0];
	  document.getElementById('dateFilter').value = today;
	}

	function filterByDate() {
	  const selectedDate = document.getElementById('dateFilter').value;
	  const filteredData = allData.filter(item => item.date === selectedDate);
	  displayData(filteredData);
	  updateSummary(filteredData);
	}

	function updateSummary(dataArray) {
	  let totalProduction = 0, totalEggTrays = 0, totalLooseEggs = 0, totalMortality = 0;
	  dataArray.forEach(item => {
		totalProduction += Number(item.production);
		totalEggTrays += Number(item.eggTrays);
		totalLooseEggs += Number(item.looseEggs);
		totalMortality += Number(item.mortality);
	  });
	  document.getElementById('totalProduction').innerText = totalProduction;
	  document.getElementById('totalEggTrays').innerText = totalEggTrays;
	  document.getElementById('totalLooseEggs').innerText = totalLooseEggs;
	  document.getElementById('totalMortality').innerText = totalMortality;
	}

	function displayData(dataArray) {
	  const tableBody = document.querySelector('#productionTable tbody');
	  tableBody.innerHTML = '';
	  if (dataArray.length === 0) {
		tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center; color: #9ca3af;">No data found for selected date</td></tr>`;
		return;
	  }
	  dataArray.forEach(item => {
		const row = `
		  <tr>
			<td>${item.date}</td>
			<td>${item.sheadNo}</td>
			<td>${item.production}</td>
			<td>${item.eggTrays}</td>
			<td>${item.looseEggs}</td>
			<td>${item.mortality}</td>
			<td>${item.batch_id}</td>
			<td>
			  <button class="edit-btn" onclick="openModal(${JSON.stringify(item).replace(/"/g, '&quot;')})">Edit</button>
			</td>
		  </tr>
		`;
		tableBody.insertAdjacentHTML('beforeend', row);
	  });
	}

	  function openModal(existingData = null) {
		document.getElementById('dataModal').style.display = 'flex';
		if (existingData) {
		  // Prefill for edit
		  document.getElementById('formId').value = existingData.id || '';
		  document.getElementById('formDate').value = existingData.date || '';
		  document.getElementById('formSheadNo').value = existingData.sheadNo || '';
		  document.getElementById('formEggTrays').value = existingData.eggTrays || '';
		  document.getElementById('formLooseEggs').value = existingData.looseEggs || '';
		  document.getElementById('formMortality').value = existingData.mortality || '';
		} else {
		  document.getElementById('dataForm').reset();
		  document.getElementById('formId').value = '';
		}
	  }

	  function closeModal() {
		document.getElementById('dataModal').style.display = 'none';
	  }

	  document.getElementById('dataForm').addEventListener('submit', function(event) {
		event.preventDefault();

		const formData = {
		  id: document.getElementById('formId').value,
		  date: document.getElementById('formDate').value,
		  sheadNo: document.getElementById('formSheadNo').value,
		  eggTrays: document.getElementById('formEggTrays').value,
		  looseEggs: document.getElementById('formLooseEggs').value,
		  mortality: document.getElementById('formMortality').value,
		  client_id: clientId 
		};

		fetch('https://sunfra.com/farm/test/tractor_production_mortality_save.php', {
		  method: 'POST',
		  headers: { 'Content-Type': 'application/json' },
		  body: JSON.stringify(formData)
		})
		.then(response => response.json())
		.then(result => {
		  alert(result.message);
		  closeModal();
		  fetchData();  // Reload table after submit
		})
		.catch(error => {
		  console.error('Error:', error);
		});
	  });

	function openAddModal() {
	  openModal('add');
	}

	window.onload = fetchData;
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
