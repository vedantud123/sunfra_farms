<!DOCTYPE html>
<html>

<head>
    <title>Insert Page page</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar { transition: all 0.3s ease; }
    .sidebar a:hover { background-color: #2b6cb0; color: white; }
    .collapsed { width: 64px !important; }
    .collapsed .sidebar-text { display: none; }
    .collapsed nav a { justify-content: center; }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .sidebar.show { display: block; }
    }
  </style>
</head>

<?php session_start(); ?>
<?php $clientName = $_SESSION['client_name'] ?? 'Yours'; ?>
<body class="bg-gray-100 text-gray-800">
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

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>

</html>