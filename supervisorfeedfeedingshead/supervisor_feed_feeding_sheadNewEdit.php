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
    <title>Feeding Data Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"], select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            padding: 10px 15px;
            background-color: #0056b3;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .back-button:hover {
            background-color: #565e64;
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-button" onclick="history.back()">Go Back</a>
        <h1>Please Enter Feeding Data</h1>
        <?php
        $id = $_REQUEST['id'];
        if ($id >= 1) {
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
            $query = "SELECT * FROM supervisor_feed_feeding_shead WHERE id=" . $id;
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $sheadNo = $row["sheadNo"];
                    $Box_1 = $row["Box_1"];
                    $Box_2 = $row["Box_2"];
                    $Box_3 = $row["Box_3"];
                    $Box_4 = $row["Box_4"];
                    $Box_5 = $row["Box_5"];
                    $Box_6 = $row["Box_6"];
                    $Box_7 = $row["Box_7"];
                    $Box_8 = $row["Box_8"];
                    $Box_9 = $row["Box_9"];
                    $Box_10 = $row["Box_10"];
                }
            }
        }
        echo '<form action="supervisor_feed_feeding_sheadEdit.php" method="post">';
        if ($id >= 1) {
            echo '
            <label for="id">ID:</label>
            <input type="text" name="id" id="id" value="' . $id . '" readonly>';
        }
        echo '
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
            </select>';
        for ($i = 1; $i <= 10; $i++) {
            $boxVar = "Box_$i";
            echo '
            <label for="Box_' . $i . '">Box ' . $i . ':</label>
            <input type="text" name="Box_' . $i . '" id="Box_' . $i . '" value="' . $$boxVar . '">';
        }
        echo '<input type="submit" value="Submit"></form>';
        ?>
    </div>
</body>
</html>
