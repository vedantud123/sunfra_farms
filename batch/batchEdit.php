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
	<button onclick="window.location.href='https://sunfra.com/farm/batch/batch.php';">Go Back</button>
    <center>
        <?php
		$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
        
        $batch_id =  $_REQUEST['batch_id'];
        $breed = $_REQUEST['breed'];
        $hatchDate =  $_REQUEST['hatchDate'];
        $noOfChicks = $_REQUEST['noOfChicks'];
        $sheadNo = $_REQUEST['sheadNo'];
		$cullDate = $_REQUEST['cullDate'];
		$live_birds = $_REQUEST['live_birds'];
		$timestamp = date('Y-m-d H:i:s');
    
        if ($batch_id>1){
            $sql = "UPDATE  batch SET breed='$breed', hatchDate='$hatchDate' , noOfChicks='.$noOfChicks.' , sheadNo='$sheadNo' , cullDate='$cullDate', live_birds='.$live_birds.'  where batch_id='$batch_id'"; 
        }else
        {
             $sql = "INSERT INTO batch (batch_id,breed,hatchDate,noOfChicks,sheadNo,cullDate,live_birds, timestamp)  VALUES ('$batch_id', 
            '$breed','$hatchDate','$noOfChicks','$sheadNo','$cullDate','$live_birds','$timestamp')";
        }
        if(mysqli_query($conn, $sql)){
             echo "Thank You!";
        } else{
            echo "ERROR: Hush! Sorry $sql. " 
                . mysqli_error($conn);
        }
        
        mysqli_close($conn);
		
        ?>
    </center>
	<form action="batch.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="batch.php"></a>
    </div>
</div>
	</form>
</body>

</html>