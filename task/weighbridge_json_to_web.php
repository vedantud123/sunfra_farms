<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
$client_id = $_SESSION['client_id'] ?? 0;

date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents("https://sunfra.com/farm/test2/weighbridge/weighbridge_json.php?client_id=$client_id");
$data = json_decode($json, true);

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$thisMonth = date('Y-m');
$thisYear = date('Y');

$raw_json = file_get_contents("https://sunfra.com/farm/test2/feedrawmaterial/feed_raw_material_json.php?client_id=$client_id");
$raw_data = json_decode($raw_json, true);
$raw_materials = $raw_data[$client_id] ?? [];

usort($raw_materials, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body {
      background: #f9f9f9;
      font-family: 'Poppins', sans-serif;
      color: #333;
      margin: 0;
      padding: 0;
    }
    .dashboard-container {
      max-width: 1200px;
      margin: auto;
      padding: 20px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    }.visually-hidden {
	  position: absolute !important;
	  width: 1px !important;
	  height: 1px !important;
	  padding: 0 !important;
	  margin: -1px !important;
	  overflow: hidden !important;
	  clip: rect(0 0 0 0) !important;
	  white-space: nowrap !important;
	  border: 0 !important;
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
      <button onclick="location.href='https://sunfra.com/farm/test2/attendance/new_labour_master.php'">📝 Assigned Master</button>
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
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/profit_loss_json_to_web.php'">Profit & Loss Summary</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/summary_report_json_to_web.php'">Summary Report</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/vaccination_json_to_web.php'">Vaccination</button>
      <button onclick="location.href='https://sunfra.com/farm/test2/profit_and_loss_details/labour_salary_json_to_web.php'">Labour Salary</button>
    </div>
  </div>

  <a href="https://sunfra.com/farm/test2/task/task_status.php"><i class="fas fa-tasks"></i><span class="label">Task Status</span></a>
  <a href="https://sunfra.com/farm/test2/settings.php"><i class="fas fa-sliders-h"></i><span class="label">Feature Settings</span></a>
  <a href="https://sunfra.com/farm/test2/login/client_user_json_to_web.php"><i class="fas fa-user-plus"></i><span class="label">Registration</span></a>
  <a href="https://sunfra.com/farm/test2/configuration/configuration.php"><i class="fas fa-cogs"></i><span class="label">Configuration</span></a>
  <a href="https://sunfra.com"><i class="fas fa-life-ring"></i><span class="label">Support</span></a>
  <a href="https://sunfra.com/farm/test2/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>
<main class="content main-content">
	<div class="dashboard-container">
      <h2 class="dashboard-title mb-3 text-center">🚚 WeighBridge Dashboard</h2>

      <div class="filter-btns mb-3 text-center">
        <button class="btn btn-sm btn-outline-primary mb-1" onclick="filterData('today')">Today</button>
        <button class="btn btn-sm btn-outline-secondary mb-1" onclick="filterData('yesterday')">Yesterday</button>
        <button class="btn btn-sm btn-outline-success mb-1" onclick="filterData('month')">This Month</button>
        <button class="btn btn-sm btn-outline-danger mb-1" onclick="filterData('year')">This Year</button>
        <button class="btn btn-sm btn-outline-dark mb-1" onclick="filterData('all')">All</button>
      </div>

      <div class="text-end mb-3">
        <div class="btn-group">
          <button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            ➕ Add New Entry
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addModal" id="btn-with-weighbridge">With Weighbridge</button></li>
            <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addModal" id="btn-without-weighbridge">Without Weighbridge</button></li>
          </ul>
        </div>
      </div>

      <div class="table-responsive">
        <table id="weighbridgeTable" class="table table-striped table-bordered align-middle">
          <thead class="table-dark text-center">
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>Vehicle No</th>
              <th>Material</th>
              <th>Empty</th>
              <th>Gross</th>
              <th>Net</th>
              <th>Owner</th>
              <th>Type</th>
              <th>Edit</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($data[$client_id]) && count($data[$client_id]) > 0): ?>
              <?php foreach ($data[$client_id] as $entry): ?>
                <tr data-date="<?= htmlspecialchars($entry['date']) ?>">
                  <td class="text-end"><?= $entry['id'] ?></td>
                  <td class="text-center"><?= htmlspecialchars($entry['date']) ?></td>
                  <td><?= htmlspecialchars($entry['vehicleNumber']) ?></td>
                  <td>
                    <span class="badge bg-<?php
                      switch (strtolower($entry['material'])) {
                        case 'feed': echo 'info'; break;
                        case 'eggs': echo 'warning text-dark'; break;
                        case 'chiru': echo 'success'; break;
                        case 'ageemix': echo 'secondary'; break;
                        default: echo 'dark'; break;
                      }
                    ?>">
                      <?= htmlspecialchars($entry['material']) ?>
                    </span>
                  </td>
                  <td class="text-end"><?= number_format($entry['empty']) ?></td>
                  <td class="text-end"><?= number_format($entry['gross']) ?></td>
                  <td class="text-end fw-bold text-primary"><?= number_format($entry['net']) ?></td>
                  <td><?= htmlspecialchars($entry['ownerName']) ?></td>
                  <td><?= htmlspecialchars($entry['type']) ?></td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary edit-btn-custom" data-bs-toggle="modal" data-bs-target="#editModal"
                      data-id="<?= $entry['id'] ?>"
                      data-date="<?= htmlspecialchars($entry['date']) ?>"
                      data-vehicle="<?= htmlspecialchars($entry['vehicleNumber']) ?>"
                      data-material="<?= htmlspecialchars($entry['material']) ?>"
                      data-empty="<?= $entry['empty'] ?>"
                      data-gross="<?= $entry['gross'] ?>"
                      data-net="<?= $entry['net'] ?>"
                      data-owner="<?= htmlspecialchars($entry['ownerName']) ?>"
                      data-type="<?= htmlspecialchars($entry['type']) ?>"
                      data-driver="<?= htmlspecialchars($entry['driverNumber']) ?>"
                      data-ownerno="<?= htmlspecialchars($entry['ownerNumber']) ?>"
                      data-details="<?= htmlspecialchars($entry['details']) ?>"
                    >Edit</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="text-center text-danger">❌ No data found for this client.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form action="https://sunfra.com/farm/test2/weighbridge/weighbridge_save.php" method="POST" id="with-weighbridge-form" class="d-none p-3">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">

        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">Add New Weighbridge Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-md-3"><label>ID</label><input name="id" class="form-control rounded-pill shadow-sm" required></div>
          <div class="col-md-3"><label>Date</label><input type="date" name="date" class="form-control" required></div>
          <div class="col-md-3"><label>Vehicle Number</label><input name="vehicleNumber" class="form-control" required></div>
          <div class="col-md-4">
            <label for="type-material">Material</label>
            <select name="material" id="type-material" class="form-control rounded-pill shadow-sm" required>
              <option value="">Select Material</option>
              <?php foreach ($raw_materials as $rm): ?>
                <option value="<?= htmlspecialchars($rm['name']) ?>">
                  <?= htmlspecialchars($rm['name']) ?>
                </option>
              <?php endforeach; ?>
              <option value="__new__">➕ Add New Material</option>
            </select>
          </div>

          <div class="col-md-4 d-none" id="new-material-div">
            <label for="new-material">New Material Name</label>
            <div class="input-group">
              <input type="text" name="new_material" id="new-material" class="form-control rounded-pill shadow-sm">
              <button type="button" class="btn btn-outline-danger ms-2" id="cancel-new-material" title="Cancel">❌ Cancel</button>
            </div>
          </div>

          <div class="col-md-4 d-none" id="material-type-section">
            <label for="material_type">Material Type</label>
            <select name="material_type" id="material_type" class="form-control rounded-pill shadow-sm">
              <option value="">Select Type</option>
              <option value="Raw Material">Raw Material</option>
              <option value="Water Medicine">Water Medicine</option>
              <option value="Feed Medicine">Feed Medicine</option>
            </select>
          </div>

          <div class="col-md-2"><label>Empty</label><input type="number" name="empty" class="form-control" required></div>
          <div class="col-md-2"><label>Gross</label><input type="number" name="gross" class="form-control" required></div>
          <div class="col-md-2"><label>Net</label><input type="number" name="net" class="form-control" required></div>
          <div class="col-md-3"><label>Owner Name</label><input name="ownerName" class="form-control" required></div>
          <div class="col-md-2">
            <label for="type">Type</label>
            <select name="type" id="type" class="form-control" required>
			  <option value="">Select Type</option>
			  <option value="Purchase">Purchase</option>
			  <option value="Sale">Sale</option>
			  <option value="Free">Free</option>
			</select>
          </div>
		  
          <div class="col-md-3"><label>Driver Number</label><input name="driverNumber" class="form-control"></div>
          <div class="col-md-3"><label>Owner Number</label><input name="ownerNumber" class="form-control"></div>
          <div class="col-12"><label>Details</label><textarea name="details" rows="2" class="form-control"></textarea></div>
        </div>
		<div class="form-check mb-3 ms-2">
		  <input class="form-check-input" type="checkbox" name="add_to_accounts" value="1" id="addToAccounts">
		  <label class="form-check-label" for="addToAccounts">
			Add to Accounts
		  </label>
		</div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit With Weighbridge</button>
          <button type="button" class="btn btn-warning w-100 rounded-pill" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>

      <form action="https://sunfra.com/farm/test2/weighbridge/weighbridge_save.php" method="POST" id="without-weighbridge-form" class="d-none p-3">
        <input type="hidden" name="action" value="add_without_weighbridge">
        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">

        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title">Add Entry Without Weighbridge</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-md-4"><label>Date</label><input type="date" name="date" class="form-control" required></div>
			<div class="col-md-4">
			  <label for="without-material">Material</label>
			  <select name="material" id="without-material" class="form-control rounded-pill shadow-sm" required>
				<option value="">Select Material</option>
				<?php foreach ($raw_materials as $rm): ?>
				  <option value="<?= htmlspecialchars($rm['name']) ?>"><?= htmlspecialchars($rm['name']) ?></option>
				<?php endforeach; ?>
				<option value="__new__">➕ Add New Material</option>
			  </select>
			</div>
			<div class="col-md-4 d-none" id="without-new-material-div">
			  <label for="without-new-material">New Material Name</label>
			  <div class="input-group">
				<input type="text" name="new_material" id="without-new-material" class="form-control rounded-pill shadow-sm" placeholder="Enter new material name">
				<button type="button" class="btn btn-outline-danger ms-2" id="without-cancel-new-material" title="Cancel">❌ Cancel</button>
			  </div>
			</div>

			<div class="col-md-4 d-none" id="without-material-type-section">
			  <label for="without-material_type">Material Type</label>
			  <select name="material_type" id="without-material_type" class="form-control rounded-pill shadow-sm">
				<option value="">Select Type</option>
				<option value="Raw Material">Raw Material</option>
				<option value="Water Medicine">Water Medicine</option>
				<option value="Feed Medicine">Feed Medicine</option>
			  </select>
			</div>
          <div class="col-md-4"><label>Quantity</label><input type="number" name="quantity" class="form-control" required></div>
        </div>
		<div class="form-check">
		  <input class="form-check-input" type="checkbox" id="addToAccountCheckbox">
		  <label class="form-check-label" for="addToAccountCheckbox">
			Add to Account
		  </label>
		</div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-secondary w-100 rounded-pill">Save Entry</button>
          <button type="button" class="btn btn-secondary w-100 rounded-pill" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form action="https://sunfra.com/farm/test2/weighbridge/weighbridge_save.php" method="POST">

		<input type="hidden" name="action" value="update">
        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">

        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">✏️ Edit Weighbridge Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-3"><label>ID</label><input type="text" name="id" id="edit-id" class="form-control rounded-pill shadow-sm" required></div>
            <div class="col-md-3"><label>Date</label><input type="date" name="date" id="edit-date" class="form-control rounded-pill shadow-sm" required></div>
            <div class="col-md-3"><label>Vehicle No</label><input name="vehicleNumber" id="edit-vehicle" class="form-control rounded-pill shadow-sm" required></div>

            <div class="col-md-3">
              <label>Material</label>
              <select name="material" id="edit-material" class="form-control rounded-pill shadow-sm" required>
                <option value="">Select Material</option>
                <?php foreach ($raw_materials as $rm): ?>
                  <option value="<?= htmlspecialchars($rm['name']) ?>"><?= htmlspecialchars($rm['name']) ?></option>
                <?php endforeach; ?>
                <option value="__new__">➕ Add New Material</option>
              </select>
            </div>

            <div class="col-md-4 d-none" id="edit-new-material-div">
              <label for="edit-new-material">New Material Name</label>
              <div class="input-group">
                <input type="text" name="new_material" id="edit-new-material" class="form-control rounded-pill shadow-sm">
                <button type="button" class="btn btn-outline-danger ms-2" id="edit-cancel-new-material" title="Cancel">❌ Cancel</button>
              </div>
            </div>

            <div class="col-md-4 d-none" id="edit-material-type-section">
              <label for="edit-material_type">Material Type</label>
              <select name="material_type" id="edit-material_type" class="form-control rounded-pill shadow-sm">
                <option value="">Select Type</option>
                <option value="Raw Material">Raw Material</option>
                <option value="Water Medicine">Water Medicine</option>
                <option value="Feed Medicine">Feed Medicine</option>
              </select>
            </div>

            <div class="col-md-2"><label>Empty</label><input type="number" name="empty" id="edit-empty" class="form-control rounded-pill shadow-sm" required></div>
            <div class="col-md-2"><label>Gross</label><input type="number" name="gross" id="edit-gross" class="form-control rounded-pill shadow-sm" required></div>
            <div class="col-md-2"><label>Net</label><input type="number" name="net" id="edit-net" class="form-control rounded-pill shadow-sm" required></div>
            <div class="col-md-3"><label>Owner Name</label><input name="ownerName" id="edit-owner" class="form-control rounded-pill shadow-sm" required></div>
            <div class="col-md-2">
              <label for="edit-type">Type</label>
              <select name="type" id="edit-type" class="form-control rounded-pill shadow-sm" required>
                <option value="">Select Type</option>
                <option value="Purchase">Purchase</option>
                <option value="Sale">Sale</option>
                <option value="Free">Free</option>
              </select>
            </div>
            <div class="col-md-3"><label>Driver Number</label><input name="driverNumber" id="edit-driver" class="form-control rounded-pill shadow-sm"></div>
            <div class="col-md-3"><label>Owner Number</label><input name="ownerNumber" id="edit-ownerno" class="form-control rounded-pill shadow-sm"></div>
            <div class="col-12"><label>Details</label><textarea name="details" id="edit-details" rows="3" class="form-control rounded-3 shadow-sm"></textarea></div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-warning w-100 rounded-pill">💾 Save Changes</button>
        </div>

      </form>
    </div>
  </div>
</div>

    </div> 
	<div class="modal fade" id="materialPriceModal" tabindex="-1" aria-labelledby="materialPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="materialPriceModalLabel">Add Material Price</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="materialPriceForm">
			<input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id); ?>">
          <div class="mb-3">
            <label for="materialName" class="form-label">Material Name</label>
            <input type="text" class="form-control" id="materialName" required>
          </div>
          <div class="mb-3">
            <label for="materialPrice" class="form-label">Price</label>
            <input type="number" class="form-control" id="materialPrice" required>
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>
</main>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
 $(document).ready(function() {
  // Initialize DataTable
  $('#weighbridgeTable').DataTable({
    order: [[0, "desc"]],
    pageLength: 10,
    responsive: true
  });

  // Filter function to show/hide rows based on date filter buttons
  window.filterData = function(type) {
    const today = "<?= $today ?>";
    const yesterday = "<?= $yesterday ?>";
    const thisMonth = "<?= $thisMonth ?>";
    const thisYear = "<?= $thisYear ?>";

    $('#weighbridgeTable tbody tr').each(function() {
      const rowDate = $(this).data('date');
      let show = false;

      if(type === 'today' && rowDate === today) show = true;
      else if(type === 'yesterday' && rowDate === yesterday) show = true;
      else if(type === 'month' && rowDate.startsWith(thisMonth)) show = true;
      else if(type === 'year' && rowDate.startsWith(thisYear)) show = true;
      else if(type === 'all') show = true;

      $(this).toggle(show);
    });
  };

  // Edit button click fills and opens the edit modal
  $(document).on('click', '.edit-btn-custom', function(){
    $('#edit-id').val($(this).data('id'));
    $('#edit-date').val($(this).data('date'));
    $('#edit-vehicle').val($(this).data('vehicle'));
    $('#edit-material').val($(this).data('material'));
    $('#edit-empty').val($(this).data('empty'));
    $('#edit-gross').val($(this).data('gross'));
    $('#edit-net').val($(this).data('net'));
    $('#edit-owner').val($(this).data('owner'));
    $('#edit-type').val($(this).data('type'));
    $('#edit-driver').val($(this).data('driver'));
    $('#edit-ownerno').val($(this).data('ownerno'));
    $('#edit-details').val($(this).data('details'));
    $('#editModal').modal('show');
  });

  // Material select change handles new material input display
  $('#type-material, #edit-material').on('change', function() {
    const id = $(this).attr('id');
    const isNew = $(this).val() === '__new__';

    const newMaterialDivId = (id === 'type-material') ? '#new-material-div' : '#edit-new-material-div';
    const materialTypeSectionId = (id === 'type-material') ? '#material-type-section' : '#edit-material-type-section';
    const newMaterialInputId = (id === 'type-material') ? '#new-material' : '#edit-new-material';
    const materialTypeInputId = (id === 'type-material') ? '#material_type' : '#edit-material_type';

    $(newMaterialDivId).toggleClass('d-none', !isNew);
    $(materialTypeSectionId).toggleClass('d-none', !isNew);

    $(newMaterialInputId).prop('required', isNew);
    $(materialTypeInputId).prop('required', isNew);
  });

  // Cancel new material in add or edit forms
  $('#cancel-new-material, #edit-cancel-new-material').on('click', function() {
    const btnId = $(this).attr('id');
    let targetDiv, materialSelect;

    if (btnId === 'cancel-new-material') {
      targetDiv = $('#new-material-div');
      materialSelect = $('#type-material');
    } else {
      targetDiv = $('#edit-new-material-div');
      materialSelect = $('#edit-material');
    }

    targetDiv.addClass('d-none');
    targetDiv.find('input, select').val('').prop('required', false);
    materialSelect.val('');
  });

  // Without weighbridge material select change handling
  const withoutMaterialSelect = $('#without-material');
  if (withoutMaterialSelect.length) {
    withoutMaterialSelect.on('change', function() {
      const isNew = $(this).val() === '__new__';
      $('#without-new-material-div').toggleClass('d-none', !isNew);
      $('#without-material-type-section').toggleClass('d-none', !isNew);
      $('#without-new-material').prop('required', isNew);
      $('#without-material_type').prop('required', isNew);

      if (!isNew) {
        $('#without-new-material').val('');
        $('#without-material_type').val('');
      }
    });
  }

  $('#without-cancel-new-material').on('click', function() {
    withoutMaterialSelect.val('');
    $('#without-new-material-div').addClass('d-none');
    $('#without-material-type-section').addClass('d-none');
    $('#without-new-material').val('').prop('required', false);
    $('#without-material_type').val('').prop('required', false);
  });

  // Show/hide price input div based on type selection
  $('#type').on('change', function() {
    const priceDiv = $('#price-per-kg-div');
    if (this.value === 'Purchase') {
      priceDiv.removeClass('d-none');
    } else {
      priceDiv.addClass('d-none');
      $('#price-per-kg').val('');
    }
  });

  // Modal instance & material name input reference
  const materialPriceModal = new bootstrap.Modal(document.getElementById('materialPriceModal'));
  const materialNameInput = $('#materialName');

  // Function for "Add to Accounts" checkbox in with-weighbridge form
  const addToAccountsCheckbox = $('#addToAccounts');
  function handleAddToAccountsChange() {
    const selectedType = $('#type').val();
    const selectedMaterial = $('#type-material').val();

    if (addToAccountsCheckbox.is(':checked')) {
      if (selectedType === 'Purchase') {
        if (selectedMaterial === '__new__') {
          materialNameInput.val($('#new-material').val());
        } else {
          materialNameInput.val(selectedMaterial);
        }
        materialPriceModal.show();
      } else {
        addToAccountsCheckbox.prop('checked', false);
        materialPriceModal.hide();
      }
    }
  }
  addToAccountsCheckbox.on('change', handleAddToAccountsChange);
  $('#type, #type-material, #new-material').on('change keyup', function() {
    if (addToAccountsCheckbox.is(':checked')) {
      handleAddToAccountsChange();
    }
  });

  // Handler for both checkboxes (#addToAccounts and #addToAccountCheckbox)
  $('#addToAccounts, #addToAccountCheckbox').each(function() {
    $(this).on('change', function () {
      if (this.checked) {
        materialPriceModal.show();
      }
    });
  });

  // Submit handler for material price form
  $('#materialPriceForm').on('submit', function(e) {
    e.preventDefault();

    const materialName = $('#materialName').val();
    const materialPrice = $('#materialPrice').val();
    const clientId = "<?= htmlspecialchars($client_id) ?>";

    fetch('https://sunfra.com/farm/test2/weighbridge/update_material_price.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `client_id=${encodeURIComponent(clientId)}&material_name=${encodeURIComponent(materialName)}&price=${encodeURIComponent(materialPrice)}`
    })
    .then(response => response.json())
    .then(result => {
      if(result.status === "success") {
        alert("Material price updated successfully!");
        materialPriceModal.hide();
      } else {
        alert("Failed to update material price: " + (result.message || "Unknown error"));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert("Failed to update material price.");
    });
  });
});


// Button toggles for with/without weighbridge forms
$('#btn-with-weighbridge').on('click', function() {
  $('#with-weighbridge-form').removeClass('d-none');
  $('#without-weighbridge-form').addClass('d-none');
});

$('#btn-without-weighbridge').on('click', function() {
  $('#without-weighbridge-form').removeClass('d-none');
  $('#with-weighbridge-form').addClass('d-none');
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
