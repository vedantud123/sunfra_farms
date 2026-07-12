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
        
        // Check connection
        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
			
        $id =  $_REQUEST['id'];
        $flushed_out_open_reading = $_REQUEST['flushed_out_open_reading'];
        $flushed_out_closing_reading =  $_REQUEST['flushed_out_closing_reading'];
		$day_end_closing_reading =  $_REQUEST['day_end_closing_reading'];
        $date = date('Y-m-d');
		$timestamp = date('Y-m-d H:i:s');

		
        if ($id>=1){
            $sql = "UPDATE supervisor_water_shead SET flushed_out_open_reading='$flushed_out_open_reading' , flushed_out_closing_reading='$flushed_out_closing_reading' , day_end_closing_reading='$day_end_closing_reading' where id='$id'"; 
        }else
        {
				$sql = "INSERT INTO supervisor_water_shead ( flushed_out_open_reading, flushed_out_closing_reading, day_end_closing_reading, date, timestamp) 
			VALUES ( '$flushed_out_open_reading', '$flushed_out_closing_reading', '$day_end_closing_reading', '$date', '$timestamp')";
        }
        if(mysqli_query($conn, $sql)){
			echo "<h3>data stored successfully</h3>";
        } else{
            echo "ERROR: Hush! Sorry $sql. " 
                . mysqli_error($conn);
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </center>
	<form action="supervisor_water_shead.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="supervisor_water_shead.php"></a>
    </div>
	</div>
	</form>
</body>

</html>