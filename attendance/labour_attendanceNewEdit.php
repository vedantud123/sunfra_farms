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
    <title>New Labour Details</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #4CAF50;
        }

        form p {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], 
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            color: #4CAF50;
            background-color: #fff;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background-color: #4CAF50;
            color: #fff;
        }

        .form-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
	<button onclick="window.location.href='https://sunfra.com/farm/attendance/labour_attendance.php';">Go Back</button>
        <?php
            $mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
            $name = '';
            $status = '';
            $working_place = '';

            if ($id >= 1) {
                $query = "SELECT * FROM attendance WHERE id = $id";
                $result = $mysqli->query($query);

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $id = $row["id"];
                    $name = $row["name"];
                    $status = $row["status"];
                    $working_place = $row["working_place"];
                } else {
                    echo "<p>No attendance record found for ID: $id</p>";
                }
            }

            $namesQuery = "SELECT name FROM labour_master order by name";
            $namesResult = $mysqli->query($namesQuery);

            echo '<h1>Enter Your Attendance</h1>';
            echo '<form action="labour_attendanceEdit.php" method="POST">';

            if ($id >= 1) {
                echo '<p><label for="id">ID:</label><input type="text" name="id" id="id" value="' . htmlspecialchars($id) . '" readonly></p>';
            }

            echo '<p><label for="name">Name:</label><select id="name" name="name" required><option value="">Select a Name</option>';
            if ($namesResult && $namesResult->num_rows > 0) {
                while ($row = $namesResult->fetch_assoc()) {
                    $selected = ($row['name'] === $name) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($row['name']) . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                }
            } else {
                echo '<option value="">No names found</option>';
            }
            echo '</select></p>';

            echo '<p><label for="status">Status:</label><select id="status" name="status" required>
                <option value="">Select Status</option>
                <option value="Present" ' . ($status === 'Present' ? 'selected' : '') . '>Present</option>
                <option value="Absent" ' . ($status === 'Absent' ? 'selected' : '') . '>Absent</option>
                <option value="Present/2" ' . ($status === 'Present/2' ? 'selected' : '') . '>Present/2</option>
            </select></p>';

            echo '<p><label for="working_place">Working Place:</label><select id="working_place" name="working_place" required>
					<option value="">Select Place</option>
					<option value="Shead_1" ' . ($working_place === 'Shead_1' ? 'selected' : '') . '>Shead_1</option>
					<option value="Shead_2" ' . ($working_place === 'Shead_2' ? 'selected' : '') . '>Shead_2</option>
					<option value="Shead_3" ' . ($working_place === 'Shead_3' ? 'selected' : '') . '>Shead_3</option>
					<option value="Shead_4" ' . ($working_place === 'Shead_4' ? 'selected' : '') . '>Shead_4</option>
					<option value="Shead_5" ' . ($working_place === 'Shead_5' ? 'selected' : '') . '>Shead_5</option>
					<option value="Shead_6" ' . ($working_place === 'Shead_6' ? 'selected' : '') . '>Shead_6</option>
					<option value="Shead_7" ' . ($working_place === 'Shead_7' ? 'selected' : '') . '>Shead_7</option>
					<option value="Shead_8" ' . ($working_place === 'Shead_8' ? 'selected' : '') . '>Shead_8</option>
					<option value="Chick" ' . ($working_place === 'Chick' ? 'selected' : '') . '>Chick</option>
					<option value="Grower" ' . ($working_place === 'Grower' ? 'selected' : '') . '>Grower</option>
					<option value="Feed_Godown" ' . ($working_place === 'Feed_Godown' ? 'selected' : '') . '>Feed_Godown</option>
					<option value="Egg_godown" ' . ($working_place === 'Egg_godown' ? 'selected' : '') . '>Egg_godown</option>
					<option value="Gate_Manager" ' . ($working_place === 'Gate_Manager' ? 'selected' : '') . '>Gate_Manager</option>
					<option value="Others" ' . ($working_place === 'Others' ? 'selected' : '') . '>Others</option>
            </select></p>';

            echo '<input type="submit" value="Submit">';
            echo '</form>';

            $mysqli->close();
        ?>
        <div class="form-footer">
            <p>&copy; <?php echo date('Y'); ?> Attendance System</p>
        </div>
    </div>
</body>
</html>
