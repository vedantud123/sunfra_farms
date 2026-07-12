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
    <title>New labour details</title>
</head>
<body>
	<button onclick="window.location.href='https://sunfra.com/farm/attendance/labour_master.php';">Go Back</button>
	 <?php
        $id=$_REQUEST['id'];
        if ($id >= 1){
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
            $query = "SELECT * FROM labour_master WHERE id=".$id;
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row["id"];
					$name = $row["name"];
					$dateOfBirth = $row["dateOfBirth"];
					$address = $row["address"];
					$phoneNumber = $row["phoneNumber"];
					$aadhar = $row["aadhar"];
					$joiningReference = $row["joiningReference"];
					$relatedTo = $row["relatedTo"];
					$startDate = $row["startDate"];
					$endDate = $row["endDate"];
                }
            }
        }
		
        echo '<center>
        <h1>Please Enter Your Attendance</h1>
		<form action="labour_masterEdit.php" method="POST" enctype="multipart/form-data">
		';
		
		if ($id >= 1){
        echo '
            <p>
               <label for="id">Id:</label>
               <input type="text" name="id" id="id" value="'.$id.'">
            </p>
            ';
        }
		
		echo '
			<p>
				<label for="name">Name:</label>
				<input type="text" id="name" name="name" value="'.$name.'" required>
			</p>
				
			<p>
				<label for="dateOfBirth">Date Of Birth:</label>
				<input type="date" id="dateOfBirth" name="dateOfBirth" value="'.$dateOfBirth.'" required>
			</p>	
				
			<p>
				<label for="address">Address:</label>
				<input type="text" id="address" name="address" value="'.$address.'" required>
			</p>	
				
			<p>
				<label for="phoneNumber">Phone Number:</label>
				<input type="text" id="phoneNumber" name="phoneNumber" value="'.$phoneNumber.'" required>
			</p>	
				
			<p>
				<label for="aadhar">Aadhar Number:</label>
				<input type="text" id="aadhar" name="aadhar" value="'.$aadhar.'" required>
			</p>	
				
			<p>
				<label for="joiningReference">Joining Reference:</label>
				<input type="text" id="joiningReference" name="joiningReference" value="'.$joiningReference.'" required>
			</p>	
				
			<p>
				<label for="relatedTo">Related To:</label>
				<input type="text" id="relatedTo" name="relatedTo" value="'.$relatedTo.'" required>
			</p>	
				
			<p>
				<label for="startDate">Start Date:</label>
				<input type="date" id="startDate" name="startDate" value="'.$startDate.'" required>
			</p>	
				
			<p>
				<label for="endDate">End Date:</label>
				<input type="date" id="endDate" name="endDate" value="'.$endDate.'">
			</p>	
				
				<input type="submit" value="Submit">
		</form>
		</center>';
	?>
</body>
</html>
