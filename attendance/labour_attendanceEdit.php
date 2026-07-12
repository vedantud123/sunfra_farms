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
	<button onclick="window.location.href='https://sunfra.com/farm/attendance/labour_attendance.php';">Go Back</button>
    <center>
        <?php
       $conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        
        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
			
        $id =  $_REQUEST['id'];
        $name = $_REQUEST['name'];
        $status =  $_REQUEST['status'];
		$working_place =  $_REQUEST['working_place'];
        $date = date('Y-m-d');
		$timestamp = date('Y-m-d H:i:s');

        if ($id>=1){
            $sql = "UPDATE attendance SET name='$name' , status='$status' , date='$date' , working_place='$working_place' where id='$id'"; 
        }else
        {
				$sql = "INSERT INTO attendance ( name, status, date, working_place, timestamp) 
			VALUES ( '$name', '$status', '$date', '$working_place' , '$timestamp')";
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
	<form action="labour_attendance.php" method="post">
	<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
    <div style="display: inline-flex; gap: 0px; align-items: center;">
        <input type="submit" value="Done">
        <a href="labour_attendance.php"></a>
    </div>
	</div>
	</form>
</body>

</html>