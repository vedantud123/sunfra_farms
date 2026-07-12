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
<div class="container">
    <button onclick="window.location.href='https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php'">← Go Back</button>
    <h1>Feed Formulas</h1>

    <?php
    $allowedUsers = ['vedant', 'divya', 'venkat'];
    $canEdit = false;

    // ✅ Client 1 — only allowed users can edit
    if ($client_id == 1 && in_array($username, $allowedUsers)) {
        $canEdit = true;
    } 
    // ✅ Other clients — everyone can edit
    elseif ($client_id != 1) {
        $canEdit = true;
    }

    if ($canEdit) {
        echo '<div class="add-data"><a href="feed_formula_edit.php">Edit Formula</a></div>';
    }

    $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // ✅ Only show batches for current tenant
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

    // ✅ Delete 0-quantity entries only for current client
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

    // ✅ Fetch feed formula details only for this tenant
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

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>