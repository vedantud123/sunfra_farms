<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set('Asia/Kolkata');

?>

<!DOCTYPE html>
<html>

<head>
    <title>Insert Page page</title>
</head>

	<body>
	<button onclick="history.back()">Go Back</button>
		<center>
		<?php
		$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
			if ($conn->connect_error) {
				die("ERROR: Could not connect. " . $conn->connect_error);
			}

			$uploadDir = "uploads/";
			if (!is_dir($uploadDir)) {
				mkdir($uploadDir, 0777, true);
			}
			$id =  $_REQUEST['id'];
			$timestamp = date('Y-m-d H:i:s');
			$sheadNo = $_POST['sheadNo'] ?? '';
			$remarks = $_POST['remarks'] ?? '';

			function handleFileUpload($file, $uploadDir) {
				if (isset($_FILES[$file]) && $_FILES[$file]['error'] === UPLOAD_ERR_OK) {
					$fileName = basename($_FILES[$file]['name']);
					$targetFile = $uploadDir . time() . "_" . $fileName;
					if (move_uploaded_file($_FILES[$file]['tmp_name'], $targetFile)) {
						return $targetFile;
					}
				}
				return null;
			}
					$tarkiyaPath = handleFileUpload('photo_tarkiya', $uploadDir);
					$heartPath = handleFileUpload('photo_heart', $uploadDir);
					$leverPath = handleFileUpload('photo_lever', $uploadDir);
					$gizzardPath = handleFileUpload('photo_gizzard', $uploadDir);
					$kidneyPath = handleFileUpload('photo_kidney', $uploadDir);
					$ovariesPath = handleFileUpload('photo_ovaries', $uploadDir);
					$option1Path = handleFileUpload('photo_option_1', $uploadDir);
					$option2Path = handleFileUpload('photo_option_2', $uploadDir);
					$option3Path = handleFileUpload('photo_option_3', $uploadDir);
					$option4Path = handleFileUpload('photo_option_4', $uploadDir);
			if($id>=1){
				$stmt = $conn->prepare("UPDATE postmartam SET sheadNo=?, tarkiya=?, heart=?, lever=?, gizzard=?, kidney=?, ovaries=?, option_1=?, option_2=?, option_3=?, option_4=?, remarks=? WHERE id=?");
				$stmt->bind_param("ssssssssssssi", $sheadNo, $tarkiyaPath, $heartPath, $leverPath, $gizzardPath, $kidneyPath, $ovariesPath, $option1Path, $option2Path, $option3Path, $option4Path, $remarks, $id);
			}else{
				$stmt = $conn->prepare("INSERT INTO postmartam (sheadNo, tarkiya, heart, lever, gizzard, kidney, ovaries, option_1, option_2, option_3, option_4, remarks, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$stmt->bind_param("ssssssssssss", $sheadNo, $tarkiyaPath, $heartPath, $leverPath, $gizzardPath, $kidneyPath, $ovariesPath, $option1Path, $option2Path, $option3Path, $option4Path, $remarks, $timestamp);
			}
			if ($stmt->execute()) {
				echo "<p>Data stored successfully!</p>";
			} else {
				error_log("Database Error: " . $stmt->error);
				echo "<p>Error storing data. Please try again.</p>";
			}
			$conn->close();
		?>
		</center>
		<form action="postmartam.php" method="post">
			<div style="display: flex; justify-content: center; align-items: center; height: 10vh;">
				<div style="display: inline-flex; gap: 0px; align-items: center;">
					<input type="submit" value="Done">
					<a href="postmartam.php"></a>
				</div>
			</div>	
		</form>
	</body>

</html>