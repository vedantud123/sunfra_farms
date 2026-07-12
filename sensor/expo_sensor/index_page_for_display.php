<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Colorful Sensor Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #4b6cb7, #182848);
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
    }

    h1 {
      font-size: 34px;
      margin-bottom: 25px;
      text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
    }

    .container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      width: 100%;
      max-width: 1100px;
    }

    .card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-8px);
    }

    .title {
      font-size: 22px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #ffeb3b;
    }

    .reading {
      font-size: 20px;
      margin: 8px 0;
    }

    .updated {
      margin-top: 12px;
      font-size: 14px;
      opacity: 0.8;
    }

    footer {
      margin-top: 35px;
      opacity: 0.8;
      font-size: 14px;
    }.card.clickable {
	  cursor: pointer;
	  transition: transform 0.25s ease, background 0.25s ease;
	}

	.card.clickable:hover {
	  transform: translateY(-10px) scale(1.02);
	  background: rgba(255, 255, 255, 0.25);
	}/* Dropdown Style */
	select, input[type="date"] {
	  background: rgba(255, 255, 255, 0.2);
	  color: #fff;
	  padding: 10px 14px;
	  border: 1px solid rgba(255, 255, 255, 0.35);
	  border-radius: 10px;
	  font-size: 16px;
	  outline: none;
	  backdrop-filter: blur(12px);
	  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
	  transition: 0.3s ease;
	  width: 100%;
	  max-width: 250px;
	}

	/* Hover effect */
	select:hover, input[type="date"]:hover {
	  background: rgba(255, 255, 255, 0.28);
	}

	/* Focus effect */
	select:focus, input[type="date"]:focus {
	  border-color: #ffeb3b;
	  box-shadow: 0 0 10px rgba(255, 235, 59, 0.7);
	}

	/* Dropdown arrow color fix */
	select {
	  appearance: none;
	  -webkit-appearance: none;
	  background-image: url("data:image/svg+xml;utf8,<svg fill='white' height='16' viewBox='0 0 24 24' width='16' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
	  background-repeat: no-repeat;
	  background-position: right 12px center;
	  background-size: 16px;
	  padding-right: 40px;
	}

	/* Container spacing */
	#customTempDates {
	  margin-top: 12px;
	}


  </style>
</head>
<body>
  <h1>Shead 1 Farm Dashboard</h1>

	  <div class="container">

	  <!-- Temperature Card -->
	  <div class="card" id="tempCard">
		<div class="title">🌡 Temperature & Humidity</div>
		<div class="reading">Temperature: <span id="tempValue">--</span> °C</div>
		<div class="reading">Humidity: <span id="humValue">--</span> %</div>
		<div class="updated">⏱ Updated: <span id="tempTime">--</span></div>
	  </div>

	  <!-- Water Flow Meter (Clickable) -->
	  <div class="card clickable" id="flowCard" onclick="window.location.href='water_flow_meter_json_to_web.php'">
		<div class="title">💧 Water Flow Meter</div>
		<div class="reading">Water Used Today: <span id="waterLiters">--</span> Liters</div>
		<div class="reading">Pulse Count: <span id="pulseCount">--</span></div>
		<div class="updated">📅 Date: <span id="flowDate">--</span></div>
	  </div>

	  <!-- Feed Silo (Clickable) -->
	  <div class="card clickable" id="thirdCard" onclick="window.location.href='silo_indicator_json_to_web.php'">
		<div class="title">📡 Feed Quantity in Silo</div>
		<div class="reading">Value: <span id="thirdValue">--</span></div>
		<div class="updated">⏱ Updated: <span id="thirdTime">--</span></div>
	  </div>

	</div>

	<div class="card" style="margin-top: 20px; width: 100%; max-width: 1100px;">
	  <div class="title">📈 Temperature Graph</div>

	  <div style="margin-bottom:10px;">
		  <input type="date" id="tempFromDate" style="padding:8px;border-radius:8px;">
	  </div>

	  <canvas id="tempChart" height="90"></canvas>
	</div>

  <footer>Designed with ❤️ for your Smart Farm</footer>
	<script>
	let tempChart = null;

	async function loadData() {
	  try {
		const tempAPI = "https://sunfra.com/farm/sensor/expo_sensor/temperature_json.php";
		const flowAPI = "https://sunfra.com/farm/sensor/expo_sensor/water_flow_meter_json.php";
		const sensor3API = "https://sunfra.com/farm/sensor/expo_sensor/silo_indicator_json.php";

		const t = await fetch(tempAPI).then(r => r.json());
		if (t.status === "success") {
		  document.getElementById("tempValue").innerText = t.data.temp;
		  document.getElementById("humValue").innerText = t.data.humidity;
		  document.getElementById("tempTime").innerText = t.data.timestamp;
		}

		const f = await fetch(flowAPI).then(r => r.json());
		if (f.status === "success" && f.data.length > 0) {
		  const today = f.data[0];
		  document.getElementById("waterLiters").innerText = today.liters_used;
		  document.getElementById("pulseCount").innerText = today.pulsecount;
		  document.getElementById("flowDate").innerText = today.date;
		}

		const s3 = await fetch(sensor3API).then(r => r.json());
		if (s3.status === "success") {
		  document.getElementById("thirdValue").innerText = s3.data.value + " KG";
		  document.getElementById("thirdTime").innerText = s3.data.timestamp;
		}

	  } catch (err) {
		console.error("Error fetching API data", err);
	  }
	}


	// --------------------------------------------------------
	//            AUTO-SET TODAY'S DATE + LOAD GRAPH
	// --------------------------------------------------------
	window.addEventListener("DOMContentLoaded", function () {
	  const dateInput = document.getElementById("tempFromDate");

	  // Set today's date
	  const today = new Date().toISOString().split("T")[0];
	  dateInput.value = today;

	  // Load today's temperature graph automatically
	  loadTemperatureGraph(today);
	});


	// --------------------------------------------------------
	//       WHEN USER SELECTS A DIFFERENT DATE → LOAD GRAPH
	// --------------------------------------------------------
	document.getElementById("tempFromDate").addEventListener("change", function () {
	  if (this.value) {
		loadTemperatureGraph(this.value);
	  }
	});


	// -------------------- LOAD TEMP GRAPH --------------------
	async function loadTemperatureGraph(selectedDate) {

	  const apiURL = `https://sunfra.com/farm/sensor/expo_sensor/temperature_day_json.php?date=${selectedDate}`;

	  const res = await fetch(apiURL).then(r => r.json());
	  if (res.status !== "success" || !res.data || res.data.length === 0) return;

	  res.data.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));

	  const labels = res.data.map(row =>
		row.timestamp.split(" ")[1].substring(0, 5)
	  );

	  const temps = res.data.map(row => parseFloat(row.temp));

	  // Destroy old chart before drawing again
	  if (tempChart) tempChart.destroy();

	  const ctx = document.getElementById("tempChart").getContext("2d");

	  tempChart = new Chart(ctx, {
		type: "line",
		data: {
		  labels,
		  datasets: [{
			label: "Temperature (°C)",
			data: temps,
			borderColor: "#ffeb3b",
			backgroundColor: "rgba(255, 235, 59, 0.3)",
			borderWidth: 3,
			tension: 0.4,
			pointRadius: 3
		  }]
		},
		options: {
		  plugins: { 
			legend: { labels: { color: "#fff" } } 
		  },
		  scales: {
			x: { ticks: { color: "#fff" } },
			y: { ticks: { color: "#fff" } }
		  }
		}
	  });
	}


	// -------------------- AUTO REFRESH HOME DATA --------------------
	loadData();
	setInterval(loadData, 5000);
	</script>


</body>
</html>
