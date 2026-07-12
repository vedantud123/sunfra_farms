<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
$client_id = $_SESSION['client_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Feed Material</title>
  <style>
    body {
		  font-family: 'Inter', sans-serif;
		  background-color: #ADD8E6;
		  color: #333;
		  margin: 0;
		  padding: 0;
		}
		.container {
		  max-width: 1200px;
		  margin: auto;
		  padding: 1.5rem;
		}
		.header {
		  display: flex;
		  justify-content: space-between;
		  align-items: flex-start;
		  flex-wrap: wrap;
		  margin-bottom: 2rem;
		}
		.header h1 {
		  font-size: 2.2rem;
		  font-weight: 700;
		  color: #1f2937;
		  margin: 0;
		  display: flex;
		  align-items: center;
		  gap: 0.5rem;
		}
		.header p {
		  color: #6b7280;
		  font-size: 0.875rem;
		}

		.controls {
		  display: flex;
		  flex-direction: column;
		  gap: 0.75rem;
		  margin-bottom: 1.5rem;
		}
		@media (min-width: 640px) {
		  .controls {
			flex-direction: row;
			align-items: stretch;
		  }
		}

		.controls input,
		.controls select,
		.controls a.add-btn {
		  padding: 0.75rem;
		  font-size: 1rem;
		  border: 1px solid #d1d5db;
		  border-radius: 0.5rem;
		  width: 100%;
		  box-sizing: border-box;
		  height: 100%;
		  display: flex;
		  align-items: center;
		  justify-content: center;
		  text-align: center;
		}

		.add-btn {
		  background-color: #4CAF50;
		  color: white;
		  font-weight: 500;
		  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		  text-decoration: none;
		  border: none;
		}
		.add-btn:hover {
		  background-color: #1d4ed8;
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

		.card-grid {
		  display: grid;
		  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
		  gap: 1.5rem;
		}
		.card {
		  background-color: #fff;
		  border: 1px solid #e5e7eb;
		  border-radius: 0.75rem;
		  padding: 1.25rem;
		  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
		  transition: transform 0.2s ease;
		}
		.card:hover {
		  transform: translateY(-4px);
		}
		.card h2 {
		  font-size: 1.25rem;
		  font-weight: 600;
		  margin: 0;
		}
		.material-name {
		  color: #4f46e5;
		}
		.badge {
		  padding: 0.25rem 0.5rem;
		  font-size: 0.75rem;
		  border-radius: 9999px;
		  font-weight: 500;
		  display: inline-block;
		}
		.water { background-color: #dbeafe; color: #1e40af; }
		.feed-med { background-color: #d1fae5; color: #065f46; }
		.raw { background-color: #fef3c7; color: #92400e; }
		.empty {
		  text-align: center;
		  color: #6b7280;
		  margin-top: 2rem;
		}.modal-overlay {
		  position: fixed;
		  top: 0; left: 0;
		  width: 100%; height: 100%;
		  background-color: rgba(0, 0, 0, 0.5);
		  display: none;
		  justify-content: center;
		  align-items: center;
		  z-index: 9999;
		}

		.modal {
		  background: #ffffff;
		  border-radius: 12px;
		  padding: 1.5rem;
		  width: 90%;
		  max-width: 400px;
		  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
		  animation: fadeIn 0.3s ease;
		}

		.modal h2 {
		  margin-top: 0;
		  color: #2563eb;
		  font-size: 1.5rem;
		  text-align: center;
		  margin-bottom: 1rem;
		}

		.form-grid p {
		  display: flex;
		  flex-direction: column;
		  margin-bottom: 1rem;
		}

		.form-grid label {
		  margin-bottom: 0.3rem;
		  font-weight: 600;
		  color: #374151;
		}

		.form-grid input,
		.form-grid select {
		  padding: 0.75rem;
		  border-radius: 8px;
		  border: 1.5px solid #d1d5db;
		  font-size: 1rem;
		  transition: border-color 0.3s;
		}

		.form-grid input:focus,
		.form-grid select:focus {
		  border-color: #2563eb;
		  outline: none;
		  background-color: #f0f9ff;
		}

		.modal-actions {
		  display: flex;
		  justify-content: space-between;
		  margin-top: 1.5rem;
		}

		.submit-btn {
		  background-color: #10b981;
		  color: white;
		  padding: 0.5rem 1.2rem;
		  border: none;
		  border-radius: 8px;
		  font-weight: 600;
		  cursor: pointer;
		}

		.cancel-btn {
		  background-color: #f87171;
		  color: white;
		  padding: 0.5rem 1rem;
		  border: none;
		  border-radius: 8px;
		  font-weight: 500;
		  cursor: pointer;
		}

		.form-message {
		  margin-top: 1rem;
		  font-size: 0.95rem;
		  text-align: center;
		  color: green;
		}

		@keyframes fadeIn {
		  from { opacity: 0; transform: translateY(-10px); }
		  to { opacity: 1; transform: translateY(0); }
		}td, th {
		  padding: 12px;
		  border: 1px solid #e5e7eb;
		  text-align: left;
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
  <a href="https://sunfra.com/farm/test2/index.php"><i class="fas fa-home"></i><span class="label">My Dashboard</span></a>
  <a href="https://sunfra.com/farm/test2/batch/batch_json_to_web.php"><i class="fas fa-globe"></i><span class="label">Batch</span></a>
  <a href="https://sunfra.com/farm/test2/weighbridge/weighbridge_json_to_web.php"><i class="fas fa-truck"></i><span class="label">WeighBridge</span></a>
  <a href="https://sunfra.com/farm/test2/tractor_production_mortality/tractor_production_mortality_json_to_web.php"><i class="fas fa-tractor"></i><span class="label">Tractor Production</span></a>

  <div class="attendance-dropdown">
    <a onclick="toggleAttendance()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-user-check"></i>
      <span class="label">Attendance <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="attendanceSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/labour_master_json_to_web.php'">👷‍♂️ Labour Details</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/labour_attendance_json_to_web.php'">📅 Labour Attendance</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/assigned_master_json_to_web.php'">📝 Assigned Master</button>
    </div>
  </div>
	<div class="attendance-dropdown">
	  <a onclick="toggleShed()" class="attendance-toggle">
		<i class="fas fa-users-cog"></i>
		<span class="label">Shead Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
	  </a>
	  <div class="attendance-submenu" id="shedSubmenu">
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_feed_feeding_shead_json_to_web.php'">🥣 Feed Feeding Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_shead_mortality_json_to_web.php'">💀 Shead Mortality</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_shead_production_json_to_web.php'">📦 Production Shead</button>
		<button onclick="location.href='https://sunfra.com/farm/test2/supervisor/supervisor_birds_weight_json_to_web.php'">🐥 Birds Weight</button>
	  </div>
	</div>
  <div class="attendance-dropdown">
    <a onclick="toggleFeedPlant()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-industry"></i>
      <span class="label">Feed Plant Supervisor <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="feedPlantSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/feed_formula/feed_formula_json_to_web.php'">⚙️ Feed Formula</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/feed_raw_material_json_to_web.php'">📥 Feed Raw Material</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/feed_to_shead_json_to_web.php'">🚚 Feed Material To Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/feedrawmaterial/water_medicine_json_to_web.php'">🧪 Water Medicine</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleEggGodown()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-warehouse"></i>
      <span class="label">Egg Godown <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="eggGodownSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_godown_stock_json_to_web.php'">🥚 Egg Production From Shead</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_godown_sale_json_to_web.php'">💰 Eggs for Sale</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_weight.php'">⚖️ Egg Weight</button>
    	<button onclick="location.href='https://sunfra.com/farm/test2/egg_godown/egg_damaged_json_to_web.php'">⚖️ Weekly Egg Damages</button>
    </div>
  </div>

  <div class="attendance-dropdown">
    <a onclick="toggleProfitLoss()" class="attendance-toggle" href="javascript:void(0)">
      <i class="fas fa-chart-line"></i>
      <span class="label">Profit & Loss <i class="fas fa-caret-down" style="margin-left: 5px;"></i></span>
    </a>
    <div class="attendance-submenu" id="profitLossSubmenu">
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/material_cost_json_to_web.php'">Feed Material Price</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/egg_cutting_json_to_web.php'">Egg Price Per Piece</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/profit_and_loss_daily.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/test2/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/test2/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/test2/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/test2/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
  <div class="container">
    <div class="header">
      <div>
        <h1>🌾Feed Material</h1>
      </div>
    </div>

    <div class="controls">
	  <input id="searchBox" type="text" placeholder="🔍 Search by name...">
	  <select id="typeFilter">
		<option value="All">All Types</option>
		<option value="Water Medicine">💧 Water Medicine</option>
		<option value="Feed Medicine">💊 Feed Medicine</option>
		<option value="Raw Material">🌿 Raw Material</option>
	  </select>
		<a href="#" class="add-btn" onclick="openModal()">➕ Add New</a>

		<div id="modalOverlay" class="modal-overlay">
		  <div class="modal">
			<h2>Add New Material</h2>
			<form id="materialForm" class="form-grid">
				<input type="hidden" name="id" id="materialId">

			  <p>
				<label for="name">Name</label>
				<input type="text" name="name" id="name" required />
			  </p>
			  <p>
				  <label for="stock">Stock</label>
				  <input type="number" name="stock" id="stock" step="0.01" required />
				</p>
			  <p>
				<label for="metric">Metric</label>
				<select name="metric" id="metric" required>
				  <option value="">Select Metric</option>
				  <option value="KG">KG</option>
				  <option value="Lit">Lit</option>
				</select>
			  </p>
			  <p>
				<label for="type">Type</label>
				<select name="type" id="type" required>
				  <option value="">Select Type</option>
				  <option value="Feed Medicine">Feed Medicine</option>
				  <option value="Water Medicine">Water Medicine</option>
				  <option value="Raw Material">Raw Material</option>
				</select>
			  </p>
			  <div class="modal-actions">
				<button type="submit" class="submit-btn">✅ Submit</button>
				<button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
			  </div>
			</form>
			<div id="formMessage" class="form-message"></div>
		  </div>
		</div>

	</div>

	<table id="materialTable" style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;">
	  <thead style="background-color: #f1f5f9;">
		<tr>
		  <th style="padding: 12px; border: 1px solid #e5e7eb;">Name</th>
		  <th style="padding: 12px; border: 1px solid #e5e7eb;">Type</th>
		  <th style="padding: 12px; border: 1px solid #e5e7eb;">Stock</th>
		  <th style="padding: 12px; border: 1px solid #e5e7eb;">Metric</th>
		  <th style="padding: 12px; border: 1px solid #e5e7eb;">Days Remaining</th>
		  <th style="padding: 12px; border: 1px solid #e5e7eb;">Action</th>
		</tr>
	  </thead>
	  <tbody id="tableBody">
	  </tbody>
	</table>
    <div id="emptyMessage" class="empty" style="display: none;">😕 No materials found.</div>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
  <script>
    const clientId = <?= json_encode($client_id); ?>;
	const apiUrl = `https://sunfra.com/farm/test2/feedrawmaterial/feed_raw_material_json.php?client_id=${clientId}`;

	let allMaterials = [];

	const tableBody = document.getElementById("tableBody");
	const searchBox = document.getElementById("searchBox");
	const typeFilter = document.getElementById("typeFilter");
	const emptyMessage = document.getElementById("emptyMessage");

	function getBadgeClass(type) {
	  if (type === "Water Medicine") return "badge water";
	  if (type === "Feed Medicine") return "badge feed-med";
	  if (type === "Feed Raw Material") return "badge raw";
	  return "badge";
	}

	async function fetchData() {
	  try {
		const res = await fetch(apiUrl);
		const json = await res.json();

		console.log("Client ID:", clientId);
		console.log("Full JSON data:", json);
		console.log("Data for this client:", json[clientId]);

		allMaterials = json[String(clientId)] || [];
		renderTable();
	  } catch (err) {
		console.error("❌ Fetch error:", err);
		tableBody.innerHTML = "<tr><td colspan='5' style='color:red;'>❌ Failed to load data</td></tr>";
	  }
	}

	function renderTable() {
	  const query = searchBox.value.toLowerCase();
	  const selectedType = typeFilter.value;

	  const filtered = allMaterials
	  .filter(item => {
		const matchesType = selectedType === "All" || item.type === selectedType;
		const matchesSearch = item.name.toLowerCase().includes(query);
		return matchesType && matchesSearch;
	  })
	  .sort((a, b) => a.name.localeCompare(b.name)); 

	  tableBody.innerHTML = "";
	  emptyMessage.style.display = filtered.length ? "none" : "block";

	  filtered.forEach(item => {
		const row = document.createElement("tr");
		row.innerHTML = `
		  <td style="padding: 12px; border: 1px solid #e5e7eb;">${item.name}</td>
		  <td style="padding: 12px; border: 1px solid #e5e7eb;">
			<span class="${getBadgeClass(item.type)}">${item.type}</span>
		  </td>
		  <td style="padding: 12px; border: 1px solid #e5e7eb;">${parseFloat(item.stock).toFixed(2)}</td>
		  <td style="padding: 12px; border: 1px solid #e5e7eb;">${item.metric}</td>
		  <td style="padding: 12px; border: 1px solid #e5e7eb;">${parseFloat(item.days).toFixed(1)}</td>
		  <td style="padding: 12px; border: 1px solid #e5e7eb;">
			<button onclick='editMaterial(${JSON.stringify(item)})' style="padding: 4px 8px; border: none; background-color: #e2e8f0; border-radius: 4px; cursor: pointer;">✏️ Edit</button>
		  </td>
		`;
		tableBody.appendChild(row);
	  });
	}

	searchBox.addEventListener("input", renderTable);
	typeFilter.addEventListener("change", renderTable);
	fetchData();

	function openModal() {
	  document.getElementById("modalOverlay").style.display = "flex";
	  document.getElementById("formMessage").innerText = "";
	}

	function closeModal() {
	  document.getElementById("modalOverlay").style.display = "none";
	  document.getElementById("materialForm").reset();
	}

	document.getElementById("materialForm").addEventListener("submit", async function (e) {
	  e.preventDefault();

	  const form = e.target;
	  const formData = new FormData(form);
	  const payload = Object.fromEntries(formData.entries());
	  payload.client_id = clientId;

	  const response = await fetch("https://sunfra.com/farm/test2/feedrawmaterial/feed_raw_material_save.php", {
		method: "POST",
		body: JSON.stringify(payload),
		headers: {
		  "Content-Type": "application/json"
		}
	  });

	  const result = await response.json();
	  const messageBox = document.getElementById("formMessage");

	  if (result.status === "success") {
		messageBox.style.color = "green";
		messageBox.innerText = "✅ Material added successfully!";
		setTimeout(() => {
		  closeModal();
		  location.reload();
		}, 1000);
	  } else {
		messageBox.style.color = "red";
		messageBox.innerText = "❌ Failed to add material.";
	  }
	});

	function editMaterial(item) {
	  document.getElementById("name").value = item.name;
	  document.getElementById("stock").value = item.stock;
	  document.getElementById("metric").value = item.metric;
	  document.getElementById("type").value = item.type;
	  document.getElementById("materialId").value = item.id;

	  openModal(); 
	}


	window.addEventListener("keydown", (e) => {
	  if (e.key === "Escape") closeModal();
	});
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
