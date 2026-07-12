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
    <title>Mortality Data Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #007bff;
            outline: none;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            border: none;
        }
        .btn:hover {
            background-color: #0056b3;
        }
		
       .back-button {
            display: inline-block;
            margin-bottom: 0px;
            text-decoration: none;
            padding: 5px 5px;
            background-color: #0056b3;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-button">
        <a class="back-button" onclick="history.back()">Go Back</a>
        </div>
        <h1>Mortality Data</h1>
        <?php
        $id = $_REQUEST['id'];
        if ($id >= 1) {
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
            $query = "SELECT * FROM supervisor_shead_mortality WHERE id=" . $id;
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $sheadNo = $row["sheadNo"];
                    $noOfBirds = $row["noOfBirds"];
                }
            }
        }
        echo '<form action="supervisor_shead_mortalityEdit.php" method="post">';
        if ($id >= 1) {
            echo '<div class="form-group">
                    <label for="id">ID:</label>
                    <input type="text" name="id" id="id" value="' . $id . '" readonly>
                </div>';
        }
        echo '<div class="form-group">
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
                    <option value="Chick" ' . ($sheadNo === 'Chick' ? 'selected' : '') . '>Chick</option>
                    <option value="Grower" ' . ($sheadNo === 'Grower' ? 'selected' : '') . '>Grower</option>
                </select>
            </div>
            <div class="form-group">
                <label for="noOfBirds">Number of Birds:</label>
                <input type="text" name="noOfBirds" id="noOfBirds" value="' . $noOfBirds . '" required>
            </div>
            <button type="submit" class="btn">Submit</button>
        </form>';
        ?>
    </div>
</body>
</html>
