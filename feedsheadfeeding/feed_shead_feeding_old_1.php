<?php
session_start();

$servername = "216.172.184.173";
$username = "sunfra_farms";
$password = "sunfra_farms";
$dbname = "sunfra_farms";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');

$sql_tons = "SELECT shead_name, COUNT(*) AS total_tons 
             FROM feed_indicator_logs 
             WHERE DATE(timestamp) = ? 
             GROUP BY shead_name";

$stmt_tons = $conn->prepare($sql_tons);
$stmt_tons->bind_param("s", $selected_date);
$stmt_tons->execute();
$result_tons = $stmt_tons->get_result();

$tons_data = [];
while ($row = $result_tons->fetch_assoc()) {
    $tons_data[$row['shead_name']] = $row['total_tons'];
}
$stmt_tons->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shead Data Report</title>
    <style>
        body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        margin: 0;
        padding: 0;
        text-align: center;
    }

    /* Container for Centered Content */
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 10px 15px;
        text-align: center;
    }

    /* Form Styles */
    form {
        display: inline-block;
        text-align: left;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    form p {
        margin-bottom: 15px;
    }

    form label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }

    form input, form select, form button {
        width: 100%;
        padding: 10px;
        font-size: 14px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }

    form button {
        background-color: #007bff;
        color: white;
        font-weight: bold;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    form button:hover {
        background-color: #0056b3;
    }

    /* Back Button */
    .button-container {
        margin-bottom: 20px;
    }

    .button-container button {
        background-color: #6c757d;
        color: white;
        font-size: 14px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .button-container button:hover {
        background-color: #5a6268;
    }

    /* Title Styles */
    h1 {
        color: #007bff;
        margin: 20px 0;
        font-size: 24px;
    }

    /* Table Styles */
    .table-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        overflow-x: auto; /* Adds horizontal scroll for small screens */
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        background: white;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    table th, table td {
        border: 1px solid #ddd;
        padding: 10px;
        font-size: 14px;
        text-align: center;
    }

    table th {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }

    table tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    /* Responsive Design for Smaller Screens */
    @media (max-width: 600px) {
        h1 {
            font-size: 20px;
        }

        form {
            width: 90%;
            padding: 15px;
        }

        table th, table td {
            font-size: 12px;
            padding: 8px;
        }
    }

    </style>
</head>
<body>

<div class="button-container">
        <button onclick="window.location.href='https://sunfra.com/farm/feed_plant_supervisor.php';">Go Back</button>
    </div>	
<div class="container">
    <h2>Feed Shesd Data</h2>
    <form method="POST">
        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date" value="<?php echo $selected_date; ?>">
        <button type="submit">Submit</button>
    </form>

    <table>
        <tr>
            <th>Shead No</th>
            <th>Total Tons</th>
        </tr>
        <?php
        if (!empty($tons_data)) {
            foreach ($tons_data as $shead_no => $total_tons) {
                echo "<tr>
                        <td>$shead_no</td>
                        <td>$total_tons</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>No data available</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>

