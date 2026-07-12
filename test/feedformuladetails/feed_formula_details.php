<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location:../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Raw Material Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 850px;
            margin: 50px auto;
            padding: 25px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        button {
            padding: 8px 14px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .add-data a {
            display: inline-block;
            padding: 10px 18px;
            margin-top: 10px;
            background-color: #28a745;
            color: white;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
        }
        .add-data a:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ccc;
        }
        thead th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        td:first-child, th:first-child {
            background-color: #28a745;
            color: white;
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
		.back-button {
		  display: inline-block;
		  margin-bottom: 1rem;
		  background-color: #3498db; /* Blue background */
		  color: white;
		  padding: 10px 16px;
		  border-radius: 6px;
		  text-decoration: none;
		  font-weight: 600;
		  transition: background-color 0.3s ease;
		}

		.back-button:hover {
		  background-color: #217dbb; /* Darker blue on hover */
		}.centered {
		  text-align: center;
		  margin-bottom: 1rem; /* optional spacing */
		}

    </style>
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
<div class="container">
	<div class="centered">
	  <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="back-button">
		← Go Back
	  </a>
	</div>
	<h1>Feed Formulas</h1>

    <?php
	$allowedUsers = ['vedant', 'divya', 'venkat'];
	$canEdit = false;

	if ($client_id == 1 && in_array($username, $allowedUsers)) {
		$canEdit = true;
	} 
	elseif ($client_id != 1) {
		$canEdit = true;
	}

	if ($canEdit) {
		echo '<div class="centered add-data"><a href="feed_formula_edit.php">Edit Formula</a></div>';
	}

	$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $shead_queries = ['1', '2', '3', '4', '5', '6', '7', '8', 'Chick', 'grower'];
    $egg_data = [];

    foreach ($shead_queries as $shead) {
        $hatchDate = '';
        $stmt = $mysqli->prepare("SELECT hatchDate FROM batch WHERE cullDate IS NULL AND sheadNo = ? AND client_id = ?");
        $stmt->bind_param("si", $shead, $client_id);
        $stmt->execute();
        $stmt->bind_result($hatchDate);
        $stmt->fetch();
        $stmt->close();

        if (!empty($hatchDate)) {
            $start = new DateTime($hatchDate);
            $today = new DateTime();
            $diff = $start->diff($today)->days + 1;
            $week = floor($diff / 7);
        } else {
            $week = "N/A";
        }

        $egg_data[] = ["Running_week" => $week];
    }

    $delete = "
        DELETE fd
        FROM feed_formula_detail fd
        JOIN (
            SELECT feed_rawmaterial_name
            FROM feed_formula_detail
            WHERE quantity = 0 AND client_id = $client_id
            GROUP BY feed_rawmaterial_name
            HAVING COUNT(DISTINCT feed_formulaType) = 10
        ) AS to_delete
        ON fd.feed_rawmaterial_name = to_delete.feed_rawmaterial_name
        WHERE fd.quantity = 0 AND fd.client_id = $client_id
        AND fd.feed_formulaType IN ('shead_1','shead_2','shead_3','shead_4','shead_5','shead_6','shead_7','shead_8','grower','chick')";
    $mysqli->query($delete);

    $sql = "
        SELECT 
            TYPE,
            feed_rawmaterial_name AS Material,
            SUM(CASE WHEN feed_formulaType = 'shead_1' THEN quantity ELSE 0 END) AS shead_1,
            SUM(CASE WHEN feed_formulaType = 'shead_2' THEN quantity ELSE 0 END) AS shead_2,
            SUM(CASE WHEN feed_formulaType = 'shead_3' THEN quantity ELSE 0 END) AS shead_3,
            SUM(CASE WHEN feed_formulaType = 'shead_4' THEN quantity ELSE 0 END) AS shead_4,
            SUM(CASE WHEN feed_formulaType = 'shead_5' THEN quantity ELSE 0 END) AS shead_5,
            SUM(CASE WHEN feed_formulaType = 'shead_6' THEN quantity ELSE 0 END) AS shead_6,
            SUM(CASE WHEN feed_formulaType = 'shead_7' THEN quantity ELSE 0 END) AS shead_7,
            SUM(CASE WHEN feed_formulaType = 'shead_8' THEN quantity ELSE 0 END) AS shead_8,
            SUM(CASE WHEN feed_formulaType = 'chick' THEN quantity ELSE 0 END) AS chick,
            SUM(CASE WHEN feed_formulaType = 'grower' THEN quantity ELSE 0 END) AS grower
        FROM feed_formula_detail
        WHERE client_id = $client_id
        GROUP BY TYPE, feed_rawmaterial_name
        ORDER BY FIELD(TYPE, 'Feed_Formula', 'Feed_Medicine', 'Water_Medicine', 'Sanitisation'), shead_1 DESC
    ";

    if ($result = $mysqli->query($sql)) {
        $currentType = '';
        $firstRow = true;

        $totals = array_fill_keys(['shead_1','shead_2','shead_3','shead_4','shead_5','shead_6','shead_7','shead_8','chick','grower'], 0);

        while ($row = $result->fetch_assoc()) {
            if ($row['TYPE'] !== $currentType) {
                if (!$firstRow) {
                    echo "<tr><td><b>TOTAL</b></td>";
                    foreach ($totals as $val) echo "<td><b>$val</b></td>";
                    echo "</tr></tbody></table>";
                }

                $currentType = $row['TYPE'];
                echo "<h2>" . htmlspecialchars($currentType) . "</h2>";

                foreach ($totals as $key => $val) $totals[$key] = 0;

                echo "<table><thead><tr><th>WEEK</th>";
                foreach ($egg_data as $weekRow) {
                    echo "<th>" . htmlspecialchars($weekRow['Running_week']) . "</th>";
                }
                echo "</tr><tr><th>Material</th><th>Shead 1</th><th>Shead 2</th><th>Shead 3</th><th>Shead 4</th><th>Shead 5</th><th>Shead 6</th><th>Shead 7</th><th>Shead 8</th><th>Chick</th><th>Grower</th></tr></thead><tbody>";
            }

            echo "<tr><td>{$row['Material']}</td>";
            foreach (array_keys($totals) as $col) {
                echo "<td>{$row[$col]}</td>";
                $totals[$col] += $row[$col];
            }
            echo "</tr>";
            $firstRow = false;
        }

        if (!$firstRow) {
            echo "<tr><td><b>TOTAL</b></td>";
            foreach ($totals as $val) echo "<td><b>$val</b></td>";
            echo "</tr></tbody></table>";
        } else {
            echo "<p>No data found</p>";
        }

        $result->free();
    } else {
        echo "<p>Error executing query.</p>";
    }

    $mysqli->close();
    ?>
</div>
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