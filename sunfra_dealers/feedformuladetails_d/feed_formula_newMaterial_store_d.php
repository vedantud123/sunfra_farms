<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login_d/login_d.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

?>

<!DOCTYPE html>
<html>

<head>
    <title>Insert Page page</title>
</head>

<body>
    <button onclick="window.location.href='https://sunfra.com/farm/sunfra_dealers/feedformuladetails_d/feed_formula_details_d.php'">Go Back</button>
    <center>
        <?php
       $conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_yugandhar_pf");
        
        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
		$type =	$_REQUEST['type'];
        $name =  $_REQUEST['name'];
  		$formulaTypes = ['shead_1', 'shead_2', 'shead_3', 'shead_4', 'shead_5', 'shead_6','shead_7','shead_8','chick','grower'];
		mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

        try {
			foreach ($formulaTypes as $formulaType) {
			$sql = "INSERT INTO feed_formula_detail (feed_rawMaterial_name, feed_formulaType, quantity, type) 
                VALUES ('$name', '$formulaType', '0','$type')";
        
			if (!mysqli_query($conn, $sql)) {
				throw new Exception("Error: " . mysqli_error($conn));
			}
		}

		mysqli_commit($conn);
		echo "<h3>All rows inserted successfully</h3>";
		} catch (Exception $e) {
			mysqli_rollback($conn);
			echo "Transaction failed: " . $e->getMessage();
		}

		mysqli_close($conn);
        ?>
    </center>
	<form action="feed_formula_details_d.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="feed_formula_details_d.php"></a>
    </div>
	</div>
	</form>
</body>

</html>