<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$current_feature = "Attendance"; // <-- Change per page (e.g. "Weighbridge")

// 1. Login check
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

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Labour Master Records</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 20px;
    }

    h2 {
      text-align: center;
      color: #3f51b5;
      margin-bottom: 20px;
    }

    .header-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }

    .left-controls {
      display: flex;
      align-items: center;
    }

    .right-controls {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 10px;
    }

    .right-controls input {
      padding: 8px 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .right-controls button {
      padding: 8px 14px;
      background-color: #3f51b5;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .right-controls button:hover {
      background-color: #303f9f;
    }

    select {
      padding: 8px 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #4CBB17;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    tr:hover {
      background-color: #e8f0fe;
    }

    .pagination {
      margin-top: 20px;
      text-align: center;
    }

    .pagination button {
      background-color: #3f51b5;
      color: white;
      border: none;
      padding: 8px 12px;
      margin: 0 4px;
      border-radius: 4px;
      cursor: pointer;
    }

    .pagination button.active {
      background-color: #1a237e;
    }

    .pagination button:hover:not(.active) {
      background-color: #5c6bc0;
    }.edit-btn {
      padding: 5px 10px;
      background-color: #007BFF;
      color: white;
      border: none;
      border-radius: 3px;
      cursor: pointer;
    }

    .edit-btn:hover {
      background-color: #0056b3;
    }.modal {
		  display: none;
		  position: fixed;
		  z-index: 999;
		  left: 0;
		  top: 0;
		  width: 100%;
		  height: 100%;
		  overflow: auto;
		  background-color: rgba(0, 0, 0, 0.5);
		}

		.modal-content {
		  background-color: #fff;
		  margin: 4% auto;
		  padding: 25px 35px;
		  border: 1px solid #888;
		  width: 80%;
		  max-width: 900px;
		  border-radius: 12px;
		  box-shadow: 0 0 12px rgba(0, 0, 0, 0.25);
		  font-family: 'Segoe UI', sans-serif;
		}

		.close {
		  color: #aaa;
		  float: right;
		  font-size: 28px;
		  font-weight: bold;
		  cursor: pointer;
		}

		.close:hover,
		.close:focus {
		  color: red;
		}

		h2#modalTitle {
		  text-align: center;
		  margin-bottom: 25px;
		}

		#labourForm {
		  display: flex;
		  flex-direction: column;
		  gap: 20px;
		}

		.form-row {
		  display: flex;
		  flex-wrap: wrap;
		  gap: 20px;
		}

		.form-group {
		  flex: 1;
		  min-width: 250px;
		  display: flex;
		  flex-direction: column;
		}

		.form-group label {
		  margin-bottom: 6px;
		  font-weight: 600;
		  color: #333;
		}

		.form-group input,
		.form-group textarea {
		  padding: 7px 10px;
		  border: 1px solid #ccc;
		  border-radius: 6px;
		  font-size: 14px;
		}

		.form-group textarea {
		  resize: vertical;
		  min-height: 60px;
		}

		.submit-btn {
		  align-self: center;
		  background-color: #4CAF50;
		  color: white;
		  padding: 10px 25px;
		  font-size: 15px;
		  border: none;
		  border-radius: 6px;
		  cursor: pointer;
		  margin-top: 10px;
		  transition: background-color 0.3s ease;
		}

		.submit-btn:hover {
		  background-color: #45a049;
		}

		#responseMessage {
		  margin-top: 10px;
		  text-align: center;
		  font-weight: bold;
		  color: #555;
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
  <a href="https://sunfra.com/farm/sunfra/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/sunfra/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
  <h2>Labour Master Records</h2>

  <div class="header-controls">
    <div class="left-controls">
      <label for="rowCountSelect">Show&nbsp;</label>
      <select id="rowCountSelect">
        <option value="5">5 rows</option>
        <option value="10" selected>10 rows</option>
        <option value="25">25 rows</option>
        <option value="50">50 rows</option>
      </select>
    </div>

    <div class="right-controls">
      <input type="text" id="searchInput" placeholder="Search...">
      <button onclick="openModal()">Add New Entry</button>

    </div>
  </div>

  <table id="labourTable">
    <thead>
      <tr>
        <th>Name</th>
        <th>DOB</th>
        <th>Address</th>
        <th>Phone</th>
        <th>Aadhar</th>
        <th>Joining Ref</th>
        <th>Related To</th>
        <th>Start Date</th>
        <th>Status</th>
        <th>Edit</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <div class="pagination" id="pagination"></div>
	<div id="labourModal" class="modal">
	  <div class="modal-content">
		<span class="close" onclick="closeModal()">&times;</span>
		<h2 id="modalTitle">Add Labour</h2>
			<form id="labourForm">
				<input type="hidden" id="id" name="id" />
				<input type="hidden" id="client_id" name="client_id" value="<?php echo $_SESSION['client_id']; ?>">
			  <div class="form-row">
				<div class="form-group">
				  <label for="name">Name</label>
				  <input type="text" id="name" name="name" required />
				</div>
				<div class="form-group">
				  <label for="dateOfBirth">Date of Birth</label>
				  <input type="date" id="dateOfBirth" name="dateOfBirth" required />
				</div>
				<div class="form-group">
				  <label for="phoneNumber">Phone Number</label>
				  <input type="tel" id="phoneNumber" name="phoneNumber" />
				</div>
			  </div>
			  <div class="form-row">
				<div class="form-group">
				  <label for="address">Address</label>
				  <input type="text" id="address" name="address" />
				</div>
				<div class="form-group">
				  <label for="aadhar">Aadhar Number</label>
				  <input type="text" id="aadhar" name="aadhar" />
				</div>
				<div class="form-group">
				  <label for="joiningReference">Joining Reference</label>
				  <input type="text" id="joiningReference" name="joiningReference" />
				</div>
			  </div>
			  <div class="form-row">
				<div class="form-group">
				  <label for="relatedTo">Related To</label>
				  <input type="text" id="relatedTo" name="relatedTo" required />
				</div>
				<div class="form-group">
				  <label for="startDate">Start Date</label>
				  <input type="date" id="startDate" name="startDate" required />
				</div>
			  <div class="form-group">
				  <label for="status">Status</label>
				  <select id="status" name="status" class="form-control">
					<option value="Active">Active</option>
					<option value="Inactive">Inactive</option>
				  </select>
				</div>
				</div>
		  <button type="submit" class="submit-btn">Submit</button>
		  <div id="responseMessage" style="margin-top:10px;"></div>
		</form>
	  </div>
	</div>
	</main>
	</div>
  <script>
    const clientId = <?= json_encode($client_id); ?>;
    let currentPage = 1;
    let rowsPerPage = parseInt(document.getElementById('rowCountSelect').value);
    let fullData = [];
    let filteredData = [];

    document.getElementById("rowCountSelect").addEventListener("change", function () {
      rowsPerPage = parseInt(this.value);
      currentPage = 1;
      renderTable();
      renderPagination();
    });

    document.getElementById("searchInput").addEventListener("input", function () {
      const query = this.value.toLowerCase();
      filteredData = fullData.filter(row =>
        Object.values(row).some(val =>
          String(val).toLowerCase().includes(query)
        )
      );
      currentPage = 1;
      renderTable();
      renderPagination();
    });

	   function fetchData() {
		fetch(`https://sunfra.com/farm/sunfra/attendance/labour_master_json.php?client_id=${clientId}`)
		  .then(response => response.json())
		  .then(data => {
			fullData = data[clientId] || [];
			filteredData = fullData;
			renderTable();
			renderPagination();
		  })
		  .catch(err => {
			console.error("Error fetching data:", err);
			document.querySelector("#labourTable tbody").innerHTML =
			  `<tr><td colspan="9" style="color:red;">Failed to load data.</td></tr>`;
		  });
	  }
    function renderTable() {
	  const tbody = document.querySelector("#labourTable tbody");
	  tbody.innerHTML = "";

	  const start = (currentPage - 1) * rowsPerPage;
	  const end = start + rowsPerPage;
	  const pageData = filteredData.slice(start, end);

	  pageData.forEach(row => {
		const tr = document.createElement("tr");
		const encodedData = encodeURIComponent(JSON.stringify(row));
		tr.innerHTML = `
		  <td>${row.name}</td>
		  <td>${row.dateOfBirth}</td>
		  <td>${row.address}</td>
		  <td>${row.phoneNumber}</td>
		  <td>${row.aadhar}</td>
		  <td>${row.joiningReference}</td>
		  <td>${row.relatedTo}</td>
		  <td>${row.startDate}</td>
		  <td>${row.status}</td>
		  <td><button class="edit-btn" data-row='${encodedData}'>Edit</button></td>
		`;
		tbody.appendChild(tr);
	  });

	  document.querySelectorAll(".edit-btn").forEach(btn => {
		btn.addEventListener("click", () => {
		  const data = JSON.parse(decodeURIComponent(btn.dataset.row));
		  openModal(data);
		});
	  });
	}

    function renderPagination() {
      const pagination = document.getElementById("pagination");
      pagination.innerHTML = "";
      const totalPages = Math.ceil(filteredData.length / rowsPerPage);

      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.textContent = i;
        btn.classList.toggle("active", i === currentPage);
        btn.addEventListener("click", () => {
          currentPage = i;
          renderTable();
          renderPagination();
        });
        pagination.appendChild(btn);
      }
    }

    fetchData();
	function openModal(editData = null) {
		  document.getElementById("labourModal").style.display = "block";
		  document.getElementById("labourForm").reset();
		  document.getElementById("modalTitle").innerText = editData ? "Edit Labour" : "Add Labour";

		  if (editData) {
			for (const key in editData) {
			  const el = document.getElementById(key);
			  if (el) el.value = editData[key];
			}
		  }
		}

		function closeModal() {
		  document.getElementById("labourModal").style.display = "none";
		}

		window.onclick = function(event) {
		  if (event.target == document.getElementById("labourModal")) {
			closeModal();
		  }
		};

		 document.getElementById("labourForm").addEventListener("submit", function(e) {
			e.preventDefault();

			const form = this;
			const formData = new FormData(form);
			const newName = formData.get("name")?.trim().toLowerCase();

			formData.append("client_id", clientId);

			fetch("https://sunfra.com/farm/sunfra/attendance/labour_master_save.php", {
			  method: "POST",
			  body: formData,
			})
			.then(response => {
			  if (response.ok) {
				alert("✅ Data submitted successfully!");
				form.reset();
				fetchData();
				closeModal();
			  } else {
				alert("❌ Failed to submit data.");
			  }
			})
			.catch(error => {
			  console.error("Error:", error);
			  alert("❌ Error occurred while submitting data.");
			});
		  });

		  fetchData();
		const sidebar = document.getElementById('sidebar');
		const mainContent = document.querySelector('.content'); 
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
  </script>
</body>
</html>
