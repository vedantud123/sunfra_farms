<?php
session_start();
$client_id = $_SESSION['client_id'] ?? 0; // Get client_id from session
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profit & Loss Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ADD8E6;
        }
        .card {
			border: 1px solid #ddd;
			border-radius: 6px;
			background: white;
			padding: 16px;
			width: 100%;
			overflow-x: auto; /* for wide tables */
		}
        .table thead th {
            background-color: #28a745;
            color: white;
            text-align: center;
        }
        .table tbody td, .table tfoot td {
            text-align: center;
            vertical-align: middle;
        }
        .form-control, .btn {
            border-radius: 8px;
        }
        .form-label {
            font-weight: 500;
        }
        /* Smaller date inputs */
        .date-input {
            max-width: 160px;
        } .sidebar {
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
			}thead {
				position: sticky;
				top: 0;
				background: #28a745;
				color: white;
				z-index: 2;
			}
    </style>
</head>
<body class="p-4">
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
    <div class="d-flex justify-content-center mt-4">
		<div class="card p-4" style="max-width: 500px; width: 100%;">
			<h4 class="text-primary text-center mb-3">Profit & Loss Report</h4>
			<form id="filterForm" class="row g-3 justify-content-center text-center">
				<input type="hidden" id="client_id" value="<?php echo $client_id; ?>">

				<div class="col-6">
					<label for="from_date" class="form-label">From Date</label>
					<input type="date" id="from_date" name="from_date" class="form-control" required>
				</div>

				<div class="col-6">
					<label for="to_date" class="form-label">To Date</label>
					<input type="date" id="to_date" name="to_date" class="form-control" required>
				</div>

				<div class="col-12">
					<button type="submit" class="btn w-100" style="background-color: #28a745; color: white;">Load Data</button>
				</div>
			</form>
		</div>
	</div>

    <div class="card p-4">
		<table id="reportTable" class="table table-striped table-bordered">
			<thead style="position: sticky; top: 0; background: #28a745; color: white; z-index: 2;">
				<tr>
					<th>Shead Name</th>
					<th>Feed Used</th>
					<th>Feed Cost</th>
					<th>Medicine</th>
					<th>Other Cost</th>
					<th>Labour Cost</th>
					<th>Total</th>
					<th>Production</th>
					<th>Egg Cost</th>
					<th>Total Egg Revenue</th>
					<th>Profit</th>
				</tr>
			</thead>
			<tbody></tbody>
			<tfoot>
				<tr style="font-weight:bold; background-color:#f1f1f1;">
					<td>Total</td>
					<td id="t_feed_used"></td>
					<td id="t_feed_cost"></td>
					<td id="t_medicine"></td>
					<td id="t_other_cost"></td>
					<td id="t_labour_cost"></td>
					<td id="t_total"></td>
					<td id="t_production"></td>
					<td id="t_egg_cost"></td>
					<td id="t_total_egg_revenue"></td>
					<td id="t_profit"></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
$(document).ready(function () {
    let table = $('#reportTable').DataTable({
        paging: false,   
        info: false,
        searching: false 
    });

    let today = new Date().toISOString().split('T')[0];
    $('#from_date').val(today);
    $('#to_date').val(today);

    function loadReport() {
        let clientId = $('#client_id').val();
        let fromDate = $('#from_date').val();
        let toDate = $('#to_date').val();

        if (!clientId || !fromDate || !toDate) {
            alert("Please fill all fields");
            return;
        }

        let apiUrl = `https://sunfra.com/farm/test2/profit_and_loss_details/profit_loss_json.php?from_date=${fromDate}&to_date=${toDate}&client_id=${clientId}`;

        $.getJSON(apiUrl, function (response) {
            if (response.status === "success") {
                table.clear();

                response.data.forEach(row => {
                    table.row.add([
                        row.shead_name,
                        row.feed_used,
                        row.feed_cost,
                        row.medicine,
                        row.other_cost,
                        row.labour_cost,
                        row.total,
                        row.production,
                        row.egg_cost,
                        row.total_egg_revenue,
                        row.profit
                    ]);
                });

                table.draw();

                $('#t_feed_used').text(response.total.feed_used);
                $('#t_feed_cost').text(response.total.feed_cost);
                $('#t_medicine').text(response.total.medicine);
                $('#t_other_cost').text(response.total.other_cost);
                $('#t_labour_cost').text(response.total.labour_cost);
                $('#t_total').text(response.total.total);
                $('#t_production').text(response.total.production);
                $('#t_egg_cost').text(response.total.egg_cost);
                $('#t_total_egg_revenue').text(response.total.total_egg_revenue);
                $('#t_profit').text(response.total.profit);
            } else {
                alert("No data found");
                table.clear().draw();
            }
        });
    }

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        loadReport();
    });

    loadReport();
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
