<!DOCTYPE html>
<html lang="en">
   <head>
      <title>GFG- Store Data</title>
   
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
      <?php
        $bId=$_REQUEST['id'];
        if ($bId > 1){
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
            $query = "SELECT * FROM batch where batch_id=".$bId;
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    $batch_id = $row["batch_id"];
                    $breed = $row["breed"];
                    $hatchDate = $row["hatchDate"];
                    $noOfChicks = $row["noOfChicks"];
                    $sheadNo = $row["sheadNo"]; 
                }
            }
            
        }
         echo '<center>
         <h1>Storing Form data in Database</h1>
         <form action="batchEdit.php" method="post">
        ';
        
        if ($bId>1){
        echo '
            <p>
               <label for="batch_id">Batch Id:</label>
               <input type="text" name="batch_id" id="batch_id" value="'.$batch_id.'">
            </p>
            ';
        }
        
        echo '
            <p>
               <label for="breed">breed:</label>
               <input type="text" name="breed" id="breed" value="'.$breed.'">
            </p>

            
            <p>
               <label for="hatchDate">hatchDate:</label>
               <input type="date" name="hatchDate" id="hatchDate" value="'.$hatchDate.'">
            </p>

            
            <p>
               <label for="noOfChicks">noOfChicks:</label>
               <input type="text" name="noOfChicks" id="noOfChicks" value="'.$noOfChicks.'">
            </p>

            
            <p>
               <label for="sheadNo">sheadNo:</label>
               <input type="text" name="sheadNo" id="sheadNo" value="'.$sheadNo.'">
            </p>

            <input type="submit" value="Submit">
         </form>
         </center>';
        ?>
     
   
<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>