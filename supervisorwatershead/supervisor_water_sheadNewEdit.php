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
    <title>Supervisor Water Shead - Edit</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #007BFF;
        }
        form p {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .back-button {
            margin: 10px 0;
            text-align: center;
        }
        .back-button button {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-button button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="back-button">
		<button class="button" onclick="window.location.href='https://sunfra.com/farm/supervisorwatershead/supervisor_water_shead.php'">Go Back</button>
    </div>

    <div class="container">
        <?php
        $id = $_REQUEST['id'];
        if ($id >= 1) {
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
            $query = "SELECT * FROM supervisor_water_shead WHERE id=" . $id;
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $flushed_out_open_reading = $row["flushed_out_open_reading"];
                    $flushed_out_closing_reading = $row["flushed_out_closing_reading"];
                    $day_end_closing_reading = $row["day_end_closing_reading"];
                }
            }
        }
        ?>
        <h1>Please Enter Data</h1>
        <form action="supervisor_water_sheadEdit.php" method="post">
            <?php if ($id >= 1): ?>
                <p>
                    <label for="id">ID:</label>
                    <input type="text" name="id" id="id" value="<?php echo $id; ?>" readonly>
                </p>
            <?php endif; ?>
            <p>
                <label for="flushed_out_open_reading">Flushed Out Open Reading:</label>
                <input type="text" name="flushed_out_open_reading" id="flushed_out_open_reading" value="<?php echo $flushed_out_open_reading ?? ''; ?>" required>
            </p>
            <p>
                <label for="flushed_out_closing_reading">Flushed Out Closing Reading:</label>
                <input type="text" name="flushed_out_closing_reading" id="flushed_out_closing_reading" value="<?php echo $flushed_out_closing_reading ?? ''; ?>" required>
            </p>
            <p>
                <label for="day_end_closing_reading">Day End Closing Reading:</label>
                <input type="text" name="day_end_closing_reading" id="day_end_closing_reading" value="<?php echo $day_end_closing_reading ?? ''; ?>" required>
            </p>
            <p style="text-align: center;">
                <input type="submit" value="Submit">
            </p>
        </form>
    </div>
</body>
</html>
