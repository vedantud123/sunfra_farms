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

$url = "https://sunfra.com/farm/sunfra_clients/profit_and_loss_details/summary_report_processing.php?client_id=$client_id";
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

foreach ($expectedKeys as $sectionName => &$keys) {

    if ($sectionName === "Production") {
        // ✅ Special ordering, exclude ch/gw
        $numericKeys = array_filter($keys, fn($key) => preg_match('/^production\d+$/i', $key));
        usort($numericKeys, fn($a, $b) => (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT));

        $totalKeys   = array_filter($keys, fn($key) => preg_match('/^total_/i', $key));
        $scrapKeys   = array_filter($keys, fn($key) => preg_match('/^scrap_/i', $key));

        $otherKeys   = array_filter($keys, fn($key) => !preg_match('/(ch|gw)/i', $key));
        $otherKeys   = array_diff($otherKeys, $numericKeys, $totalKeys, $scrapKeys);

        $keys = array_merge($numericKeys, $totalKeys, $scrapKeys, $otherKeys);

    } elseif ($sectionName === "Damage") {
        // ✅ Special ordering, exclude ch/gw
        $damageKeys = array_filter($keys, fn($key) => preg_match('/^damage\d+$/i', $key));
        usort($damageKeys, fn($a, $b) => (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT));

        $totalKeys  = array_filter($keys, fn($key) => $key === 'total_damaged');

        $otherKeys  = array_filter($keys, fn($key) => !preg_match('/(ch|gw)/i', $key));
        $otherKeys  = array_diff($otherKeys, $damageKeys, $totalKeys);

        $keys = array_merge($damageKeys, $totalKeys, $otherKeys);

    } elseif ($sectionName === "Percentage") {
        // ✅ Special ordering, exclude ch/gw
        $numericKeys = array_filter($keys, fn($key) => preg_match('/^percentage\d+$/i', $key));
        usort($numericKeys, fn($a, $b) => (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT));

        $avgKeys     = array_filter($keys, fn($key) => $key === 'average_percentage');

        $otherKeys   = array_filter($keys, fn($key) => !preg_match('/(ch|gw)/i', $key));
        $otherKeys   = array_diff($otherKeys, $numericKeys, $avgKeys);

        $keys = array_merge($numericKeys, $avgKeys, $otherKeys);

    } elseif ($sectionName === "Egg Weight") {
        // ✅ Special ordering, exclude ch/gw
        $eggKeys = array_filter($keys, fn($key) => preg_match('/^egg_weight\d+$/i', $key));
        usort($eggKeys, fn($a, $b) => (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT));

        $otherKeys = array_filter($keys, fn($key) => !preg_match('/(ch|gw)/i', $key));
        $otherKeys = array_diff($otherKeys, $eggKeys);

        $keys = array_merge($eggKeys, $otherKeys);

    } elseif ($sectionName === "Profit & Loss") {
        // ✅ Special ordering: numbers first, then ch/gw, then total at last
        $numericKeys = array_filter($keys, fn($key) => preg_match('/^profit_and_loss\d+$/i', $key));
        usort($numericKeys, fn($a, $b) => (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT) - (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT));

        $chGwKeys = array_filter($keys, fn($key) => preg_match('/(ch|gw)/i', $key));

        $totalKeys = array_filter($keys, fn($key) => $key === 'total_profit_loss');

        $otherKeys = array_diff($keys, $numericKeys, $chGwKeys, $totalKeys);

        $keys = array_merge($numericKeys, $chGwKeys, $otherKeys, $totalKeys);

    } else {
        // ✅ Feed Intake, Mortality, and others → keep normal order
        $keys = array_values($keys);
    }
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
?>
