<?php session_start(); ?>
<?php $features = $_SESSION['features'] ?? [];
$clientName = $_SESSION['client_name'] ?? 'Yours';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home</title>
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
<body class="bg-gray-100 text-gray-800" class="bg-gray-300 text-gray-800">
  <div class="bg-blue-700 text-white md:hidden flex items-center justify-between px-4 py-3">
    <div class="text-lg font-bold">🌐 Dashboard</div>
    <button id="menu-btn" class="text-white text-2xl"><i class="fas fa-bars"></i></button>
  </div>

  <div class="flex flex-col md:flex-row min-h-screen">
    <aside id="sidebar" class="bg-blue-500 text-white w-full md:w-64 p-6 space-y-4 md:block hidden">
	  <h2 class="text-2xl font-bold flex items-center gap-2 mb-6">
			<i class="fas fa-home text-white text-xl"></i>
			My Dashboard
	  </h2>

	  <a href="https://sunfra.com/farm/test/batch_json_to_web.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
		<i class="fas fa-globe"></i> Batch
	  </a>

	  <a href="https://sunfra.com/farm/test/weighbridge_json_to_web.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
		<i class="fas fa-truck"></i> WeighBridge
	  </a>

	  <a href="https://sunfra.com/farm/test/tractor_production_mortality_json_to_web.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
		<i class="fas fa-tractor"></i> Tractor Production Mortality
	  </a>

	  <a href="https://sunfra.com/farm/test/test_show_attendance.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
		<i class="fas fa-user-check"></i> Attendance
	  </a>
		<a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
			<i class="fas fa-user-tie"></i> Shead Supervisor
	  </a>
	  <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
			<i class="fas fa-warehouse"></i> Feed Plant Supervisor
	  </a>
	  <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
			<i class="fas fa-egg"></i> Egg Godown Supervisor
	  </a>
	  <a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
			<i class="fas fa-chart-line"></i> Profit And Loss
	  </a>
	  <a href="https://sunfra.com/farm/test/settings.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
			<i class="fas fa-sliders-h"></i> Feature Settings
	  </a>	
	  <a href="https://sunfra.com" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
		<i class="fas fa-life-ring"></i> Support
	  </a>
		<a href="https://sunfra.com/farm/test/logout.php" class="flex items-center gap-3 hover:bg-blue-600 p-2 rounded">
		<i class="fas fa-sign-out-alt"></i> Logout
	  </a>
	</aside>


    <main class="flex-1 p-6">
      <header class="mb-6">
        <h2 class="text-3xl font-bold">Welcome to <?= htmlspecialchars($clientName) ?> Farm 👋</h2>
        <p class="text-gray-600">Your data are just a click away!</p>
      </header>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
		<?php if (in_array('batch', $features)) : ?>
		<a href="https://sunfra.com/farm/test/batch_json_to_web.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(59,130,246,0.5)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-blue-600 text-3xl"><i class="fas fa-globe"></i></div>
			<span class="bg-blue-100 text-blue-800 text-sm font-semibold px-2 py-1 rounded-full"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">Batch</h3>
		  <p class="text-gray-500 text-sm">Go to your check your batches.</p>
		</a>

		<?php endif; ?>

		<?php if (in_array('weighbridge', $features)) : ?>
		<a href="https://sunfra.com/farm/test/weighbridge_json_to_web.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(34,197,94,0.5)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-green-600 text-3xl"><i class="fas fa-truck"></i></div>
			<span class="bg-green-100 text-green-800 text-sm font-semibold px-2 py-1 rounded-full"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">WeighBridge</h3>
		  <p class="text-gray-500 text-sm">Check the vehicle load reports.</p>
		</a>
		<?php endif; ?>

		<?php if (in_array('tractor', $features)) : ?>
		<a href="https://sunfra.com/farm/test/tractor_production_mortality_json_to_web.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(168,85,247,0.5)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-purple-600 text-3xl"><i class="fas fa-tractor"></i></div>
			<span class="bg-purple-100 text-purple-800 text-sm font-semibold px-2 py-1 rounded-full"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">Tractor Production Mortality</h3>
		  <p class="text-gray-500 text-sm">Check the production taken by the tractor.</p>
		</a>
		<?php endif; ?>

		<?php if (in_array('attendance', $features)) : ?>
		<a href="https://sunfra.com/farm/test/test_show_attendance.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(168,85,247,0.5)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-purple-600 text-3xl"><i class="fas fa-user-check"></i></div>
			<span class="bg-purple-100 text-purple-800 text-sm font-semibold px-2 py-1 rounded-full"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">Attendance</h3>
		  <p class="text-gray-500 text-sm">Check today's labours</p>
		</a>
		<?php endif; ?>

		<?php if (in_array('supervisor', $features)) : ?>
		<a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(0,0,0,0.3)] group">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-black text-3xl transition duration-300 group-hover:text-gray-800">
			  <i class="fas fa-user-tie"></i>
			</div>
			<span class="bg-gray-200 text-black text-sm font-semibold px-2 py-1 rounded-full transition duration-300 group-hover:bg-gray-300 group-hover:text-gray-900"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2 text-black">Shead Supervisor</h3>
		  <p class="text-gray-600 text-sm">Check Supervisor Entries</p>
		</a>
		<?php endif; ?>

		<?php if (in_array('feedplant', $features)) : ?>
		<a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(168,85,247,0.5)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-purple-600 text-3xl"><i class="fas fa-warehouse"></i></div>
			<span class="bg-purple-100 text-purple-800 text-sm font-semibold px-2 py-1 rounded-full"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">Feed Plant Supervisor</h3>
		  <p class="text-gray-500 text-sm">Check formulas and stock</p>
		</a>
		<?php endif; ?>
		
		<?php if (in_array('egg_godown', $features)) : ?>
		<a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(253,224,71,0.6)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-yellow-600 text-3xl transition duration-300 group-hover:text-yellow-500">
			  <i class="fas fa-egg group-hover:text-yellow-500 transition duration-300"></i>
			</div>
			<span class="bg-yellow-100 text-yellow-600 text-sm font-semibold px-2 py-1 rounded-full transition duration-300 group-hover:bg-yellow-200 group-hover:text-yellow-700"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">Egg Godown Supervisor</h3>
		  <p class="text-gray-500 text-sm">Check the stock and sale of eggs</p>
		</a>
		<?php endif; ?>
		
		<?php if (in_array('profitandloss', $features)) : ?>
		<a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(34,197,94,0.5)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-green-600 text-3xl"><i class="fas fa-chart-line"></i></div>
			<span class="bg-green-100 text-green-800 text-sm font-semibold px-2 py-1 rounded-full"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">Profit And Loss</h3>
		  <p class="text-gray-500 text-sm">Please check your profit and loss</p>
		</a>
		<?php endif; ?>
		
		<?php if (in_array('task_status', $features)) : ?>
		<a href="https://sunfra.com/farm/test/task/task_status.php"
		   class="bg-white rounded-xl shadow-md p-5 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:shadow-[0_0_15px_rgba(239,68,68,0.5)]">
		  <div class="flex items-center justify-between mb-4">
			<div class="text-red-600 text-3xl"><i class="fas fa-tasks"></i></div>
			<span class="bg-red-100 text-red-800 text-sm font-semibold px-2 py-1 rounded-full"></span>
		  </div>
		  <h3 class="text-lg font-bold mb-2">Task</h3>
		  <p class="text-gray-500 text-sm">Please check and complete the task</p>
		</a>
		<?php endif; ?>

      </div>
    </main>
  </div>
  <script>
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    menuBtn.addEventListener('click', () => {
      sidebar.classList.toggle('hidden');
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
