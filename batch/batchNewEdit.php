<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$batch_id = $breed = $hatchDate = $noOfChicks = $sheadNo = $cullDate = $live_birds = "";

if (isset($_REQUEST['id']) && $_REQUEST['id'] > 1) {
    $bId = intval($_REQUEST['id']);
    $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $query = "SELECT * FROM batch WHERE batch_id = $bId";
    $result = $mysqli->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $batch_id = htmlspecialchars($row["batch_id"]);
        $breed = htmlspecialchars($row["breed"]);
        $hatchDate = htmlspecialchars($row["hatchDate"]);
        $noOfChicks = htmlspecialchars($row["noOfChicks"]);
        $sheadNo = htmlspecialchars($row["sheadNo"]);
        $cullDate = htmlspecialchars($row["cullDate"]);
		$live_birds = htmlspecialchars($row["live_birds"]);
    } else {
        echo "<p class='error'>Batch data not found.</p>";
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Edit Batch</title>
      <style>
         /* Add your styles here */
         body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
         }
         .form-container {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
         }
         h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
         }
         button, input[type="submit"] {
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
         }
         button {
            background-color: #007bff;
            color: white;
         }
         button:hover {
            background-color: #0056b3;
         }
         input[type="text"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
         }
         input[type="submit"] {
            background-color: #28a745;
            color: white;
            margin-top: 20px;
         }
         input[type="submit"]:hover {
            background-color: #218838;
         }
         .error {
            color: red;
            text-align: center;
         }
      </style>
   </head>
   <body>
      <div class="form-container">
         <button onclick="window.location.href='https://sunfra.com/farm/batch/batch.php';">Go Back</button>
         <h1>Edit Batch Details</h1>
         <form action="batchEdit.php" method="post">
            <p>
               <label for="batch_id">Batch Id:</label>
               <input type="text" name="batch_id" id="batch_id" value="<?= $batch_id ?>" readonly>
            </p>
            <p>
               <label for="breed">Breed:</label>
               <input type="text" name="breed" id="breed" value="<?= $breed ?>">
            </p>
            <p>
               <label for="hatchDate">Hatch Date:</label>
               <input type="date" name="hatchDate" id="hatchDate" value="<?= $hatchDate ?>">
            </p>
            <p>
               <label for="noOfChicks">Number of Chicks:</label>
               <input type="text" name="noOfChicks" id="noOfChicks" value="<?= $noOfChicks ?>">
            </p>
            <p>
               <label for="sheadNo">Shead Number:</label>
               <select id="sheadNo" name="sheadNo" required>
					<option value="">Select option</option>
					<option value="1" <?= $sheadNo === '1' ? 'selected' : '' ?>>1</option>
					<option value="2" <?= $sheadNo === '2' ? 'selected' : '' ?>>2</option>
					<option value="3" <?= $sheadNo === '3' ? 'selected' : '' ?>>3</option>
					<option value="4" <?= $sheadNo === '4' ? 'selected' : '' ?>>4</option>
					<option value="5" <?= $sheadNo === '5' ? 'selected' : '' ?>>5</option>
					<option value="6" <?= $sheadNo === '6' ? 'selected' : '' ?>>6</option>
					<option value="7" <?= $sheadNo === '7' ? 'selected' : '' ?>>7</option>
					<option value="8" <?= $sheadNo === '8' ? 'selected' : '' ?>>8</option>
					<option value="Chick" <?= $sheadNo === 'Chick' ? 'selected' : '' ?>>Chick</option>
					<option value="Grower" <?= $sheadNo === 'Grower' ? 'selected' : '' ?>>Grower</option>
               </select>
            </p>
            <p>
               <label for="cullDate">Cull Date:</label>
               <input type="date" name="cullDate" id="cullDate" value="<?= $cullDate ?>">
            </p>
			
			 <p>
               <label for="live_birds">Live Birds:</label>
               <input type="text" name="live_birds" id="live_birds" value="<?= $live_birds ?>">
            </p>
			
            <input type="submit" value="Submit">
         </form>
      </div>
   </body>
</html>
