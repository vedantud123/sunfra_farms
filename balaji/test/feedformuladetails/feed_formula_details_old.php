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
    <button onclick="window.location.href='https://sunfra.com/farm/feed_plant_supervisor.php'">Go Back</button>
    <h1>Feed Formulas</h1>

    <?php
    $allowedUsers = ['vedant', 'divya', 'venkat']; 

    if (isset($_SESSION['username']) && in_array($_SESSION['username'], $allowedUsers)) {
        echo '
        <div class="add-data">
            <a href="feed_formula_newMaterial.php">Add New Material</a>
        </div>';
    }
	
	 if (isset($_SESSION['username']) && in_array($_SESSION['username'], $allowedUsers)) {
        echo '
        <div class="add-data">
            <a href="feed_formula_edit.php">Edit Formula</a>
        </div>';
    }

    $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $query = "SELECT 
        TYPE,
        feed_rawMaterial_name AS Material,
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
    FROM 
        `feed_formula_detail`
    GROUP BY TYPE, feed_rawMaterial_name
    ORDER BY 
        FIELD(TYPE, 'Feed', 'Water_Medicine', 'Sanitisation'), shead_1 DESC";

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
        while ($row = $result->fetch_assoc()) {
            if ($row['TYPE'] !== $currentType) {
                if ($currentType !== "") {
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
                echo '<table>
                    <tr>
                        <th>Material</th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_1">Shead 1</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_2">Shead 2</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_3">Shead 3</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_4">Shead 4</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_5">Shead 5</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_6">Shead 6</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_7">Shead 7</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=shead_8">Shead 8</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=chick">Chick</a></th>
                        <th><a href="feed_formula_detailsNewEdit.php?type=grower">Grower</a></th>
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
            $shead1Total=$shead1Total+$row['shead_1'];
            $shead2Total=$shead2Total+$row['shead_2'];
            $shead3Total=$shead3Total+$row['shead_3'];
            $shead4Total=$shead4Total+$row['shead_4'];
            $shead5Total=$shead5Total+$row['shead_5'];
            $shead6Total=$shead6Total+$row['shead_6'];
            $shead7Total=$shead7Total+$row['shead_7'];
            $shead8Total=$shead8Total+$row['shead_8'];
            $sheadCTotal=$sheadCTotal+$row['chick'];
            $sheadGTotal=$sheadGTotal+$row['grower'];
        }
        
        echo '</table>';
        
        $result->free();
    } else {
        echo '<p>No data found</p>';
    }

    $mysqli->close();
    ?>
    <div class="remove-data">
    <?php
    $allowedRemoveUsers = ['vedant', 'divya','venkat']; 

    if (isset($_SESSION['username']) && in_array($_SESSION['username'], $allowedRemoveUsers)) {
        echo '<a href="feed_formula_removeMaterial.php">Remove Existing Material</a>';
    }
    ?>
</div>

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
