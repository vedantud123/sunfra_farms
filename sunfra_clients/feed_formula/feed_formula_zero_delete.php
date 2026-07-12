<?php
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

$client_id = (int)($_GET['client_id'] ?? 0);
if (!$client_id) {
    echo json_encode(["status" => "error", "message" => "❌ Client ID missing from request"]);
    exit;
}

$shead_url = "https://sunfra.com/farm/sunfra_clients/configuration/shead_chick_grower_json.php?client_id=$client_id";
$shead_response = file_get_contents($shead_url);

if ($shead_response === false) {
    echo json_encode(["error" => "Unable to fetch shead data"]);
    exit;
}

$shead_data = json_decode($shead_response, true);
if (!is_array($shead_data)) {
    echo json_encode(["error" => "Invalid JSON received"]);
    exit;
}

$shead_list = [];
foreach ($shead_data as $item) {
    if (isset($item['shead_name'])) {
        $normalized = strtolower(str_replace(' ', '_', $item['shead_name']));
        $shead_list[] = $normalized;
    }
}

$shead_list_str = "'" . implode("','", $shead_list) . "'";

$delete_null_value_query = "
    DELETE fd
    FROM `feed_formula_detail` fd
    JOIN (
        SELECT feed_rawMaterial_name
        FROM `feed_formula_detail`
        WHERE feed_formulaType IN ($shead_list_str)
          AND client_id = $client_id
        GROUP BY feed_rawMaterial_name
        HAVING SUM(quantity) = 0
    ) AS materials_to_delete
      ON fd.feed_rawMaterial_name = materials_to_delete.feed_rawMaterial_name
    WHERE fd.feed_formulaType IN ($shead_list_str)
      AND fd.client_id = $client_id;
";

if ($mysqli->query($delete_null_value_query)) {
    echo json_encode(["status" => "success", "message" => "✅ Null value rows deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "❌ Error: " . $mysqli->error]);
}

// ✅ Close connection
$mysqli->close();
?>
