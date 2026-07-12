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
      <title>GFG- Store Data</title>
      <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        input[type="text"],
        input[type="date"],
        input[type="submit"] {
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-top: 5px;
        }

        input[type="text"],
        input[type="date"] {
            background-color: #f9f9f9;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .back-button {
            background-color: #f1f1f1;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: #333;
            cursor: pointer;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
        }

        .back-button:hover {
            background-color: #ddd;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            input[type="text"],
            input[type="date"],
            input[type="submit"] {
                font-size: 14px;
            }
        }
      </style>
   </head>
   <body>
      <div class="container">
         <button class="back-button" onclick="history.back()">Go Back</button>
         <?php
            $id = $_REQUEST['id'];
            if ($id >= 1) {
                $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms"); 
                $query = "SELECT * FROM tractor_production_mortality WHERE id=" . $id;
                if ($result = $mysqli->query($query)) {
                    while ($row = $result->fetch_assoc()) {
                        $id = $row["id"];
                        $sheadNo = $row["sheadNo"];
                        $batch_id = $row["batch_id"];
                        $date = $row["date"];
                        $production = $row["production"];
                        $eggTrays = $row["eggTrays"];
                        $looseEggs = $row["looseEggs"];
                        $mortality = $row["mortality"];
                    }
                }
            }
         ?>
         <h1>Storing Form Data in Database</h1>
         <form action="Tractor_production_mortalityEdit.php" method="post">
            <?php if ($id >= 1): ?>
                <p>
                    <label for="id">Id:</label>
                    <input type="text" name="id" id="id" value="<?php echo $id; ?>" readonly>
                </p>
            <?php endif; ?>
            
            <p>
                <label for="date">Date:</label>
                <input type="date" name="date" id="date" value="<?php echo $date; ?>">
            </p>
            
            <p>
                <label for="sheadNo">Shead No:</label>
                <input type="text" name="sheadNo" id="sheadNo" value="<?php echo $sheadNo; ?>">
            </p>
            
            <p>
                <label for="eggTrays">Egg Trays:</label>
                <input type="text" name="eggTrays" id="eggTrays" value="<?php echo $eggTrays; ?>">
            </p>
            
            <p>
                <label for="looseEggs">Loose Eggs:</label>
                <input type="text" name="looseEggs" id="looseEggs" value="<?php echo $looseEggs; ?>">
            </p>
            
            <p>
                <label for="mortality">Mortality:</label>
                <input type="text" name="mortality" id="mortality" value="<?php echo $mortality; ?>">
            </p>
            
            <input type="submit" value="Submit">
         </form>
      </div>
   </body>
</html>
