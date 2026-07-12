<?php
header('Content-Type: application/json');

$response = [];
$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_global_user");

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$query = "SELECT * FROM batch";

$batches_by_client = [];

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $client_id = $row["client_id"] ?? "Unknown";

        $batch_id = $row["batch_id"];
        $breed = $row["breed"];
        $hatchDate = $row["hatchDate"];
        $noOfChicks = $row["noOfChicks"];
        $sheadNo = $row["sheadNo"];
        $cullDate = $row["cullDate"];
        $live_birds = $row["live_birds"];

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

        $batches_by_client[$client_id][] = [
            'batch_id' => $batch_id,
            'breed' => $breed,
            'hatchDate' => $hatchDate,
            'noOfChicks' => $noOfChicks,
            'sheadNo' => $sheadNo,
            'cullDate' => $cullDate,
            'live_birds' => $live_birds,
            'duration' => $duration,
        ];
    }
    $result->free();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching batch data: ' . $mysqli->error]);
    exit;
}

$mysqli->close();

$response['batches'] = $batches_by_client;

echo json_encode($batches_by_client, JSON_PRETTY_PRINT);
?>
