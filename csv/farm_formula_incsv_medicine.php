<?php
$host = '216.172.184.173';
$user = 'sunfra_farms';
$password = 'sunfra_farms';
$database = 'sunfra_farms';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT 
        feed_rawMaterial_name AS Material,
        SUM(CASE WHEN feed_formulaType = 'shead_1' THEN quantity * 1000 ELSE 0 END) AS shead_1,
        SUM(CASE WHEN feed_formulaType = 'shead_2' THEN quantity * 1000 ELSE 0 END) AS shead_2,
        SUM(CASE WHEN feed_formulaType = 'shead_3' THEN quantity * 1000 ELSE 0 END) AS shead_3,
        SUM(CASE WHEN feed_formulaType = 'shead_4' THEN quantity * 1000 ELSE 0 END) AS shead_4,
        SUM(CASE WHEN feed_formulaType = 'shead_5' THEN quantity * 1000 ELSE 0 END) AS shead_5,
        SUM(CASE WHEN feed_formulaType = 'shead_6' THEN quantity * 1000 ELSE 0 END) AS shead_6,
        SUM(CASE WHEN feed_formulaType = 'shead_7' THEN quantity * 1000 ELSE 0 END) AS shead_7,
        SUM(CASE WHEN feed_formulaType = 'shead_8' THEN quantity * 1000 ELSE 0 END) AS shead_8,
        SUM(CASE WHEN feed_formulaType = 'chick_1' THEN quantity * 1000 ELSE 0 END) AS chick_1,
        SUM(CASE WHEN feed_formulaType = 'grower_1' THEN quantity * 1000 ELSE 0 END) AS grower_1
    FROM 
        `feed_formula_detail` 
    WHERE type = 'Feed_Medicine'
    GROUP BY feed_rawMaterial_name
    ORDER BY FIELD(type, 'Feed', 'Water_Medicine', 'Sanitisation'), shead_1 DESC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "Material,shead_1,shead_2,shead_3,shead_4,shead_5,shead_6,shead_7,shead_8,chick_1,grower_1\n";

    while ($row = $result->fetch_assoc()) {
        echo $row['Material'] . "," . $row['shead_1'] . "," . $row['shead_2'] . "," . $row['shead_3'] . "," . $row['shead_4'] . "," .
             $row['shead_5'] . "," . $row['shead_6'] . "," . $row['shead_7'] . "," . $row['shead_8'] . "," . $row['chick_1'] . "," . $row['grower_1'] . "\n";
    }
} else {
    echo "No data found.";
}

$conn->close();
?>
