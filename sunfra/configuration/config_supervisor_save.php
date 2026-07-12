<?php
// Database connection
$conn = mysqli_connect("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if (!$conn) {
    die("❌ Database Connection failed: " . mysqli_connect_error());
}

// Validate client_id
if (!isset($_POST['client_id'])) {
    echo "❌ client_id missing";
    exit;
}

$client_id = intval($_POST['client_id']);
$feature   = isset($_POST['role']) ? trim($_POST['role']) : '';
$usernames = isset($_POST['username']) ? $_POST['username'] : []; // Accepts array

if ($feature === '' || empty($usernames)) {
    echo "❌ Missing required fields";
    exit;
}

$successCount = 0;
$duplicateCount = 0;
$failCount = 0;

// Prepare SELECT statement to check duplicates
$check = mysqli_prepare($conn, "SELECT id FROM config_feature WHERE feature = ? AND username = ? AND client_id = ?");

// Prepare INSERT statement
$insert = mysqli_prepare($conn, "INSERT INTO config_feature (feature, username, client_id) VALUES (?, ?, ?)");

foreach ($usernames as $username) {
    $username = trim($username);
    if ($username === '') continue;

    // Check if already exists
    mysqli_stmt_bind_param($check, "ssi", $feature, $username, $client_id);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);

    if (mysqli_fetch_assoc($result)) {
        // Duplicate found
        $duplicateCount++;
        continue;
    }

    // Insert if not duplicate
    mysqli_stmt_bind_param($insert, "ssi", $feature, $username, $client_id);
    if (mysqli_stmt_execute($insert)) {
        $successCount++;
    } else {
        $failCount++;
    }
}

mysqli_stmt_close($check);
mysqli_stmt_close($insert);
mysqli_close($conn);

// Response message
echo "✅ Successfully assigned $successCount supervisor(s).";
if ($duplicateCount > 0) {
    echo " ⚠️ Skipped $duplicateCount duplicate supervisor(s).";
}
if ($failCount > 0) {
    echo " ❌ Failed to assign $failCount supervisor(s).";
}
?>
