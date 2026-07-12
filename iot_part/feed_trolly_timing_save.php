<?php
header('Content-Type: application/json; charset=utf-8');

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; 
}

require_once 'db.php';

$action = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
} else {
    $post = json_decode(file_get_contents('php://input'), true);
    if (!is_array($post)) {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
    } else {
        $action = isset($post['action']) ? $post['action'] : '';
    }
}

if (empty($action)) $action = 'list';

$mysqli = get_db_connection();

if ($action === 'list') {
    $sql = "SELECT id, DATE_FORMAT(feeding_time, '%H:%i:%s') AS feeding_time FROM feed_trolly_timing ORDER BY feeding_time";
    $res = $mysqli->query($sql);
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $r['id'] = (int)$r['id'];
        $rows[] = $r;
    }
    echo json_encode(['success' => true, 'count' => count($rows), 'data' => $rows]);
    $mysqli->close();
    exit;
}

if ($action === 'add') {
    $raw = json_decode(file_get_contents('php://input'), true);
    $timeVal = null;
    if (is_array($raw) && isset($raw['feeding_time'])) {
        $timeVal = trim($raw['feeding_time']);
    } elseif (isset($_POST['feeding_time'])) {
        $timeVal = trim($_POST['feeding_time']);
    }

    if (empty($timeVal)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'feeding_time is required (format HH:MM or HH:MM:SS)']);
        $mysqli->close();
        exit;
    }

    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeVal)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid time format. Use HH:MM or HH:MM:SS']);
        $mysqli->close();
        exit;
    }

    if (preg_match('/^\d{2}:\d{2}$/', $timeVal)) {
        $timeVal .= ':00';
    }

    $checkStmt = $mysqli->prepare("SELECT id FROM feed_trolly_timing WHERE feeding_time = ?");
    $checkStmt->bind_param('s', $timeVal);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This time already exists']);
        $checkStmt->close();
        $mysqli->close();
        exit;
    }
    $checkStmt->close();

    $stmt = $mysqli->prepare("INSERT INTO feed_trolly_timing (feeding_time) VALUES (?)");
    $stmt->bind_param('s', $timeVal);
    if ($stmt->execute()) {
        @file_get_contents(__DIR__ . '/create_json.php');
        echo json_encode(['success' => true, 'message' => 'Inserted', 'id' => $mysqli->insert_id, 'feeding_time' => $timeVal]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}

if ($action === 'delete') {
    $raw = json_decode(file_get_contents('php://input'), true);
    $id = null;
    if (is_array($raw) && isset($raw['id'])) {
        $id = (int)$raw['id'];
    } elseif (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
    }

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id is required']);
        $mysqli->close();
        exit;
    }

    $stmt = $mysqli->prepare("DELETE FROM feed_trolly_timing WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        @file_get_contents(__DIR__ . '/create_json.php');
        echo json_encode(['success' => true, 'message' => 'Deleted', 'id' => $id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $stmt->error]);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown action']);
$mysqli->close();
exit;
?>
