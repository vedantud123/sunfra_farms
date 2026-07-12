<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$conn = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

$url = "https://sunfra.com/farm/test2/profit_and_loss_details/summary_report_processing.php?client_id=$client_id";
$response = file_get_contents($url);

if ($client_id <= 0) {
    echo json_encode(["error" => "Invalid client_id"]);
    exit;
}

$expectedKeys = [];

$sqlKeys = "
    SELECT DISTINCT summary
    FROM summary_report_test
    WHERE client_id = ?
";
$stmtKeys = $conn->prepare($sqlKeys);
$stmtKeys->bind_param("i", $client_id);
$stmtKeys->execute();
$resKeys = $stmtKeys->get_result();

while ($row = $resKeys->fetch_assoc()) {
    $keyLower = strtolower(trim($row['summary']));

    if (strpos($keyLower, 'production') === 0 || $keyLower === 'total_production' || $keyLower === 'scrap_production') {
        $section = 'Production';
    } elseif (strpos($keyLower, 'damage') === 0 || $keyLower === 'total_damaged') {
        $section = 'Damage';
    } elseif (strpos($keyLower, 'percentage') === 0 || $keyLower === 'average_percentage') {
        $section = 'Percentage';
    } elseif (strpos($keyLower, 'feed_intake') === 0) {
        $section = 'Feed Intake';
    } elseif (strpos($keyLower, 'mortality') === 0) {
        $section = 'Mortality';
    } elseif (strpos($keyLower, 'egg_weight') === 0) {
        $section = 'Egg Weight';
    } elseif (strpos($keyLower, 'profit_and_loss') === 0 || $keyLower === 'total_profit_loss') {
        $section = 'Profit & Loss';
    } else {
        $section = 'Other';
    }

    if (!isset($expectedKeys[$section])) {
        $expectedKeys[$section] = [];
    }

    if (!in_array($row['summary'], $expectedKeys[$section])) {
        $expectedKeys[$section][] = $row['summary'];
    }
}
$stmtKeys->close();

// Step 1.1: Dynamic ordering for all sections
foreach ($expectedKeys as $sectionName => &$keys) {
    // Separate numeric keys
    $numericKeys = array_filter($keys, function($key) {
        return preg_match('/\d+$/', $key);
    });

    // Sort numeric keys by number
    usort($numericKeys, function($a, $b) {
        return (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - 
               (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
    });

    // Find total_* keys
    $totalKeys = array_filter($keys, function($key) {
        return preg_match('/^total_/i', $key);
    });

    // Find scrap_* keys
    $scrapKeys = array_filter($keys, function($key) {
        return preg_match('/^scrap_/i', $key);
    });

    // Remaining keys
    $otherKeys = array_diff($keys, $numericKeys, $totalKeys, $scrapKeys);

    // Merge: numeric → total → scrap → others
    $keys = array_merge($numericKeys, $totalKeys, $scrapKeys, $otherKeys);
}
unset($keys);

// Step 2: Get data rows
$sql = "
    SELECT `date`, `summary`, `value`
    FROM summary_report_test
    WHERE client_id = ?
    ORDER BY `date`, `summary`
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

// Step 3: Group data by date and section
while ($row = $result->fetch_assoc()) {
    $date = $row['date'];
    $keyLower = strtolower(trim($row['summary']));

    if (strpos($keyLower, 'production') === 0 || $keyLower === 'total_production' || $keyLower === 'scrap_production') {
        $section = 'Production';
    } elseif (strpos($keyLower, 'damage') === 0 || $keyLower === 'total_damaged') {
        $section = 'Damage';
    } elseif (strpos($keyLower, 'percentage') === 0 || $keyLower === 'average_percentage') {
        $section = 'Percentage';
    } elseif (strpos($keyLower, 'feed_intake') === 0) {
        $section = 'Feed Intake';
    } elseif (strpos($keyLower, 'mortality') === 0) {
        $section = 'Mortality';
    } elseif (strpos($keyLower, 'egg_weight') === 0) {
        $section = 'Egg Weight';
    } elseif (strpos($keyLower, 'profit_and_loss') === 0 || $keyLower === 'total_profit_loss') {
        $section = 'Profit & Loss';
    } else {
        $section = 'Other';
    }

    if (!isset($data[$date])) $data[$date] = [];
    if (!isset($data[$date][$section])) $data[$date][$section] = [];

    $data[$date][$section][$row['summary']] = is_null($row['value']) ? null : (float)$row['value'];
}

$stmt->close();
$conn->close();

// Step 4: Fill missing keys & reorder within sections
foreach ($data as $date => &$sections) {
    foreach ($expectedKeys as $sectionName => $keys) {
        if (!isset($sections[$sectionName])) {
            $sections[$sectionName] = [];
        }
        foreach ($keys as $key) {
            if (!isset($sections[$sectionName][$key])) {
                $sections[$sectionName][$key] = 0;
            }
        }
        // Reorder keys
        $orderedItems = [];
        foreach ($keys as $key) {
            $orderedItems[$key] = $sections[$sectionName][$key];
        }
        $sections[$sectionName] = $orderedItems;
    }
}
unset($sections);

// Step 5: Sort sections in original order
$sectionOrder = array_merge(array_keys($expectedKeys), ['Other']);
foreach ($data as $date => $sections) {
    $orderedSections = [];
    foreach ($sectionOrder as $sectionName) {
        if (isset($sections[$sectionName])) {
            $orderedSections[$sectionName] = $sections[$sectionName];
        }
    }
    foreach ($sections as $sectionName => $items) {
        if (!isset($orderedSections[$sectionName])) {
            $orderedSections[$sectionName] = $items;
        }
    }
    $data[$date] = $orderedSections;
}

// Output JSON
echo json_encode($data, JSON_PRETTY_PRINT);
