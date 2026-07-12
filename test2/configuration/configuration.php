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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Configuration Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* Layout Basics */
body {
  margin: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f7fa;
  height: 100vh;
  overflow: hidden;
  display: flex;
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

/* Content */
.content {
  margin-left: 70px;
  padding: 20px;
  flex-grow: 1;
  overflow-y: auto;
  height: 100vh;
  transition: margin-left 0.3s ease;
}

.sidebar.expanded ~ .content {
  margin-left: 250px;
}

/* Card Layout */
.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  padding: 20px 0;
}

.card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  border-top: 5px solid #016795;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.2s ease;
}

.card:hover {
  transform: translateY(-5px);
}

.card h3 {
  margin: 0 0 10px;
  font-size: 20px;
  color: #016795;
}

.card p {
  flex-grow: 1;
  color: #444;
  font-size: 14px;
  margin-bottom: 15px;
}

.card-btn {
  display: inline-block;
  padding: 8px 16px;
  background-color: #016795;
  color: white;
  text-decoration: none;
  border-radius: 5px;
  text-align: center;
  transition: background-color 0.3s ease;
  cursor: pointer;
}

.card-btn:hover {
  background-color: #0194c7;
}

/* Modals (Generic) */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background-color: rgba(0,0,0,0.5);
  z-index: 2000;
  justify-content: center;
  align-items: center;
  padding: 15px;
  box-sizing: border-box;
}

.modal-overlay.active {
  display: flex;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 400px;
  width: 100%;
  padding: 25px 30px;
  box-shadow: 0 10px 30px rgb(0 0 0 / 0.2);
  position: relative;
  outline: none;
  max-height: 90vh;
  overflow-y: auto;
}

.close-modal-btn {
  position: absolute;
  right: 15px;
  top: 15px;
  background: transparent;
  color: #333;
  border: none;
  font-size: 24px;
  font-weight: 700;
  cursor: pointer;
  line-height: 1;
  user-select: none;
}

.close-modal-btn:hover {
  color: #e63946;
}

/* Form Elements */
form label {
  display: block;
  font-weight: 600;
  margin-bottom: 6px;
  color: #444;
}

form input[type="number"],
form input[type="text"],
form textarea {
  width: 90%;
  padding: 10px 12px;
  font-size: 15px;
  border-radius: 6px;
  border: 1px solid #ccc;
  transition: border-color 0.3s;
  font-family: inherit;
}

form input[type="number"]:focus,
form input[type="text"]:focus,
form textarea:focus {
  border-color: #016795;
  outline: none;
  background-color: #fff;
}

form button[type="submit"] {
  background-color: #016795;
  color: white;
  font-weight: 700;
  padding: 12px 20px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  width: 100%;
  margin-top: 10px;
}

form button[type="submit"]:hover {
  background-color: #014f69;
}

/* Attendance Submenu */
.attendance-submenu {
  display: none;
  flex-direction: column;
  background: #1e293b;
  width: 100%;
  padding-left: 40px;
  transition: all 0.3s ease;
}

.attendance-submenu button:hover {
  background-color: #2563EB;
}

/* Location Modal List */
#locationList {
  margin-top: 15px;
}

.location-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f0f0f0;
  padding: 10px 12px;
  margin-bottom: 10px;
  border-radius: 6px;
  font-size: 15px;
  color: #333;
}

.location-item button {
  background-color: #e63946;
  color: white;
  border: none;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.location-item button:hover {
  background-color: #b92d3a;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    width: 250px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  .sidebar.show {
    transform: translateX(0);
  }
  .content {
    margin-left: 0 !important;
    padding: 15px !important;
  }
}

@media (max-width: 700px) {
  form {
    padding: 20px;
    width: 95%;
  }
  .content {
    padding: 15px;
    margin-left: 70px;
  }
}#sheadList {
  margin: 20px 0;
  text-align: center;
}

.shead-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;
}

.shead-card {
  background-color: #f3f4f6;
  border: 1px solid #ccc;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 14px;
  box-shadow: 2px 2px 5px rgba(0,0,0,0.08);
  transition: transform 0.2s ease;
}

.shead-card:hover {
  transform: scale(1.05);
  background-color: #e5e7eb;
  cursor: default;
}.shead-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 12px;
  margin-top: 15px;
}

.shead-card {
  background-color: #f0f0f0;
  padding: 12px;
  border-radius: 8px;
  text-align: center;
  font-weight: bold;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
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
    <div class="card-container">
      <div class="card">
        <h3>Shead Configuration</h3>
        <p>Configure all your sheads here.</p>
        <a  class="card-btn" id="openSheadModalBtn">Configure Sheads</a>
      </div>
		<div class="card">
        <h3>Location Configuration</h3>
        <p>Configure all your location here.</p>
        <a class="card-btn" id="openLocationModalBtn">Configure Working Locations</a>
      </div>
	  <div class="card">
        <h3>Shead Box Configuration</h3>
        <p>Configure all your shead boxes.</p>
        <a class="card-btn" id="openSheadBoxModalBtn">Configure Shead Boxes</a>
      </div>
    </div>

    <div id="sheadModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <div class="modal-content">
        <button class="close-modal-btn" aria-label="Close modal" id="closeSheadModalBtn">×</button>
        <h2 id="modalTitle">Add Sheads</h2>
			<div id="sheadList"></div> <!-- Add this line -->

			<form id="modalSheadForm">
			  <label for="modalSheadNumber">How many sheads you have:</label>
			  <input type="number" id="modalSheadNumber" name="shead_number" min="1" required />
			  <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">
			  <button type="submit">Save Sheads</button>
			  <p id="modalResponseMsg" style="margin-top: 15px; text-align: center;"></p>
			</form>
      </div>
    </div>
	
	<div id="locationModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="locationModalTitle">
	  <div class="modal-content">
		<button class="close-modal-btn" aria-label="Close modal" id="closeLocationModalBtn">×</button>
		<h2 id="locationModalTitle">Manage Locations</h2>
		<div id="locationList"></div>
		<form id="addLocationForm" style="margin-top: 20px;">
		  <input type="text" id="newLocationInput" placeholder="Enter new location" required />
		  <button type="submit">Add Location</button>
		</form>
		<p id="locationMsg" style="text-align: center; margin-top: 10px;"></p>
	  </div>
	</div>
	
	<div id="sheadBoxModalOverlay" class="modal-overlay" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="sheadBoxModalTitle">
	  <div class="modal-content">
		<button class="close-modal-btn" aria-label="Close modal" id="closeSheadBoxModalBtn">×</button>
		<h2 id="sheadBoxModalTitle">Add Shead Boxes</h2>

		<div id="sheadBoxList" style="margin-bottom: 20px;"></div> <!-- ✅ Added here -->

		<form id="modalSheadBoxForm">
		  <label for="modalSheadBoxNumber">Enter Number of Feed Box in sheads:</label>
		  <input type="number" id="modalSheadBoxNumber" name="shead_box_number" min="1" required />
		  <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">
		  <button type="submit">Save Shead Boxes</button>
		  <p id="modalBoxResponseMsg" style="margin-top: 15px; text-align: center;"></p>
		</form>
	  </div>
	</div>

  </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
  const clientId = <?php echo json_encode($client_id); ?>;

  const sheadModal = document.getElementById('sheadModalOverlay');
  const openSheadModalBtn = document.getElementById('openSheadModalBtn');
  const closeSheadModalBtn = document.getElementById('closeSheadModalBtn');
  const modalSheadForm = document.getElementById('modalSheadForm');
  const modalResponseMsg = document.getElementById('modalResponseMsg');
  const modalSheadNumber = document.getElementById('modalSheadNumber');

  openSheadModalBtn.addEventListener('click', (e) => {
	  e.preventDefault();
	  sheadModal.classList.add('active');
	  modalResponseMsg.textContent = '';
	  modalSheadNumber.value = '';
	  modalSheadNumber.focus();
	  fetchSheads(); // Add this line
	});

  closeSheadModalBtn.addEventListener('click', () => {
    sheadModal.classList.remove('active');
  });

  sheadModal.addEventListener('click', (e) => {
    if (e.target === sheadModal) {
      sheadModal.classList.remove('active');
    }
  });
	
	setTimeout(() => {
	  sheadModal.classList.remove('active');
	  fetchSheads(); // refresh after save
	}, 1500);

	 modalSheadForm.addEventListener('submit', async (e) => {
	  e.preventDefault();
	  modalResponseMsg.textContent = '';
	  try {
		const formData = new FormData(modalSheadForm);
		const res = await fetch('shead_status_save.php', {
		  method: 'POST',
		  body: formData
		});
		const data = await res.json();
		if (data.status === 'success') {
		  modalResponseMsg.style.color = 'green';
		  modalResponseMsg.textContent = data.message || 'Saved successfully!';
		  setTimeout(() => {
			sheadModal.classList.remove('active');
			fetchSheads(); // ✅ Refresh after saving
		  }, 1500);
		} else {
		  modalResponseMsg.style.color = 'red';
		  modalResponseMsg.textContent = data.message || 'Failed to save data.';
		}
	  } catch (err) {
		modalResponseMsg.style.color = 'red';
		modalResponseMsg.textContent = 'Error occurred while saving.';
		console.error(err);
	  }
	});

	const openLocationBtn = document.getElementById('openLocationModalBtn');
  const locationModal = document.getElementById('locationModalOverlay');
  const closeLocationModalBtn = document.getElementById('closeLocationModalBtn');
  const locationListDiv = document.getElementById('locationList');
  const addLocationForm = document.getElementById('addLocationForm');
  const newLocationInput = document.getElementById('newLocationInput');
  const locationMsg = document.getElementById('locationMsg');

  openLocationBtn.addEventListener('click', () => {
    locationModal.style.display = 'flex';
    fetchLocations();
  });

  closeLocationModalBtn.addEventListener('click', () => {
    locationModal.style.display = 'none';
    locationMsg.textContent = '';
    newLocationInput.value = '';
    locationListDiv.innerHTML = '';
  });

  function fetchLocations() {
    locationListDiv.innerHTML = 'Loading...';
    fetch(`https://sunfra.com/farm/test2/configuration/config_location_json.php?client_id=${clientId}`)
      .then(response => response.json())
      .then(data => {
        locationListDiv.innerHTML = '';
        const locations = data[clientId];
        if (!locations || locations.length === 0) {
          locationListDiv.innerHTML = '<p>No locations found.</p>';
          return;
        }
        locations.forEach(loc => {
          const locationName = loc.location;
          const div = document.createElement('div');
          div.className = 'location-item';
          div.innerHTML = `
            <span>${locationName}</span>
            <button onclick="removeLocation('${locationName}')">Remove</button>
          `;
          locationListDiv.appendChild(div);
        });
      })
      .catch(err => {
        console.error(err);
        locationListDiv.innerHTML = '<p style="color: red;">Error fetching locations.</p>';
      });
  }

addLocationForm.addEventListener('submit', e => {
  e.preventDefault();
  const locationName = newLocationInput.value.trim();
  if (!locationName) return;
  
  fetch('https://sunfra.com/farm/test2/configuration/config_location_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `client_id=${clientId}&location=${encodeURIComponent(locationName)}&operation=add`
  })
  .then(response => response.text())
  .then(result => {
    locationMsg.textContent = 'Location added successfully!';
    newLocationInput.value = '';
    fetchLocations();
  })
  .catch(() => {
    locationMsg.textContent = 'Error adding location.';
  });
});

window.removeLocation = function(locationName) {
  if (!confirm(`Remove location: ${locationName}?`)) return;

  fetch('https://sunfra.com/farm/test2/configuration/config_location_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `client_id=${clientId}&location=${encodeURIComponent(locationName)}&operation=delete`
  })
  .then(response => response.text())
  .then(result => {
    locationMsg.textContent = 'Location removed.';
    fetchLocations();
  })
  .catch(() => {
    locationMsg.textContent = 'Error removing location.';
  });
};

const openSheadBoxModalBtn = document.getElementById('openSheadBoxModalBtn');
const sheadBoxModal = document.getElementById('sheadBoxModalOverlay');
const closeSheadBoxModalBtn = document.getElementById('closeSheadBoxModalBtn');
const modalSheadBoxForm = document.getElementById('modalSheadBoxForm');
const modalSheadBoxNumber = document.getElementById('modalSheadBoxNumber');
const modalBoxResponseMsg = document.getElementById('modalBoxResponseMsg');

openSheadBoxModalBtn.addEventListener('click', (e) => {
  e.preventDefault();
  sheadBoxModal.classList.add('active');
  modalBoxResponseMsg.textContent = '';
  modalSheadBoxNumber.value = '';
  modalSheadBoxNumber.focus();
  fetchSheadBoxes();
});

closeSheadBoxModalBtn.addEventListener('click', () => {
  sheadBoxModal.classList.remove('active');
});

sheadBoxModal.addEventListener('click', (e) => {
  if (e.target === sheadBoxModal) {
    sheadBoxModal.classList.remove('active');
  }
});

modalSheadBoxForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  modalBoxResponseMsg.textContent = '';

  try {
    const formData = new FormData(modalSheadBoxForm);

    const res = await fetch('https://sunfra.com/farm/test2/configuration/config_shead_box_save.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json(); 

    if (data.status === 'success') {
      modalBoxResponseMsg.style.color = 'green';
      modalBoxResponseMsg.textContent = data.message || 'Shead boxes saved successfully!';

      setTimeout(() => {
        sheadBoxModal.classList.remove('active');
        fetchSheadBoxes(); // ✅ Refresh the list
      }, 1500);
      
    } else {
      modalBoxResponseMsg.style.color = 'red';
      modalBoxResponseMsg.textContent = data.message || 'Failed to save shead boxes.';
    }
  } catch (err) {
    modalBoxResponseMsg.style.color = 'red';
    modalBoxResponseMsg.textContent = 'Error occurred while saving.';
    console.error('Fetch error:', err);
  }
});

function fetchSheads() {
  const sheadListDiv = document.getElementById('sheadList');
  sheadListDiv.innerHTML = 'Loading...';

  fetch(`https://sunfra.com/farm/test2/configuration/shead_number_json.php?client_id=${clientId}`)
    .then(response => response.json())
    .then(data => {
      sheadListDiv.innerHTML = '';

      if (!data || data.length === 0) {
        sheadListDiv.innerHTML = '<p style="text-align: center;">No sheads configured yet.</p>';
        return;
      }

      const container = document.createElement('div');
      container.classList.add('shead-grid');

      data.forEach((shead, index) => {
        const box = document.createElement('div');
        box.className = 'shead-card';
        box.textContent = shead.shead_name;
        container.appendChild(box);
      });

      sheadListDiv.appendChild(container);
    })
    .catch(err => {
      console.error(err);
      sheadListDiv.innerHTML = '<p style="color: red;">Error fetching sheads.</p>';
    });
}
function fetchSheadBoxes() {
  const boxListDiv = document.getElementById('sheadBoxList');
  boxListDiv.innerHTML = 'Loading...';

  fetch(`https://sunfra.com/farm/test2/configuration/config_shead_box_json.php?client_id=${clientId}`)
    .then(response => response.json())
    .then(data => {
      boxListDiv.innerHTML = '';

      const boxes = data[clientId];
      if (!boxes || boxes.length === 0) {
        boxListDiv.innerHTML = '<p style="text-align: center;">No shead boxes configured yet.</p>';
        return;
      }

      const container = document.createElement('div');
      container.classList.add('shead-grid');

      boxes.forEach((boxObj) => {
        const box = document.createElement('div');
        box.className = 'shead-card';
        box.textContent = boxObj.box_numbers;
        container.appendChild(box);
      });

      boxListDiv.appendChild(container);
    })
    .catch(err => {
      console.error(err);
      boxListDiv.innerHTML = '<p style="color: red;">Error fetching shead boxes.</p>';
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
