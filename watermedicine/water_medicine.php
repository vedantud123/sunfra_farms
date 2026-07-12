<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Raw Material Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        button {
            padding: 8px 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        button:hover {
            background-color: #0056b3;
        }
        .add-data, .remove-data {
            text-align: center;
            margin-top: 20px;
        }
        .add-data a, .remove-data a {
            padding: 10px 16px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
        }
        .add-data a {
            background-color: #28a745;
        }
        .add-data a:hover {
            background-color: #218838;
        }
        .remove-data a {
            background-color: #dc3545;
        }
        .remove-data a:hover {
            background-color: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        th {
            background-color: white;
            color: #333;
            font-size: 16px;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9ecef;
        }
		th {
			background-color: #007BFF; /* Blue background */
			color: white; /* White text */
			font-size: 16px;
			font-weight: bold;
			text-align: center;
		}

		thead th {
			background-color: #007BFF; 
			color: white;
		}

		td:first-child, th:first-child {
			background-color: #28a745; /* Green background for first column */
			color: white;
			font-weight: bold;
		}

    </style>
</head>
<body>

<div class="container">
    <button onclick="window.location.href='https://sunfra.com/farm/feed_plant_supervisor.php'">Go Back</button>
    <h1>Feed Formulas</h1>

    <?php
	$allowedUsers = ['vedant', 'divya', 'venkat']; 
	
	if (isset($_SESSION['username']) && in_array($_SESSION['username'], $allowedUsers)) {
        echo '
        <div class="add-data">
            <a href="water_medicine_edit.php">Edit Formula</a>
        </div>';
    }

    $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
	$shead_queries = ['1', '2', '3', '4', '5', '6', '7', '8', 'Chick', 'grower'];
	$egg_data = [];

	foreach ($shead_queries as $shead) {
		$hatchDate = 0;
		$day_query = "SELECT hatchDate FROM `batch` WHERE cullDate = '0000-00-00' AND sheadNo = ?";
		$day_stmt = $mysqli->prepare($day_query);
		
		if ($day_stmt) {
			$day_stmt->bind_param("s", $shead);
			$day_stmt->execute();
			$day_stmt->bind_result($hatchDate);
			$day_stmt->fetch();
			$day_stmt->close();
		} else {
			echo "Error in preparing statement: " . $mysqli->error;
			continue;
		}
		
		if (!empty($hatchDate)) {
			$startDateObj = new DateTime($hatchDate);
			$diff = $startDateObj->diff(new DateTime());
			$runningDays = $diff->days + 1;
		} else {
			$runningDays = "N/A";
		}
		
		if (is_numeric($runningDays)) {
			$runningWeeks = floor($runningDays / 7);
		} else {
			$runningWeeks = "N/A";
		}
		
		$duration = (is_numeric($runningDays) && $runningDays !== "Done") ? strval($runningWeeks) : "Done";
		
		$egg_data[] = [
			"Running_week" => $duration
		];
	}
	
	$delete_null_value_query = "delete fd
		FROM `water_formula_details` fd
		JOIN (
		  SELECT water_medicine_name
		  FROM `water_formula_details`
		  WHERE quantity = '0'
		  GROUP BY water_medicine_name
		  HAVING COUNT(DISTINCT water_formulaType) = 10
		) AS materials_to_delete
		  ON fd.water_medicine_name = materials_to_delete.water_medicine_name
		WHERE fd.quantity = '0'
		  AND fd.water_formulaType IN ('shead_1', 'shead_2', 'shead_3', 'shead_4', 
									  'shead_5', 'shead_6', 'shead_7', 'shead_8', 
									  'grower', 'chick');";
	
	$delete_null_value_result = $mysqli->query($delete_null_value_query);

    $query = "SELECT 
        TYPE,
        water_medicine_name AS Material,
        SUM(CASE WHEN water_formulaType = 'shead_1' THEN quantity ELSE 0 END) AS shead_1,
        SUM(CASE WHEN water_formulaType = 'shead_2' THEN quantity ELSE 0 END) AS shead_2,
        SUM(CASE WHEN water_formulaType = 'shead_3' THEN quantity ELSE 0 END) AS shead_3,
        SUM(CASE WHEN water_formulaType = 'shead_4' THEN quantity ELSE 0 END) AS shead_4,
        SUM(CASE WHEN water_formulaType = 'shead_5' THEN quantity ELSE 0 END) AS shead_5,
        SUM(CASE WHEN water_formulaType = 'shead_6' THEN quantity ELSE 0 END) AS shead_6,
        SUM(CASE WHEN water_formulaType = 'shead_7' THEN quantity ELSE 0 END) AS shead_7,
        SUM(CASE WHEN water_formulaType = 'shead_8' THEN quantity ELSE 0 END) AS shead_8,
        SUM(CASE WHEN water_formulaType = 'chick' THEN quantity ELSE 0 END) AS chick,
        SUM(CASE WHEN water_formulaType = 'grower' THEN quantity ELSE 0 END) AS grower
    FROM 
        `water_formula_details`
    GROUP BY TYPE, water_medicine_name
    ORDER BY 
        FIELD(TYPE,'Water_Medicine', 'Sanitisation'), shead_1 DESC";

    $currentType = "";
    $shead1Total=0;
    $shead2Total=0;
    $shead3Total=0;
    $shead4Total=0;
    $shead5Total=0;
    $shead6Total=0;
    $shead7Total=0;
    $shead8Total=0;
    $sheadCTotal=0;
    $sheadGTotal=0;
	
    if ($result = $mysqli->query($query)) {
    $currentType = ""; 
    $firstRow = true; 

    while ($row = $result->fetch_assoc()) {
        if ($row['TYPE'] !== $currentType) {
            if (!$firstRow) {
                echo "<tr>
                    <td>TOTAL</td>
                    <td>{$shead1Total}</td>
                    <td>{$shead2Total}</td>
                    <td>{$shead3Total}</td>
                    <td>{$shead4Total}</td>
                    <td>{$shead5Total}</td>
                    <td>{$shead6Total}</td>
                    <td>{$shead7Total}</td>
                    <td>{$shead8Total}</td>
                    <td>{$sheadCTotal}</td>
                    <td>{$sheadGTotal}</td>
                </tr>";
                echo '</table>';
            }

            $currentType = $row['TYPE']; 
            echo "<h2>" . htmlspecialchars($currentType) . "</h2>";

            $shead1Total = $shead2Total = $shead3Total = $shead4Total = 
            $shead5Total = $shead6Total = $shead7Total = $shead8Total = 
            $sheadCTotal = $sheadGTotal = 0;

            echo '<table>
                    <thead>
                        <tr>
                            <th>WEEK</th>';
            foreach ($egg_data as $weekRow) {
                echo '<th>' . htmlspecialchars($weekRow['Running_week']) . '</th>';
            }
            echo '   </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Material</th>
                            <th>Shead 1</th>
                            <th>Shead 2</th>
                            <th>Shead 3</th>
                            <th>Shead 4</th>
                            <th>Shead 5</th>
                            <th>Shead 6</th>
                            <th>Shead 7</th>
                            <th>Shead 8</th>
                            <th>Chick</th>
                            <th>Grower</th>
                        </tr>';
        }

        echo "<tr>
            <td>{$row['Material']}</td>
            <td>{$row['shead_1']}</td>
            <td>{$row['shead_2']}</td>
            <td>{$row['shead_3']}</td>
            <td>{$row['shead_4']}</td>
            <td>{$row['shead_5']}</td>
            <td>{$row['shead_6']}</td>
            <td>{$row['shead_7']}</td>
            <td>{$row['shead_8']}</td>
            <td>{$row['chick']}</td>
            <td>{$row['grower']}</td>
        </tr>";

        $shead1Total += $row['shead_1'];
        $shead2Total += $row['shead_2'];
        $shead3Total += $row['shead_3'];
        $shead4Total += $row['shead_4'];
        $shead5Total += $row['shead_5'];
        $shead6Total += $row['shead_6'];
        $shead7Total += $row['shead_7'];
        $shead8Total += $row['shead_8'];
        $sheadCTotal += $row['chick'];
        $sheadGTotal += $row['grower'];

        $firstRow = false; 
    }

    if (!$firstRow) {
        echo "<tr>
            <td>TOTAL</td>
            <td>{$shead1Total}</td>
            <td>{$shead2Total}</td>
            <td>{$shead3Total}</td>
            <td>{$shead4Total}</td>
            <td>{$shead5Total}</td>
            <td>{$shead6Total}</td>
            <td>{$shead7Total}</td>
            <td>{$shead8Total}</td>
            <td>{$sheadCTotal}</td>
            <td>{$sheadGTotal}</td>
        </tr>";
        echo '</table>';
    } else {
        echo '<p>No data found</p>';
    }

    $result->free();
} else {
    echo '<p>No data found</p>';
}

    $mysqli->close();
    ?>
</div>

</body>
</html>
