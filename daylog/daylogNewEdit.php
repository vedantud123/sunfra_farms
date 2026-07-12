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
    <title>GFG - Store Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .button-wrapper {
            text-align: center;
            margin-bottom: 20px;
        }

        button {
            padding: 12px 20px;
            font-size: 1rem;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .form-wrapper {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 1rem;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group input[type="date"] {
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group input[type="submit"] {
            padding: 12px 20px;
            font-size: 1.1rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }

        .form-group input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="button-wrapper">
		<button onclick="window.location.href='https://sunfra.com/farm/daylog/daylog.php';">Go Back</button>
    </div>

    <?php
    $id = $_REQUEST['id'];
    if ($id > 1) {
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        $query = "SELECT * FROM dayLog WHERE id=" . $id;
        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $id = $row["id"];
                $sheadNo = $row["sheadNo"];
                $batchId = $row["batchId"];
                $date = $row["date"];
                $feed = $row["feed"];
                $water = $row["water"];
                $mortality = $row["mortality"];
                $liveBirds = $row["liveBirds"];
                $eggsTotal = $row["eggsTotal"];
                $eggsDamaged = $row["eggsDamaged"];
                $productionPercentage = $row["productionPercentage"];
                $eggWeight = $row["eggWeight"];
            }
        }
    }

    echo '<h1>Storing Form Data in Database</h1>';
    echo '<form action="daylogEdit.php" method="post" class="form-wrapper">';

    if ($id > 1) {
        echo '
            <div class="form-group">
                <label for="id">Id:</label>
                <input type="text" name="id" id="id" value="' . $id . '" readonly>
            </div>';
    }

    echo '
        <div class="form-group">
            <label for="sheadNo">Shead No:</label>
            <input type="text" name="sheadNo" id="sheadNo" value="' . $sheadNo . '">
        </div>

        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" name="date" id="date" value="' . $date . '">
        </div>

        <div class="form-group">
            <label for="feed">Feed:</label>
            <input type="text" name="feed" id="feed" value="' . $feed . '">
        </div>

        <div class="form-group">
            <label for="water">Water:</label>
            <input type="text" name="water" id="water" value="' . $water . '">
        </div>

        <div class="form-group">
            <label for="mortality">Mortality:</label>
            <input type="text" name="mortality" id="mortality" value="' . $mortality . '">
        </div>

        <div class="form-group">
            <label for="liveBirds">Live Birds:</label>
            <input type="text" name="liveBirds" id="liveBirds" value="' . $liveBirds . '">
        </div>

        <div class="form-group">
            <label for="eggsTotal">Eggs Total:</label>
            <input type="text" name="eggsTotal" id="eggsTotal" value="' . $eggsTotal . '">
        </div>

        <div class="form-group">
            <label for="eggsDamaged">Eggs Damaged:</label>
            <input type="text" name="eggsDamaged" id="eggsDamaged" value="' . $eggsDamaged . '">
        </div>

        <div class="form-group">
            <label for="eggWeight">Egg Weight:</label>
            <input type="text" name="eggWeight" id="eggWeight" value="' . $eggWeight . '">
        </div>

        <div class="form-group">
            <input type="submit" value="Submit">
        </div>
    </form>';
    ?>
</div>

</body>
</html>
