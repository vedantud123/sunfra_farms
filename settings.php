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
  <meta charset="UTF-8">
  <title>Settings</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
</head>
<body class="bg-gray-100 py-12 px-4 relative">

  <div class="absolute inset-0 z-0 opacity-10 pointer-events-none bg-[url('background.jpg')] bg-repeat bg-[length:100px_100px] blur-sm"></div>

  <div class="relative z-10 max-w-2xl mx-auto bg-white shadow-2xl rounded-xl p-8">
    <h1 class="text-3xl font-bold text-blue-700 mb-6 flex items-center gap-3">
      <i class="fas fa-sliders-h"></i> Settings Panel
    </h1>


    <form method="POST" class="space-y-6">
      <?php
      $options = [
        'batch' => 'Batch',
        'weighbridge' => 'WeighBridge',
        'tractor' => 'Tractor Production',
        'attendance' => 'Attendance',
        'supervisor' => 'Shead Supervisor',
        'feedplant' => 'Feed Plant'
      ];

      foreach ($options as $key => $label) {
        $isChecked = in_array($key, $features);
        echo '
        <div class="flex items-center justify-between border-b pb-3">
          <span class="text-gray-800 font-medium text-lg">'.$label.'</span>
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

      <div class="pt-4">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white text-lg font-semibold py-2 px-4 rounded-md shadow">
          Save Settings
        </button>
      </div>
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

</body>
</html>
