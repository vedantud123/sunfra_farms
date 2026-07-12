<!DOCTYPE html>
<html>

<head>
    <title>Insert Page page</title>
</head>

<body>
    <center>
        <?php
		date_default_timezone_set('Asia/Kolkata');

        $conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        
        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
       
        $key =  $_REQUEST['key'];
        $time = $_REQUEST['time'];
	if (strpos($key, 'G:') !== false && strpos($key, 'N:') !== false) {
		
        $key=substr($key,strpos($key,"RST")+4);
        $rst=substr($key,0,4);
        $key=substr($key,5);
        $party=substr($key,0,strpos($key,"G:")-1);
        $party=trim($party);
        $key=substr($key,strpos($key,"G:")+2);
        $gross=substr($key,1,strpos($key,"Kg")-1);
        $gross=trim($gross);
        $key=substr($key,strpos($key,"T:")+2);
        $tare=substr($key,1,strpos($key,"Kg")-1);
        $tare=trim($tare);
        $key=substr($key,strpos($key,"N:")+2);
        $net=substr($key,1,strpos($key,"Kg")-1);
        $net=trim($net);
        $date = date('Y-m-d');
		$timestamp = date('Y-m-d H:i:s');

		$spaceCount = substr_count($party, "\n");

		if($spaceCount==3){
			$parts = explode(" ", $party, 3);
			$vehicleNumber = $parts[0]; 
			$ownerName = $parts[1];          
			$material = $parts[2];
			$sql = "INSERT INTO weighBridge (id,vehicleNumber,ownerName,gross,empty,net,date,material,timestamp) VALUES ('$rst','$vehicleNumber','$ownerName','$gross','$tare','$net','$date','$material','$timestamp')";
        
			if(mysqli_query($conn, $sql)){
				echo "<h3>data stored in a database successfully." 
					. " Please browse your localhost php my admin" 
					. " to view the updated data</h3>"; 

				echo nl2br("\n$key\n $time\n");
			} else{
				echo "ERROR: Hush! Sorry $sql. " 
					. mysqli_error($conn);
			}
		}else{
			#$sql = "INSERT INTO weighBridge (id,ownerName,gross,empty,net,date,timestamp) VALUES ('$rst','$party','$gross','$tare','$net','$date','$timestamp')";
			$sql = "INSERT INTO weighBridge (id,ownerName,gross,empty,net,date,timestamp) VALUES ('$rst','$party','$gross','$tare','$net','$date','$timestamp')";
			if(mysqli_query($conn, $sql)){
				echo "<h3>data stored in a database successfully." 
					. " Please browse your localhost php my admin" 
					. " to view the updated data</h3>"; 

				echo nl2br("\n$key\n $time\n");
			} else{
				echo "ERROR: Hush! Sorry $sql. " 
					. mysqli_error($conn);
			}
        }
		
        // Close connection
        mysqli_close($conn);
		}
        ?>
    </center>
</body>

</html>