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
    <title>Eggs Data Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            margin-top: 15px;
        }

        input[type="text"],
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .button {
            display: inline-block;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #5a6268;
        }

        .back-btn {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="back-btn">
	<button class="button" onclick="window.location.href='https://sunfra.com/farm/supervisorproductionshead/supervisor_production_shead.php'">Go Back</button>
    </div>
    <h1>Enter Eggs Data</h1>
    <?php
    $id = $_REQUEST['id'];
    if ($id >= 1) {
        $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
        $query = "SELECT * FROM supervisor_production_shead WHERE id=" . $id;
        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $id = $row["id"];
                $sheadNo = $row["sheadNo"];
                $no_of_trays = $row["no_of_trays"];
                $no_of_loose_eggs = $row["no_of_loose_eggs"];
                $no_of_damaged_eggs = $row["no_of_damaged_eggs"];
            }
        }
    }

    echo '<form action="supervisor_production_sheadEdit.php" method="post">';

    if ($id >= 1) {
        echo '
            <p>
               <label for="id">ID:</label>
               <input type="text" name="id" id="id" value="' . $id . '" readonly>
            </p>';
    }

    echo '
        <p>
           <label for="sheadNo">Shead No:</label>
           <select id="sheadNo" name="sheadNo" required>
               <option value="">Select option</option>
               <option value="Shead_1" ' . ($sheadNo === 'Shead_1' ? 'selected' : '') . '>Shead_1</option>
               <option value="Shead_2" ' . ($sheadNo === 'Shead_2' ? 'selected' : '') . '>Shead_2</option>
               <option value="Shead_3" ' . ($sheadNo === 'Shead_3' ? 'selected' : '') . '>Shead_3</option>
               <option value="Shead_4" ' . ($sheadNo === 'Shead_4' ? 'selected' : '') . '>Shead_4</option>
               <option value="Shead_5" ' . ($sheadNo === 'Shead_5' ? 'selected' : '') . '>Shead_5</option>
               <option value="Shead_6" ' . ($sheadNo === 'Shead_6' ? 'selected' : '') . '>Shead_6</option>
               <option value="Shead_7" ' . ($sheadNo === 'Shead_7' ? 'selected' : '') . '>Shead_7</option>
               <option value="Shead_8" ' . ($sheadNo === 'Shead_8' ? 'selected' : '') . '>Shead_8</option>
           </select>
        </p>

        <p>
           <label for="no_of_trays">No of Trays:</label>
           <input type="text" name="no_of_trays" id="no_of_trays" value="' . $no_of_trays . '">
        </p>

        <p>
           <label for="no_of_loose_eggs">No of Loose Eggs in Tray:</label>
           <input type="text" name="no_of_loose_eggs" id="no_of_loose_eggs" value="' . $no_of_loose_eggs . '">
        </p>

        <p>
           <label for="no_of_damaged_eggs">No of Damaged Eggs:</label>
           <input type="text" name="no_of_damaged_eggs" id="no_of_damaged_eggs" value="' . $no_of_damaged_eggs . '">
        </p>

        <input type="submit" value="Submit">
    </form>';
    ?>
</div>
</body>
</html>
