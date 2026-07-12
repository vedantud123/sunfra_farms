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
    <title>ðŸŒ± Labour Attendance</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

   <h1>🌱 Labour Attendance</h1>

<div style="margin-bottom: 20px;">
    <a href="https://sunfra.com/farm/test/test_show_attendance.php" 
       style="
            background-color: #6b7280;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
        ">
       ← Back
    </a>
</div>

<div class="button-container">
    <button onclick="toggleForm()">➕ Add New Entry</button>
</div>

	<div id="formModal" class="modal">
		<div class="modal-content">
			<span class="close-btn" onclick="toggleForm()">&times;</span>
			<h2 id="formTitle">Labour Attendance</h2>
			 <form id="attendanceForm" method="POST" action="https://sunfra.com/farm/test/labour_attendance_save.php">
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
					<option value="">Select Place</option>
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
					<option value="Feed_Godown">Feed_Godown</option>
					<option value="Egg_godown">Egg_godown</option>
					<option value="Gate_Manager">Gate_Manager</option>
					<option value="Others">Others</option>
				</select>

				<button type="submit">Submit</button>
			</form>
		</div>
	</div>

    <div class="filters">
        <select id="dateFilter">
            <option value="">Filter by Date</option>
        </select>

        <select id="statusFilter">
            <option value="">Filter by Status</option>
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
        </select>

        <input type="text" id="searchName" placeholder="Search by Name...">
    </div>

    <div class="grid-container" id="attendanceGrid">
    </div>
</main>
	</div>
    <script>
        let allData = [];
		const clientId = parseInt(<?php echo json_encode($_SESSION['client_id'] ?? "0"); ?>);

        function loadCards(filteredData) {
			const grid = document.getElementById('attendanceGrid');
			grid.innerHTML = '';

			filteredData.forEach(item => {
				const statusClass = item.status.toLowerCase() === 'present' ? 'present' : 'absent';
				const card = document.createElement('div');
				card.className = 'card';
				card.innerHTML = `
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<h3 style="margin: 0;">${item.name}</h3>
						<button class="action-btn inline-edit" onclick="openEditForm('${item.id}', '${item.name}', '${item.status}', '${item.working_place}')">Edit</button>	
					</div>
					<p><strong>Date:</strong> ${item.date}</p>
					<p><strong>Time:</strong> ${item.timestamp}</p>
					<p><strong>Working Place:</strong> ${item.working_place}</p>
					<p><span class="status ${statusClass}">${item.status}</span></p>
				`;
				grid.appendChild(card);
			});
		}

        function applyFilters() {
            const selectedDate = document.getElementById('dateFilter').value;
            const selectedStatus = document.getElementById('statusFilter').value.toLowerCase();
            const searchText = document.getElementById('searchName').value.toLowerCase();

            let filteredData = allData;

            if (selectedDate) {
                filteredData = filteredData.filter(item => item.date === selectedDate);
            }
            if (selectedStatus) {
                filteredData = filteredData.filter(item => item.status.toLowerCase() === selectedStatus);
            }
            if (searchText) {
                filteredData = filteredData.filter(item => item.name.toLowerCase().includes(searchText));
            }

            loadCards(filteredData);
        }
		fetch('https://sunfra.com/farm/test/labour_attendance_json.php')
			.then(response => response.json())
			.then(data => {
				allData = data[clientId] || [];  // âœ… filter by client ID

				const uniqueDates = [...new Set(allData.map(item => item.date))];
				const dateFilter = document.getElementById('dateFilter');
				dateFilter.innerHTML = '<option value="">Filter by Date</option>';

				uniqueDates.forEach(date => {
					const option = document.createElement('option');
					option.value = date;
					option.textContent = date;
					dateFilter.appendChild(option);
				});

				const today = new Date().toISOString().split('T')[0];
				if (uniqueDates.includes(today)) {
					dateFilter.value = today;
					applyFilters();
				} else {
					loadCards(allData);
				}
			})
			.catch(error => console.error('Error fetching attendance data:', error));


        document.getElementById('dateFilter').addEventListener('change', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
        document.getElementById('searchName').addEventListener('input', applyFilters);
		function toggleForm() {
					const modal = document.getElementById('formModal');
					modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
				}
				window.onclick = function(event) {
					const modal = document.getElementById('formModal');
					if (event.target === modal) {
						modal.style.display = "none";
					}
				}
				function openEditForm(id, name, status, working_place) {
			toggleForm(); 
			document.getElementById('entryId').value = id;
			document.getElementById('name').value = name;
			document.getElementById('status').value = status;
			document.getElementById('working_place').value = working_place;

			document.getElementById('attendanceForm').action = 'https://sunfra.com/farm/test/labour_attendance_save.php';
		}
		 fetch('https://sunfra.com/farm/test/labour_master_json.php')
			.then(response => response.json())
			.then(data => {
				const nameSelect = document.getElementById('name');
				nameSelect.innerHTML = '<option value="">Select Name</option>';

				const clientData = data[clientId] || []; // âœ… Only get names for client

				clientData.forEach(item => {
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
