<?php
header('Content-Type: application/json');

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$query = "SELECT * FROM batch WHERE client_id = $client_id";

$data = [];

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $cullDate = $row["cullDate"];
        $hatchDate = $row["hatchDate"];

        if ($cullDate === "0000-00-00" || empty($cullDate)) {
            if (!empty($hatchDate)) {
                $startDateObj = new DateTime($hatchDate);
                $diff = $startDateObj->diff(new DateTime());
                $runningDays = $diff->days + 1;
            } else {
                $runningDays = null;
            }
        } else {
            $runningDays = "Done";
        }

        $runningWeeks = (is_numeric($runningDays)) ? floor($runningDays / 7) : "Done";

        $duration = (is_numeric($runningDays))
            ? "$runningDays day(s), $runningWeeks week(s)"
            : "Done";

        $data[] = [
            'batch_id' => $row["batch_id"],
            'breed' => $row["breed"],
            'hatchDate' => $hatchDate,
            'noOfChicks' => $row["noOfChicks"],
            'sheadNo' => $row["sheadNo"],
            'cullDate' => $cullDate,
            'live_birds' => $row["live_birds"],
            'duration' => $duration
        ];
    }
    $result->free();
} else {
    echo json_encode(['error' => 'Error fetching batch data: ' . $mysqli->error]);
    exit;
}

$mysqli->close();

echo json_encode([
    strval($client_id) => $data
], JSON_PRETTY_PRINT);
?>
