<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        margin: 0;
        font-family: "Poppins", sans-serif;
        background: #000;
        color: white;
        display: flex;
    }

    .sidebar {
        width: 230px;
        background: #0d1b2a;
        height: 100vh;
        padding: 25px;
        box-sizing: border-box;
        color: white;
    }

    .sidebar h2 {
        margin-bottom: 25px;
    }

    .sidebar a {
        display: block;
        padding: 12px 8px;
        margin-bottom: 10px;
        color: #cfd8dc;
        text-decoration: none;
        font-size: 17px;
        border-radius: 8px;
    }

    .sidebar a:hover {
        background-color: #1b263b;
    }

    .main {
        flex: 1;
        padding: 30px;
        height: 100vh;
        overflow-y: auto;
    }

    .top-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-bottom: 25px;
    }

    .card {
        background: #111827;
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 0 12px rgba(255,255,255,0.05);
    }

    .card h3 {
        font-size: 20px;
        margin: 0;
        color: #cfd8dc;
    }

    .value {
        font-size: 35px;
        font-weight: bold;
        margin-top: 10px;
        color: #00aaff;
    }

    .graph-card {
        background: #111827;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        height: 340px;
    }

    canvas {
        width: 100% !important;
        height: 260px !important;
    }

    .bottom-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
    }

    .used-card {
        background: #111827;
        padding: 25px;
        text-align: center;
        border-radius: 15px;
        box-shadow: 0 0 12px rgba(255,255,255,0.06);
    }

    .used-value {
        font-size: 32px;
        color: #00ff80;
        font-weight: bold;
        margin-top: 10px;
    }
</style>

</head>
<body>

<div class="sidebar">
    <h2>Dashboard</h2>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/dosing_pump_web_page.php">🏠 Home</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/acid_used_web_page.php">🧪 Acid Used</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/chlorine_used_web_page.php">🧼 Chlorine Used</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/ph_data_web_page.php">📊 pH Data</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/orp_data_web_page.php">⚡ ORP Data</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/water_data_web_page.php">💧 Water Data</a>
</div>

<div class="main">

    <div class="top-cards">
        <div class="card">
            <h3>Water Flow</h3>
            <div id="waterFlow" class="value">0 L/min</div>
        </div>

        <div class="card">
            <h3>ORP</h3>
            <div id="orpValue" class="value">0 mV</div>
        </div>

        <div class="card">
            <h3>pH Level</h3>
            <div id="phValue" class="value">0.00</div>
        </div>
    </div>

    <!-- Graph Section -->
    <div class="graph-card">
        <canvas id="waterChart"></canvas>
    </div>

    <!-- Bottom Cards -->
    <div class="bottom-cards">
        <div class="used-card">
            <h3>Acid Used</h3>
            <div id="acidUsed" class="used-value">0 L</div>
        </div>

        <div class="used-card">
            <h3>Chlorine Used</h3>
            <div id="chlorineUsed" class="used-value">0 L</div>
        </div>

        <div class="used-card">
            <h3>Total Water Used</h3>
            <div id="totalWater" class="used-value">0 L</div>
        </div>
    </div>

</div>
<script>

    /* ---------------------------
       DAILY TARGET LIMITS
       --------------------------- */
    const DAILY_LIMITS = {
        acid: 4,           // 4 Liters per day
        chlorine: 3,       // 3 Liters per day
        water: 200         // 200 Liters per day
    };

    /* ---------------------------
       GET CURRENT VALUES BASED ON TIME
       --------------------------- */
    function calculateTodayValues() {
        const now = new Date();
        const minutesPassed = now.getHours() * 60 + now.getMinutes(); 
        const minutesPerDay = 24 * 60;

        const dayProgress = minutesPassed / minutesPerDay; // 0 → 1

        return {
            acid: DAILY_LIMITS.acid * dayProgress,
            chlorine: DAILY_LIMITS.chlorine * dayProgress,
            water: DAILY_LIMITS.water * dayProgress
        };
    }

    /* ---------------------------
       LIVE SENSOR UPDATES (Flow, pH, ORP)
       --------------------------- */
    function getLiveRandomSensorValues() {
        return {
            flow: (5 + Math.random() * 5).toFixed(1),
            orp: Math.floor(300 + Math.random() * 400),
            ph: (6.5 + Math.random()).toFixed(2)
        };
    }

    const ctx = document.getElementById("waterChart").getContext("2d");

    const chartData = {
        labels: ["5m", "4m", "3m", "2m", "1m", "Now"],
        datasets: [{
            label: "Water (L/min)",
            data: [5, 6, 7, 8, 9, 10],
            borderWidth: 3,
            borderColor: "#00aaff",
            backgroundColor: "rgba(0,170,255,0.25)",
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: "#00aaff"
        }]
    };

    const waterChart = new Chart(ctx, {
        type: "line",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    function updateLiveData() {

        const today = calculateTodayValues();

        document.getElementById("acidUsed").innerHTML =
            today.acid.toFixed(2) + " L";

        document.getElementById("chlorineUsed").innerHTML =
            today.chlorine.toFixed(2) + " L";

        document.getElementById("totalWater").innerHTML =
            today.water.toFixed(1) + " L";

        const live = getLiveRandomSensorValues();
        document.getElementById("waterFlow").innerHTML = live.flow + " L/min";
        document.getElementById("orpValue").innerHTML = live.orp + " mV";
        document.getElementById("phValue").innerHTML = live.ph;

        // Update chart
        chartData.datasets[0].data.push(live.flow);
        chartData.datasets[0].data.shift();
        waterChart.update();
    }

    setInterval(updateLiveData, 2000);

</script>
</body>
</html>
