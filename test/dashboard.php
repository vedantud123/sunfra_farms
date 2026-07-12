<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$client_id = $_SESSION['client_id'] ?? 0;

$from_date = $_POST['from_date'] ?? date('Y-m-d');
$to_date = $_POST['to_date'] ?? date('Y-m-d');

$sql = "SELECT * FROM `summary_report` WHERE `date` BETWEEN ? AND ? AND client_id= ? order by date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $from_date, $to_date, $client_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Summary</title>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
     body {
		font-family: Arial, sans-serif;
	}

	h2 {
		text-align: center;
		margin-top: 20px;
	}

	.filter-form {
		width: fit-content;
		margin: 20px auto;
		display: flex;
		gap: 10px;
		align-items: center;
		background-color: #f2f2f2;
		padding: 10px 15px;
		border: 1px solid #ccc;
		border-radius: 6px;
	}

	.filter-form label {
		font-size: 14px;
		color: #333;
		margin-right: 5px;
	}

	.filter-form input[type="date"] {
		padding: 5px;
		font-size: 14px;
		border: 1px solid #aaa;
		border-radius: 4px;
		width: 140px;
	}

	.filter-form input[type="submit"] {
		padding: 6px 12px;
		font-size: 14px;
		background-color: #2e8b57;
		color: white;
		border: none;
		border-radius: 4px;
		cursor: pointer;
	}

	.filter-form input[type="submit"]:hover {
		background-color: #256f45;
	}

	.table-container {
		overflow-x: auto;
		max-width: 90%;
		margin: 0 auto;
		border: 1px solid #ccc;
	}

	table {
		border-collapse: collapse;
		width: max-content;
		font-size: 15px;
		min-width: 1200px;
	}

	.separator {
		background-color: green !important;
		border-left: none !important;
		border-right: none !important;
		width: 4px;
		padding: 1 !important;
	}

	th, td {
		border: 1px solid #999;
		padding: 6px;
		text-align: center;
		background-color: white;
		white-space: nowrap;
	}

	th {
		background-color: #2e8b57;
		color: white;
	}

	tr:nth-child(even) {
		background-color: #f9f9f9;
	}

	/* Sticky first column (Date) */
	.sticky-col {
		position: sticky;
		left: 0;
		background-color: #ffffff;
		z-index: 2;
		min-width: 120px;
	}

	/* Also sticky header with better visibility */
	thead th {
		position: sticky;
		top: 0;
		z-index: 3;
	}
	.sticky-col-2 {
		position: sticky;
		left: 120px; /* Adjust based on the width of the first column */
		background-color: green !important;
		z-index: 2;
		width: 4px;
	}.back-button {
	  display: block;
	  width: fit-content;
	  margin: 20px auto; /* Centers horizontally */
	  padding: 10px 20px;
	  background-color: #3498db;
	  color: white;
	  text-decoration: none;
	  border-radius: 5px;
	  font-weight: bold;
	  transition: background-color 0.3s ease;
	}

	.back-button:hover {
	  background-color: #2980b9;
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
	<script>
		window.onload = function () {
			const clientId = <?= json_encode($_SESSION['client_id'] ?? 0) ?>;

			fetch(`https://sunfra.com/farm/test/dashboard_data.php?client_id=${clientId}`)
				.then(response => {
					if (!response.ok) {
						console.error('Script request failed: dashboard_data.php');
					}
					return response.json();
				})
				.then(data => {
					console.log(data);
				})
				.catch(error => console.error('Fetch error:', error));
		};
	</script>
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

	<h2>Summary Report</h2>
	<a class="back-button" href="https://sunfra.com/farm/test/test_show_profit_loss_details.php">Go Back</a>
	<form method="post" class="filter-form">
		<label for="from_date">From:</label>
		<input type="date" name="from_date" id="from_date" >

		<label for="to_date">To:</label>
		<input type="date" name="to_date" id="to_date" >

		<input type="submit" value="Filter">
	</form>

<table>
	<tr>
        <th class="sticky-col" rowspan="2" style="background-color: #FFA500; color: white; rowspan="2">Date</th>
		<th class="separator sticky-col-2" rowspan="2"></th>
		<th colspan="10" style="background-color: #FFA500; color: white;">Production</th>
        <th class="separator" rowspan="2"></th>
        <th colspan="9" style="background-color: #FFA500; color: white;">Damage</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="9" style="background-color: #FFA500; color: white;">Percentage</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="10" style="background-color: #FFA500; color: white;">Feed Intake</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="10" style="background-color: #FFA500; color: white;">Mortality</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="8" style="background-color: #FFA500; color: white;">Egg Weight</th>
		<th class="separator" rowspan="2"></th>
        <th colspan="11" style="background-color: #FFA500; color: white;">Profit And Loss</th>
    </tr>
    <tr>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>Total</th>
        <th>Scrap</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>Total</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>Average</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>CH</th>
		<th>GR</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>CH</th>
		<th>GR</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
		<th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
        <th>5</th>
        <th>6</th>
        <th>7</th>
        <th>8</th>
        <th>CH</th>
		<th>GR</th>
		<th>Total</th>
    </tr>

    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
				<td class="sticky-col"><?php echo $row["date"]; ?></td>
				<th class="separator sticky-col-2" rowspan="1"></th>
                <td><?php echo $row["production1"]; ?></td>
                <td><?php echo $row["production2"]; ?></td>
                <td><?php echo $row["production3"]; ?></td>
                <td><?php echo $row["production4"]; ?></td>
                <td><?php echo $row["production5"]; ?></td>
                <td><?php echo $row["production6"]; ?></td>
                <td><?php echo $row["production7"]; ?></td>
                <td><?php echo $row["production8"]; ?></td>
                <td><?php echo $row["total_production"]; ?></td>
                <td><?php echo $row["total_scrap"]; ?></td>
                <td class="separator"></td> 
                <td><?php echo $row["damage1"]; ?></td>
                <td><?php echo $row["damage2"]; ?></td>
                <td><?php echo $row["damage3"]; ?></td>
                <td><?php echo $row["damage4"]; ?></td>
                <td><?php echo $row["damage5"]; ?></td>
                <td><?php echo $row["damage6"]; ?></td>
                <td><?php echo $row["damage7"]; ?></td>
                <td><?php echo $row["damage8"]; ?></td>
				<td><?php echo $row["total_damage"]; ?></td>
                <td class="separator"></td> 
				<td><?php echo $row["percentage1"]; ?></td>
                <td><?php echo $row["percentage2"]; ?></td>
                <td><?php echo $row["percentage3"]; ?></td>
                <td><?php echo $row["percentage4"]; ?></td>
                <td><?php echo $row["percentage5"]; ?></td>
                <td><?php echo $row["percentage6"]; ?></td>
                <td><?php echo $row["percentage7"]; ?></td>
                <td><?php echo $row["percentage8"]; ?></td>
				<td><?php echo $row["average_percentage"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["feedintake1"]; ?></td>
                <td><?php echo $row["feedintake2"]; ?></td>
                <td><?php echo $row["feedintake3"]; ?></td>
                <td><?php echo $row["feedintake4"]; ?></td>
                <td><?php echo $row["feedintake5"]; ?></td>
                <td><?php echo $row["feedintake6"]; ?></td>
                <td><?php echo $row["feedintake7"]; ?></td>
                <td><?php echo $row["feedintake8"]; ?></td>
				<td><?php echo $row["feedintakeChick"]; ?></td>
                <td><?php echo $row["feedintakeGrower"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["mortality1"]; ?></td>
                <td><?php echo $row["mortality2"]; ?></td>
                <td><?php echo $row["mortality3"]; ?></td>
                <td><?php echo $row["mortality4"]; ?></td>
                <td><?php echo $row["mortality5"]; ?></td>
                <td><?php echo $row["mortality6"]; ?></td>
                <td><?php echo $row["mortality7"]; ?></td>
                <td><?php echo $row["mortality8"]; ?></td>
				<td><?php echo $row["mortalityChick"]; ?></td>
                <td><?php echo $row["mortalityGrower"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["eggweight1"]; ?></td>
                <td><?php echo $row["eggweight2"]; ?></td>
                <td><?php echo $row["eggweight3"]; ?></td>
                <td><?php echo $row["eggweight4"]; ?></td>
                <td><?php echo $row["eggweight5"]; ?></td>
                <td><?php echo $row["eggweight6"]; ?></td>
                <td><?php echo $row["eggweight7"]; ?></td>
                <td><?php echo $row["eggweight8"]; ?></td>
				<td class="separator"></td>
				<td><?php echo $row["profitloss1"]; ?></td>
                <td><?php echo $row["profitloss2"]; ?></td>
                <td><?php echo $row["profitloss3"]; ?></td>
                <td><?php echo $row["profitloss4"]; ?></td>
                <td><?php echo $row["profitloss5"]; ?></td>
                <td><?php echo $row["profitloss6"]; ?></td>
                <td><?php echo $row["profitloss7"]; ?></td>
                <td><?php echo $row["profitloss8"]; ?></td>
				<td><?php echo $row["profitlossChick"]; ?></td>
                <td><?php echo $row["profitlossGrower"]; ?></td>
                <td><?php echo $row["total_profit_loss"]; ?></td>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="17">No data found</td></tr>
    <?php endif; ?>

</table>
</main>
</div>
<script>
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
