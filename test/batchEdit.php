<!DOCTYPE html>
<html>

<head>
    <title>Insert Page page</title>
</head>

<body>
    <button onclick="history.back()">Go Back</button>
    <center>
        <?php

        // servername => localhost
        // username => root
        // password => empty
        // database name => staff
       $conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        
        
        // Check connection
        if($conn === false){
            die("ERROR: Could not connect. " 
                . mysqli_connect_error());
        }
        
        // Taking all 5 values from the form data(input)
        $batch_id =  $_REQUEST['batch_id'];
        $breed = $_REQUEST['breed'];
        $hatchDate =  $_REQUEST['hatchDate'];
        $noOfChicks = $_REQUEST['noOfChicks'];
        $sheadNo = $_REQUEST['sheadNo'];
        
        // Performing insert query execution
        // here our table name is college
        if ($batch_id>1){
            $sql = "update batch set breed='$breed' where batch_id='$batch_id'"; 
        }else
        {
             $sql = "INSERT INTO batch (batch_id,breed,hatchDate,noOfChicks,sheadNo)  VALUES ('$batch_id', 
            '$breed','$hatchDate','$noOfChicks','$sheadNo')";
        }
        if(mysqli_query($conn, $sql)){
            echo "<h3>data stored in a database successfully." 
                . " Please browse your localhost php my admin" 
                . " to view the updated data</h3>"; 

            echo nl2br("\n$batch_id\n $breed\n "
                . "$hatchDate\n $noOfChicks\n $sheadNo");
        } else{
            echo "ERROR: Hush! Sorry $sql. " 
                . mysqli_error($conn);
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </center>
</body>

</html>