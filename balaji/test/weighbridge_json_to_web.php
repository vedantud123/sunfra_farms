<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
$client_id = $_SESSION['client_id'] ?? 0;
$clientName = $_SESSION['client_name'] ?? 'Yours';

date_default_timezone_set('Asia/Kolkata');

$json = file_get_contents("https://sunfra.com/farm/test/weighbridge_json.php");
$data = json_decode($json, true);
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$thisMonth = date('Y-m');
$thisYear = date('Y');

$raw_json = file_get_contents("https://sunfra.com/farm/test/feedrawmaterial/feed_raw_material_json.php");
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
  <title><?= htmlspecialchars($clientName) ?> - WeighBridge Dashboard</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
	<script src="https://cdn.tailwindcss.com"></script>

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
    }
    .sidebar {
      height: 100vh;
      background-color: #0d6efd;
      color: #fff;
      padding: 20px;
      position: fixed;
      width: 250px;
      overflow-y: auto;
    }
    .sidebar a {
      color: #fff;
      text-decoration: none;
      display: block;
      padding: 10px 0;
      border-radius: 4px;
    }
    .sidebar a:hover {
      background-color: #084298;
    }.sidebar.collapsed {
	  width: 64px; /* adjust small width */
	}
	.sidebar.collapsed a .sidebar-text {
	  display: none;
	}

    @media (max-width: 768px) {
      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
      }
    }.sidebar.collapsed {
	  width: 64px !important;
	  /* Optional: prevent text selection or overflow */
	  overflow-x: hidden;
	}

	/* Hide the text labels inside links */
	.sidebar.collapsed a .sidebar-text {
	  display: none;
	}

	/* Center icons inside links horizontally and vertically */
	.sidebar.collapsed nav a {
	  justify-content: center;      /* center items horizontally */
	  display: flex !important;     /* ensure flexbox */
	  align-items: center;          /* center items vertically */
	  padding-left: 0 !important;   /* remove left padding to center icon */
	}

	/* Optional: Adjust the icon size or margin for better centering */
	.sidebar.collapsed nav a i {
	  margin: 0; /* remove icon margin if any */
	  font-size: 1.2rem; /* optional: increase/decrease icon size */
	}

	/* (Optional) Add hover effect full width on expanded sidebar, but limited in collapsed */
	.sidebar nav a:hover {
	  background-color: #2b6cb0;
	  color: white;
	  transition: background-color 0.3s ease;
	}

	@media (max-width: 768px) {
	  .sidebar {
		position: fixed;
		top: 0; left: 0; height: 100vh; z-index: 1050;
		width: 250px;
		background-color: #0d6efd;
		padding-top: 60px; /* room for header if any */
		transform: translateX(-100%);
		transition: transform 0.3s ease;
	  }
	  .sidebar.show {
		transform: translateX(0);
	  }
	  .sidebar-backdrop {
		display: none;
		position: fixed;
		top:0; left:0; width:100vw; height:100vh;
		background: rgba(0,0,0,0.5);
		z-index: 1040;
	  }
	  .sidebar-backdrop.show {
		display: block;
	  }
	  .main-content {
		margin-left: 0 !important; /* Remove sidebar margin on mobile */
	  }
	}
	.main-content {
	  margin-left: 250px; /* same as sidebar width */
	  transition: margin-left 0.3s ease;
	}

	.main-content.collapsed {
	  margin-left: 64px; /* same as collapsed sidebar width */
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

<div class="d-flex">
    <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

	
	<aside id="sidebar" class="sidebar w-full md:w-64 bg-blue-800 text-white p-4 md:block hidden">
	 <div class="d-flex align-items-center justify-content-between mb-4">
	  <button id="collapse-btn" class="btn btn-outline-light btn-sm">
		<i class="fas fa-angle-double-left"></i>
	  </button>
	</div>
	  <nav class="space-y-2">
		<a href="test_dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600 ">
		  <i class="fas fa-home"></i> <span class="sidebar-text">Home</span>
		</a>
		<a href="batch_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-globe"></i> <span class="sidebar-text">Batch</span>
        </a>
        <a href="weighbridge_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-truck"></i> <span class="sidebar-text">WeighBridge</span>
        </a>
        <a href="tractor_production_mortality_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-tractor"></i> <span class="sidebar-text">Tractor Production Mortality</span>
        </a>
        <a href="test_show_attendance.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-check"></i> <span class="sidebar-text">Attendance</span>
        </a>
        <a href="test_show_shead_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-tie"></i> <span class="sidebar-text">Shead Supervisor</span>
        </a>
        <a href="test_show_feed_plant_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-warehouse"></i> <span class="sidebar-text">Feed Plant Supervisor</span>
        </a>
        <a href="egg_godown/egg_godown.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-egg"></i> <span class="sidebar-text">Egg Godown Supervisor</span>
        </a>
        <a href="test_show_profit_loss_details.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-chart-line"></i> <span class="sidebar-text">Profit And Loss</span>
        </a>
        <a href="settings.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-sliders-h"></i> <span class="sidebar-text">Feature Settings</span>
        </a>
        <a href="https://sunfra.com" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-life-ring"></i> <span class="sidebar-text">Support</span>
        </a>
        <a href="logout.php" class="flex items-center gap-3 p-2 rounded hover:bg-red-600">
          <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text">Logout</span>
        </a>	
		</nav>
	</aside>

	<main class="flex-grow-1 main-content">
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
        <a href="https://sunfra.com/farm/test/test_dashboard.php" class="btn btn-primary me-2">Home</a>
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
      <form action="https://sunfra.com/farm/test/weighbridge_save.php" method="POST" id="with-weighbridge-form" class="d-none p-3">
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

      <form action="https://sunfra.com/farm/test/weighbridge_save.php" method="POST" id="without-weighbridge-form" class="d-none p-3">
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
		<div class="form-check mb-3 ms-2">
		  <input class="form-check-input" type="checkbox" name="add_to_accounts" value="1" id="addToAccounts">
		  <label class="form-check-label" for="addToAccounts">
			Add to Accounts
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
      <form action="https://sunfra.com/farm/test/weighbridge_save.php" method="POST">

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
  </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

  $('#type').on('change', function() {
    const priceDiv = $('#price-per-kg-div');
    if (this.value === 'Purchase') {
      priceDiv.removeClass('d-none');
    } else {
      priceDiv.addClass('d-none');
      $('#price-per-kg').val(''); // Clear input when hidden
    }
  });
});
$(document).ready(function() {
	$('#collapse-btn').on('click', function() {
    $('.sidebar').toggleClass('collapsed');
    $('.main-content').toggleClass('collapsed');
  });
  
  $('#addModal').on('show.bs.modal', function () {
    $('#with-weighbridge-form').addClass('d-none');
    $('#without-weighbridge-form').addClass('d-none');
  });

  $('#btn-with-weighbridge').on('click', function() {
    $('#with-weighbridge-form').removeClass('d-none');
    $('#without-weighbridge-form').addClass('d-none');
  });

  $('#btn-without-weighbridge').on('click', function() {
    $('#without-weighbridge-form').removeClass('d-none');
    $('#with-weighbridge-form').addClass('d-none');
  });
})
 document.getElementById('menu-btn').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
  });

  document.getElementById('collapse-btn').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('collapsed');
  });
$(document).ready(function() {
  const $sidebar = $('#sidebar');
  const $backdrop = $('#sidebar-backdrop');

  $('#menu-btn').click(function() {
    $sidebar.addClass('show');
    $backdrop.addClass('show');
  });

  $backdrop.click(function() {
    $sidebar.removeClass('show');
    $backdrop.removeClass('show');
  });

  // Optional: close sidebar when a link is clicked on mobile
  $sidebar.find('a').click(function() {
    if($(window).width() < 768){
      $sidebar.removeClass('show');
      $backdrop.removeClass('show');
    }
  });

  // your existing collapse button handling...
  $('#collapse-btn').click(function() {
    $sidebar.toggleClass('collapsed');
    $('.main-content').toggleClass('collapsed');
  });
});

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
