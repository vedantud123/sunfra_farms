<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location:../login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

$client_id = $_SESSION['client_id'] ?? 0;
?>

<html>
<body>
    <div class="container">			
        <button onclick="window.location.href='https://sunfra.com/farm/test/feedformuladetails/feed_formula_details.php'">Go Back</button>
    </div>
    <form action="feed_formula_details.php" method="post">
        <div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
            <div style="display: inline-flex; gap: 0px; align-items: center;">
                <input type="submit" value="Done">
                <a href="feed_formula_details.php"></a>
            </div>
        </div>
    </form>
</body>
</html>

<?php
$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

if (isset($_POST['name'])) {
    $name = $_POST['name'];

    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

    try {
        $deleteSql = "DELETE FROM feed_formula_detail WHERE feed_rawMaterial_name = ? AND client_id = ?";
        $stmt = mysqli_prepare($conn, $deleteSql);
        mysqli_stmt_bind_param($stmt, "si", $name, $client_id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error: " . mysqli_error($conn));
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Transaction failed: " . $e->getMessage();
    }
} else {
    echo "<h3>No material selected for deletion.</h3>";
}

mysqli_close($conn);
?>
