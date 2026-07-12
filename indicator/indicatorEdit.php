<html>
<body>
    <center>
        <?php
        date_default_timezone_set('Asia/Kolkata');

        $conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

        if ($conn === false) {
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        $mac_address = $_REQUEST['mac_address'];
        $weight = $_REQUEST['weight'];
        $device_id = $_REQUEST['device_id'];
        $timestamp = date('Y-m-d H:i:s'); 

        $sql = "INSERT INTO indicator_values (mac_address, weight, device_id, datetime) 
                VALUES ('$mac_address', '$weight', '$device_id', '$timestamp')";

        if (mysqli_query($conn, $sql)) {
            echo "<h2>Data successfully inserted into the database.</h2>";
        } else {
            echo "<h2>ERROR: Could not insert data. " . mysqli_error($conn) . "</h2>";
        }

        mysqli_close($conn);
        ?>
    </center>
</body>
</html>
