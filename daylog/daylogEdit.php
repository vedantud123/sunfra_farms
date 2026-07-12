<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
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
	<button onclick="window.location.href='https://sunfra.com/farm/daylog/daylog.php';">Go Back</button>
    <center>
        <?php
       $conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
			
        $id =  $_REQUEST['id'];
        $sheadNo = $_REQUEST['sheadNo'];
        $date = $_REQUEST['date'];
        $feed = $_REQUEST['feed'];
		$water = $_REQUEST['water'];
		$mortality = $_REQUEST['mortality'];
		$liveBirds = $_REQUEST['liveBirds'];
		$eggsTotal = $_REQUEST['eggsTotal'];
		$eggsDamaged = $_REQUEST['eggsDamaged'];
		$eggWeight = $_REQUEST['eggWeight'];
        $productionPercentage=($eggsTotal / $liveBirds) * 100;
		$timestamp = date('Y-m-d H:i:s');
		$TempQuery="SELECT batch_id FROM batch WHERE sheadNo='$sheadNo' AND (cullDate > hatchDate OR cullDate = '0000-00-00')";
		$result = $conn->query($TempQuery);
		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$batch_Id = $row['batch_id']; 
		} else {
			echo "No records found.";
		}
		
        if ($id>1){
            $sql = "UPDATE dayLog SET sheadNo='.$sheadNo.' , batchId='.$batch_Id.' , date='$date', feed='.$feed.' , water='.$water.' , mortality='.$mortality.' , liveBirds='.$liveBirds.' ,  eggsTotal='.$eggsTotal.', eggsDamaged='.$eggsDamaged.' , productionPercentage='$productionPercentage' , eggWeight='$eggWeight'  where id='$id'"; 
        }else{
			$sql = "INSERT INTO dayLog (timestamp,sheadNo, batchId, date, feed, water, mortality, liveBirds, eggsTotal, eggsDamaged, productionPercentage, eggWeight) 
        VALUES ('$timestamp', '$sheadNo', '$batch_Id', '$date', '$feed', '$water', '$mortality', '$liveBirds', '$eggsTotal', '$eggsDamaged', '$productionPercentage', '$eggWeight')";
        }
        if(mysqli_query($conn, $sql)){
			echo "<h3>data stored successfully</h3>";
        } else{
            echo "ERROR: Hush! Sorry $sql. " 
                . mysqli_error($conn);
        }
        
        mysqli_close($conn);
        ?>
    </center>
	<form action="daylog.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="daylog.php"></a>
    </div>
	</div>
	</form>
</body>

</html>