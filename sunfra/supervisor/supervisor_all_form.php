<?php
session_start();
$client_id = $_SESSION['client_id'] ?? 0;

// Fetch Shead List
$shead_url = "https://sunfra.com/farm/sunfra/configuration/shead_chick_grower_json.php?client_id=$client_id";
$shead_response = file_get_contents($shead_url);
$shead_data = json_decode($shead_response, true);

$shead_list = [];
if (is_array($shead_data)) {
    foreach ($shead_data as $item) {
        if (isset($item['shead_name'])) {
            $shead_list[] = str_replace(' ', '_', $item['shead_name']);
        }
    }
}
$box_api_url = "https://sunfra.com/farm/sunfra/configuration/config_shead_box_json.php?client_id=" . $client_id;
$box_json = file_get_contents($box_api_url);
$box_data = json_decode($box_json, true);

$box_list = [];
if (is_array($box_data)) {
    foreach ($box_data as $sheadId => $boxes) {
        foreach ($boxes as $b) {
            if (isset($b['box_numbers'])) {
                $box_list[] = $b['box_numbers'];
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shead Feeding Entry</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .shead-entry-form label {
      font-weight: 500;
      color: #333;
    }
    .shead-entry-form input,
    .shead-entry-form select {
      border-radius: 10px;
      border: 1px solid #ccc;
      transition: all 0.2s ease;
    }
    .shead-entry-form input:focus,
    .shead-entry-form select:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
    }
  </style>
</head>
<body class="p-3">

<div class="container">
  <h4 class="mb-3">Shead Entry</h4>

  <div class="mb-3">
    <label for="sheadNo" class="form-label">Shead No</label>
    <select class="form-select" id="sheadNo" name="sheadNo" required>
      <option value="">Select Shead</option>
      <?php foreach ($shead_list as $shead) { ?>
        <option value="<?php echo $shead; ?>"><?php echo $shead; ?></option>
      <?php } ?>
    </select>
  </div>

  <div class="border p-3 mb-4 rounded">
    <h5>Feeding Entry</h5>
    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
    <div id="boxInputs"></div>
  </div>

  <div class="border p-3 mb-4 rounded">
    <h5>Mortality Entry</h5>
    <input type="number" id="noOfBirds" name="noOfBirds" class="form-control mb-2" placeholder="No. of Mortality" required />
  </div>
	
	<div class="border p-3 mb-4 rounded">
		<h5>Production ENtry</h5>
		<input type="number" id="noOfBirds" name="noOfBirds" class="form-control mb-2" placeholder="No. of Mortality" required />
	  </div>
	
  <button id="submitAll" class="btn btn-primary mt-3">Submit All</button>
</div>


<script>
let boxList = <?php echo json_encode($box_list); ?>;
const clientId = <?php echo json_encode($client_id); ?>;

$(document).ready(function () {
  function generateBoxInputs() {
    $('#boxInputs').empty();
    for (let i = 0; i < boxList.length; i += 2) {
      let box1 = boxList[i];
      let box2 = boxList[i + 1] || null;

      let row = `<div class="row">
        <div class="col-6 mb-2">
          <label for="${box1}">${box1}</label>
          <input type="number" step="0.01" class="form-control" id="${box1}" name="${box1}" value="0">
        </div>`;
      if (box2) {
        row += `<div class="col-6 mb-2">
          <label for="${box2}">${box2}</label>
          <input type="number" step="0.01" class="form-control" id="${box2}" name="${box2}" value="0">
        </div>`;
      }
      row += '</div>'; 
      $('#boxInputs').append(row);
    } 
  }

  $('#sheadNo').on('change', function () { 
    generateBoxInputs();
  });

  $('#submitAll').click(async function () {
    const sheadNo = $('#sheadNo').val();
    if (!sheadNo) {
      alert("Please select a Shead first.");
      return;
    }

    let feedingData = { sheadNo, client_id: clientId };
    boxList.forEach(box => {
      feedingData[box] = parseFloat($('#' + box).val()) || 0;
    });

    let mortalityData = {
      sheadNo,
      noOfBirds: $('#noOfBirds').val(),
      client_id: clientId
    };

    try {
      let feedingResp = await $.ajax({
        url: 'https://sunfra.com/farm/sunfra/supervisor/supervisor_feed_feeding_shead_save.php',
        method: 'POST',
        data: feedingData,
        dataType: 'json'
      });

      if (feedingResp.status !== "success") {
        alert("Feeding save failed: " + feedingResp.message);
        return;
      }

      let mortalityResp = await fetch("https://sunfra.com/farm/sunfra/supervisor/supervisor_shead_mortality_save.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(mortalityData)
      });

      if (!mortalityResp.ok) {
        alert("Mortality save failed.");
        return;
      }

      alert("✅ All data saved successfully!");

      $('#sheadNo').val("");
      $('#boxInputs').empty();
      $('#noOfBirds').val("");

    } catch (error) {
      console.error("Error:", error);
      alert("Something went wrong during submission.");
    }
  });
});

</script>
</body>
</html>
