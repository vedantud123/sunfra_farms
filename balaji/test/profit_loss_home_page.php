<?php
$total_sales = 120000;
$total_expenses = 95000;

$profit = max($total_sales - $total_expenses, 0);
$loss = max($total_expenses - $total_sales, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profit Loss Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

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
<body class="bg-gray-100 text-gray-800" class="bg-gray-100 min-h-screen font-sans">

<header class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-5 px-6 shadow-md">
  <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:justify-between items-start sm:items-center gap-2">
    <h1 class="text-2xl font-bold tracking-wide">📊 Dashboard</h1>
    <div class="flex items-center gap-4">
		<a href="https://sunfra.com/farm/test/test_dashboard.php" class="bg-white text-blue-600 text-sm font-semibold px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition">
			<i class="fas fa-home mr-2"></i> Home
      </a>
      <p class="text-sm font-semibold opacity-90">Welcome</p>
      
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-6 space-y-10">

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white p-5 rounded-2xl shadow hover:shadow-lg transition">
      <div class="text-blue-600 text-3xl mb-3"><i class="fas fa-user-check"></i></div>
      <h3 class="text-lg font-semibold mb-1">Attendance</h3>
      <p class="text-gray-500 text-sm">Today’s worker check-in</p>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow hover:shadow-lg transition">
      <div class="text-red-500 text-3xl mb-3"><i class="fas fa-tasks"></i></div>
      <h3 class="text-lg font-semibold mb-1">Tasks</h3>
      <p class="text-gray-500 text-sm">Work progress status</p>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow hover:shadow-lg transition">
      <div class="text-yellow-500 text-3xl mb-3"><i class="fas fa-egg"></i></div>
      <h3 class="text-lg font-semibold mb-1">Egg Stock</h3>
      <p class="text-gray-500 text-sm">Latest egg inventory</p>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow hover:shadow-lg transition">
      <div class="text-purple-600 text-3xl mb-3"><i class="fas fa-tractor"></i></div>
      <h3 class="text-lg font-semibold mb-1">Production</h3>
      <p class="text-gray-500 text-sm">Tractor output status</p>
    </div>
  </div>

<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

$date = date('Y-m-d');

$sheadList = ["'Shead 1'", "'Shead 2'", "'Shead 3'", "'Shead 4'", "'Shead 5'", "'Shead 6'", "'Shead 7'", "'Shead 8'"];
$sheadListStr = implode(',', $sheadList);

$query = "SELECT shead_name, profit FROM profit_and_loss WHERE DATE(datetime) = '$date' AND shead_name IN ($sheadListStr)";
$result = $mysqli->query($query);

$sheads = [];
$shead_profits = [];
$total_profit = 0;

while ($row = $result->fetch_assoc()) {
    $sheads[] = $row['shead_name'];
    $profit_value = (float)$row['profit'];
    $shead_profits[] = $profit_value;
    $total_profit += $profit_value;
}

$total_profit_display = number_format($total_profit, 2);

?>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
	<div class="bg-white p-6 rounded-2xl shadow">
	  <h3 class="text-lg font-semibold mb-4">Shead-wise Profit / Loss (<?php echo $date; ?>)</h3>
	  <canvas id="sheadProfitChart" height="300"></canvas>

	  <div class="mt-4 text-center text-lg font-semibold <?php echo ($total_profit >= 0) ? 'text-green-600' : 'text-red-600'; ?>">
		<?php
		if ($total_profit >= 0) {
			echo "Total Profit: ₹" . $total_profit_display;
		} else {
			echo "Total Loss: ₹" . $total_profit_display;
		}
		?>
	  </div>
	</div>


    <div class="bg-white p-6 rounded-2xl shadow">
      <h3 class="text-lg font-semibold mb-4">Profit & Loss Overview</h3>
      <canvas id="profitLossChart" height="200"></canvas>
    </div>
	<div class="mb-6">
	  <form method="GET" class="flex items-center gap-4">
		<label class="font-semibold">Select Duration:</label>
		<select name="filter" onchange="this.form.submit()" class="p-2 rounded border">
		  <option value="today" <?php if($_GET['filter'] == 'today') echo 'selected'; ?>>Today</option>
		  <option value="yesterday" <?php if($_GET['filter'] == 'yesterday') echo 'selected'; ?>>Yesterday</option>
		  <option value="weekly" <?php if($_GET['filter'] == 'weekly') echo 'selected'; ?>>This Week</option>
		  <option value="monthly" <?php if($_GET['filter'] == 'monthly') echo 'selected'; ?>>This Month</option>
		  <option value="yearly" <?php if($_GET['filter'] == 'yearly') echo 'selected'; ?>>This Year</option>
		  <option value="total" <?php if($_GET['filter'] == 'total') echo 'selected'; ?>>Total Till Now</option>
		</select>
	  </form>
	</div>
  </div>
</main>
<script>
  const sheads = <?php echo json_encode($sheads); ?>;
	const profits = <?php echo json_encode($shead_profits); ?>;

	const colors = profits.map(value => value >= 0 ? '#10b981' : '#ef4444');

	const ctxSheadProfit = document.getElementById('sheadProfitChart').getContext('2d');
	new Chart(ctxSheadProfit, {
	  type: 'bar',
	  data: {
		labels: sheads,
		datasets: [{
		  label: 'Profit / Loss (₹)',
		  data: profits,
		  backgroundColor: colors
		}]
	  },
	  options: {
		responsive: true,
		scales: {
		  y: {
			beginAtZero: true,
			ticks: {
			  callback: function(value) {
				return '₹' + value;
			  }
			}
		  }
		},
		plugins: {
		  legend: { display: false }
		}
	  }
	});

	  const profit = <?php echo $profit; ?>;
	  const loss = <?php echo $loss; ?>;

	  const doughnutCtx = document.getElementById('profitLossChart').getContext('2d');
	  new Chart(doughnutCtx, {
		type: 'doughnut',
		data: {
		  labels: ['Profit', 'Loss'],
		  datasets: [{
			data: [profit, loss],
			backgroundColor: ['#10b981', '#ef4444'],
			borderWidth: 1
		  }]
		},
		options: {
		  responsive: true,
		  cutout: '70%',
		  plugins: {
			legend: {
			  position: 'bottom'
			}
		  }
		}
	  });
	
	const breakdownCtx = document.getElementById('totalBreakdownChart').getContext('2d');

	new Chart(breakdownCtx, {
	  type: 'bar',
	  data: {
		labels: <?php echo json_encode($labels); ?>,
		datasets: [{
		  label: 'Amount (₹)',
		  data: <?php echo json_encode($data); ?>,
		  backgroundColor: [
			'#3b82f6', '#f59e0b', '#f97316', '#8b5cf6', '#10b981', '#ef4444',
			'#6366f1', '#e11d48', '#22c55e', '#facc15', '#14b8a6', '#f43f5e'
		  ],
		  borderWidth: 1,
		  barThickness: 25 
		}]
	  },
	  options: {
		indexAxis: 'y',
		responsive: true,
		maintainAspectRatio: false, 
		scales: {
		  x: {
			beginAtZero: true,
			ticks: {
			  callback: function(value) {
				return '₹' + value;
			  },
			  color: '#374151', 
			  font: {
				size: 14
			  }
			},
			grid: {
			  color: '#e5e7eb' 
			}
		  },
		  y: {
			ticks: {
			  color: '#374151',
			  font: {
				size: 14
			  }
			}
		  }
		},
		plugins: {
		  legend: {
			display: false
		  },
		  tooltip: {
			callbacks: {
			  label: function(context) {
				return '₹' + context.parsed.x;
			  }
			}
		  }
		}
	  }
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
