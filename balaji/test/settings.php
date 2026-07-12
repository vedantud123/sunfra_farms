<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['features'] = $_POST['features'] ?? [];
    header("Location: test_dashboard.php");
    exit;
}
$features = $_SESSION['features'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Feature Toggles</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    .toggle-track {
      width: 48px;
      height: 24px;
      background-color: #d1d5db;
      border-radius: 9999px;
      position: relative;
      transition: background-color 0.3s;
    }
    .toggle-thumb {
      width: 20px;
      height: 20px;
      background-color: white;
      border-radius: 9999px;
      position: absolute;
      top: 2px;
      left: 2px;
      transition: transform 0.3s;
    }
    input:checked + .toggle-track {
      background-color: #10b981;
    }
    input:checked + .toggle-track .toggle-thumb {
      transform: translateX(24px);
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
<body class="bg-gray-100 text-gray-800" class="relative min-h-screen bg-gray-100 py-6 px-4 sm:px-6">

  <div class="absolute inset-0 bg-[url('background.jpg')] bg-repeat bg-[length:80px_80px] opacity-10 blur-sm pointer-events-none z-0"></div>

  <div class="relative z-10 w-full max-w-lg mx-auto bg-white shadow-2xl rounded-2xl px-5 py-6 sm:p-8">
    <h2 class="text-2xl sm:text-3xl font-bold mb-6 text-blue-700 flex items-center gap-3">
      <i class="fas fa-sliders-h"></i> Feature Toggle Settings
    </h2>

    <form method="POST" class="space-y-5">
      <?php
      $options = [
        'batch' => 'Batch',
        'weighbridge' => 'WeighBridge',
        'tractor' => 'Tractor Production',
        'attendance' => 'Attendance',
        'supervisor' => 'Shead Supervisor',
        'feedplant' => 'Feed Plant',
        'egg_godown' => 'Egg Godown',
        'profitandloss' => 'Profit And Loss',
		'task_status' => 'Task'
      ];

      foreach ($options as $key => $label) {
        $isChecked = in_array($key, $features);
        echo '
        <div class="flex items-center justify-between border-b pb-3">
          <span class="text-gray-800 font-medium text-base sm:text-lg">'.$label.'</span>
          <div class="flex items-center gap-2">
            <label class="relative flex items-center cursor-pointer">
              <input type="checkbox" name="features[]" value="'.$key.'" class="sr-only toggle-input" '.($isChecked ? 'checked' : '').'>
              <div class="toggle-track">
                <div class="toggle-thumb"></div>
              </div>
            </label>
            <span class="text-sm font-semibold toggle-label text-gray-600">'.($isChecked ? 'On' : 'Off').'</span>
          </div>
        </div>';
      }
      ?>

      <button type="submit" class="w-full bg-blue-600 text-white text-base sm:text-lg font-semibold py-2 rounded-md hover:bg-blue-700 transition">
        Save Settings
      </button>
    </form>
  </div>

  <script>
    document.querySelectorAll('.toggle-input').forEach(input => {
      const label = input.closest('div').querySelector('.toggle-label');
      input.addEventListener('change', () => {
        label.textContent = input.checked ? 'On' : 'Off';
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
