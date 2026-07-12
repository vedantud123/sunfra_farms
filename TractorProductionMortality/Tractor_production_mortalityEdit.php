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
<button onclick="history.back()">Go Back</button>
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
		$eggTrays = $_REQUEST['eggTrays'];
		$looseEggs = $_REQUEST['looseEggs'];
		$mortality = $_REQUEST['mortality'];
        $production=($eggTrays * 30) + $looseEggs ;
		$timestamp = date('Y-m-d H:i:s');
		$TempQuery="SELECT batch_id FROM batch WHERE sheadNo='$sheadNo' AND (cullDate > hatchDate OR cullDate = '0000-00-00')";
		$result = $conn->query($TempQuery);
		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$batch_id = $row['batch_id']; 
		} else {
			echo "No records found.";
		}
		
        if ($id > 1){
            $sql = "UPDATE tractor_production_mortality SET sheadNo='.$sheadNo.' , batch_id='.$batch_id.' , date='$date', eggTrays='.$eggTrays.' , looseEggs='.$looseEggs.' , mortality='.$mortality.' , production='.$production.' where id='$id'"; 
        }else{
			$sql = "INSERT INTO tractor_production_mortality (sheadNo, batch_id, date, eggTrays, looseEggs, mortality, production, timestamp) 
        VALUES ('$sheadNo', '$batch_id', '$date', '$eggTrays', '$looseEggs', '$mortality', '$production', '$timestamp')";

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
	<form action="Tractor_production_mortality.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="Tractor_production_mortality.php"></a>
    </div>
	</div>
	</form>
</body>

</html>