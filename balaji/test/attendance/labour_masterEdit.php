<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0; // <-- Multi-tenant key
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Page page</title>

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
    <button onclick="window.location.href='https://sunfra.com/farm/test/attendance/labour_master.php';">Go Back</button>
    <center>
        <?php
        $conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

        if ($conn === false) {
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        // Get all form fields
        $id = $_REQUEST['id'] ?? 0;
        $name = $_REQUEST['name'] ?? '';
        $dateOfBirth = $_REQUEST['dateOfBirth'] ?? '';
        $address = $_REQUEST['address'] ?? '';
        $phoneNumber = $_REQUEST['phoneNumber'] ?? '';
        $aadhar = $_REQUEST['aadhar'] ?? '';
        $joiningReference = $_REQUEST['joiningReference'] ?? '';
        $relatedTo = $_REQUEST['relatedTo'] ?? '';
        $startDate = $_REQUEST['startDate'] ?? '';
        $endDate = $_REQUEST['endDate'] ?? '';

        if ($id > 1) {
            // Only update records that belong to this tenant
            $sql = "UPDATE labour_master 
                    SET name='$name', dateOfBirth='$dateOfBirth', address='$address', 
                        phoneNumber='$phoneNumber', aadhar='$aadhar', joiningReference='$joiningReference', 
                        relatedTo='$relatedTo', startDate='$startDate', endDate='$endDate' 
                    WHERE id='$id' AND client_id='$client_id'";
        } else {
            // Insert new record and attach client_id
            $sql = "INSERT INTO labour_master 
                        (name, dateOfBirth, address, phoneNumber, aadhar, joiningReference, relatedTo, startDate, endDate, client_id) 
                    VALUES 
                        ('$name', '$dateOfBirth', '$address', '$phoneNumber', '$aadhar', '$joiningReference', '$relatedTo', '$startDate', '$endDate', '$client_id')";
        }

        echo $sql; // For debugging — you can remove this later

        if (mysqli_query($conn, $sql)) {
            echo "<h3>Data stored successfully</h3>";
        } else {
            echo "ERROR: Sorry $sql. " . mysqli_error($conn);
        }

        mysqli_close($conn);
        ?>
    </center>

    <form action="labour_master.php" method="post">
        <div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
            <div style="display: inline-flex; gap: 0px; align-items: center;">
                <input type="submit" value="Done">
            </div>
        </div>
    </form>

<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
