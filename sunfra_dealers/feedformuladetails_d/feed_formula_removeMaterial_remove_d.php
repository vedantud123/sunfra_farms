<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login_d/login_d.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');
?>
<html>
	<body>
		<div class="container">			
			<button onclick="window.location.href='https://sunfra.com/farm/sunfra_dealers/feedformuladetails_d/feed_formula_details_d.php'">Go Back</button>
		</div>
		<form action="feed_formula_details_d.php" method="post">
			<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
			<div style="display: inline-flex; gap: 0px; align-items: center;">
				<input type="submit" value="Done">
				<a href="feed_formula_details_d.php"></a>
			</div>
			</div>
		</form>
	</body>
</html>

<?php
$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_yugandhar_pf");
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}


if (isset($_POST['name'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

    try {
        $deleteSql = "DELETE FROM feed_formula_detail WHERE feed_rawMaterial_name = '$name'";

        if (!mysqli_query($conn, $deleteSql)) {
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
