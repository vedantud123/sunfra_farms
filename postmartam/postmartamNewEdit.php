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
    <title>Postmartam Edit</title>
</head>
<body>
     <button onclick="history.back()">Go Back</button>
	 <?php
        $id=$_REQUEST['id'];
        if ($id >= 1){
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
            $query = "SELECT * FROM postmartam WHERE id=".$id;
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row["id"];
					$sheadNo = $row["sheadNo"];
					$tarkiya = $row["tarkiya"];
					$tarkiya=str_replace("uploads/","",$tarkiya);
					echo $tarkiya;
					$heart = $row["heart"];
					$lever = $row["lever"];
					$gizzard = $row["gizzard"];
					$kidney = $row["kidney"];
					$ovaries = $row["ovaries"];
					$option_1 = $row["option_1"];
					$option_2 = $row["option_2"];
					$option_3 = $row["option_3"];
					$option_4 = $row["option_4"];
					$remarks = $row["remarks"];
                }
            }
        }
		
        echo '<center>
        <h1>Please Enter Your Attendance</h1>
		<form action="postmartamEdit.php" method="POST" enctype="multipart/form-data">
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
				<label for="sheadNo">Shead Number:</label>
				<input type="text" id="sheadNo" name="sheadNo" value="'.$sheadNo.'" required>
			</p>
				
			<p>        
				<label for="photo_tarkiya">Tarkiya Photo:</label>
				<input type="file" id="photo_tarkiya" name="photo_tarkiya" value="'.$tarkiya.'">
			</p>	
				
			<p>
				<label for="photo_heart">Heart Photo:</label>
				<input type="file" id="photo_heart" name="photo_heart" value="'.$heart.'">
			</p>	
				
			<p>	
				<label for="photo_lever">Lever Photo:</label>
				<input type="file" id="photo_lever" name="photo_lever" value="'.$lever.'">
			</p>	
				
			<p>	
				<label for="photo_gizzard">Gizzard Photo:</label>
				<input type="file" id="photo_gizzard" name="photo_gizzard" value="'.$gizzard.'">
			</p>	
				
			<p>	
				<label for="photo_kidney">Kidney Photo:</label>
				<input type="file" id="photo_kidney" name="photo_kidney" value="'.$kidney.'">
			</p>	
				
			<p>	
				<label for="photo_ovaries">Ovaries Photo:</label>
				<input type="file" id="photo_ovaries" name="photo_ovaries" value="'.$ovaries.'">
			</p>	
				
			<p>	
				<label for="photo_option_1">Option 1 Photo:</label>
				<input type="file" id="photo_option_1" name="photo_option_1" value="'.$option_1.'">
			</p>	
				
			<p>	
				<label for="photo_option_2">Option 2 Photo:</label>
				<input type="file" id="photo_option_2" name="photo_option_2" value="'.$option_2.'">
			</p>	
				
			<p>	
				<label for="photo_option_3">Option 3 Photo:</label>
				<input type="file" id="photo_option_3" name="photo_option_3" value="'.$option_3.'">
			</p>	
				
			<p>	
				<label for="photo_option_4">Option 4 Photo:</label>
				<input type="file" id="photo_option_4" name="photo_option_4" value="'.$option_4.'">
			</p>	
				
			<p>	
				<label for="remarks">Remarks:</label>
				<input type="text" id="remarks" name="remarks" value="'.$remarks.'">
			</p>
			
				<input type="submit" value="Submit">
		</form>
		</center>';
	?>
</body>
</html>
