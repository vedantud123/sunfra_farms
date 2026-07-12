<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Insert Page page</title>
</head>

<body>
	<button onclick="window.location.href='https://sunfra.com/farm/attendance/labour_master.php';">Go Back</button>
    <center>
       <?php
		$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
			
        $id =  $_REQUEST['id'];
        $name = $_REQUEST['name'];
        $dateOfBirth = $_REQUEST['dateOfBirth'];
        $address = $_REQUEST['address'];
		$phoneNumber = $_REQUEST['phoneNumber'];
		$aadhar = $_REQUEST['aadhar'];
		$joiningReference = $_REQUEST['joiningReference'];
		$relatedTo = $_REQUEST['relatedTo'];
		$startDate = $_REQUEST['startDate'];
		$endDate = $_REQUEST['endDate'];
		
        if ($id>1){
            $sql = "UPDATE labour_master SET name='$name' , dateOfBirth='$dateOfBirth' , address='$address', phoneNumber='$phoneNumber' , aadhar='$aadhar' , joiningReference='$joiningReference' , relatedTo='$relatedTo' ,  startDate='$startDate', endDate='$endDate' where id='$id'"; 
        }else{
			$sql = "INSERT INTO labour_master (name, dateOfBirth, address, phoneNumber, aadhar, joiningReference, relatedTo, startDate ) 
        VALUES ('$name', '$dateOfBirth', '$address', '$phoneNumber', '$aadhar', '$joiningReference', '$relatedTo', '$startDate')";

        }
		echo $sql;
        if(mysqli_query($conn, $sql)){
			echo "<h3>data stored successfully</h3>";
        } else{
            echo "ERROR: Hush! Sorry $sql. " 
                . mysqli_error($conn);
        }
        
        mysqli_close($conn);
        ?>
    </center>
		<form action="labour_master.php" method="post">
			<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
			<div style="display: inline-flex; gap: 0px; align-items: center;">
				<input type="submit" value="Done">
				<a href="daylog.php"></a>
			</div>
			</div>
		</form>
</body>

</html>