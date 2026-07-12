<?php
$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");

if ($conn === false) {
    die("Database connection failed: " . mysqli_connect_error());
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : '';
$dateOfBirth = isset($_POST['dateOfBirth']) ? $_POST['dateOfBirth'] : '';
$address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
$phoneNumber = isset($_POST['phoneNumber']) ? mysqli_real_escape_string($conn, $_POST['phoneNumber']) : '';
$aadhar = isset($_POST['aadhar']) ? mysqli_real_escape_string($conn, $_POST['aadhar']) : '';
$joiningReference = isset($_POST['joiningReference']) ? mysqli_real_escape_string($conn, $_POST['joiningReference']) : '';
$relatedTo = isset($_POST['relatedTo']) ? mysqli_real_escape_string($conn, $_POST['relatedTo']) : '';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : null;

if ($id > 0) {
    $sql = "UPDATE labour_master SET 
                name='$name', 
                dateOfBirth='$dateOfBirth', 
                address='$address', 
                phoneNumber='$phoneNumber', 
                aadhar='$aadhar', 
                joiningReference='$joiningReference', 
                relatedTo='$relatedTo', 
                startDate='$startDate', 
                status='$status' 
            WHERE id=$id";
} else {
    $sql = "INSERT INTO labour_master 
                (name, dateOfBirth, address, phoneNumber, aadhar, joiningReference, relatedTo, startDate, status, client_id) 
            VALUES 
                ('$name', '$dateOfBirth', '$address', '$phoneNumber', '$aadhar', '$joiningReference', '$relatedTo', '$startDate', '$status', $client_id)";
}

if (mysqli_query($conn, $sql)) {
    header("Location: https://sunfra.com/farm/sunfra/attendance/labour_master_json_to_web.php");
    exit;
} else {
    echo "Error saving labour data: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
