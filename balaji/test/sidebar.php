<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 
$clientName = $_SESSION['client_name'] ?? 'Yours';
?>

<div class="bg-blue-700 text-white md:hidden flex items-center justify-between px-4 py-3">
  <div class="text-lg font-bold">🌐 <?= htmlspecialchars($clientName) ?> Dashboard</div>
  <button id="menu-btn" class="text-white text-2xl"><i class="fas fa-bars"></i></button>
</div>

<aside id="sidebar" class="sidebar w-full md:w-64 bg-blue-800 text-white p-4 md:block hidden">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-semibold sidebar-text"><?= htmlspecialchars($clientName) ?></h2>
    <button id="collapse-btn" class="text-white hidden md:inline-block">
      <i class="fas fa-angle-double-left"></i>
    </button>
  </div>

  <nav class="space-y-2">
    <a href="test_dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600 active:bg-blue-700">
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

<script>
  document.getElementById('menu-btn')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
  });

  document.getElementById('collapse-btn')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('collapsed');
  });
</script>
