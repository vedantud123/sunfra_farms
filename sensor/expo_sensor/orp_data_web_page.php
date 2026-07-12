<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ORP Data</title>

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        margin: 0;
        font-family: "Poppins", sans-serif;
        background: #0b0b0b;
        color: white;
        display: flex;
    }

    /* SIDEBAR */
    .sidebar {
        width: 240px;
        background: #111;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        padding: 20px;
        box-shadow: 2px 0 10px #000;
    }

    .sidebar h2 {
        text-align: center;
        color: #ff7b39;
        margin-bottom: 25px;
        font-size: 24px;
    }

    .sidebar a {
        display: block;
        padding: 12px;
        margin: 8px 0;
        background: #1a1a1a;
        color: #e0e0e0;
        text-decoration: none;
        border-radius: 10px;
        transition: 0.3s;
        font-size: 16px;
    }
    .sidebar a:hover {
        background: #ff7b39;
        color: #000;
    }

    /* CONTENT AREA */
    .content {
        margin-left: 260px;
        width: calc(100% - 260px);
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    h1 {
        margin: 10px 0;
        color: #ff7b39;
        font-size: 30px;
    }

    /* CARDS */
    .cards {
        display: flex;
        gap: 25px;
        margin-bottom: 10px;
    }

    .card {
        width: 260px;
        background: linear-gradient(135deg, #330f00, #1a0700);
        padding: 20px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0px 0px 15px #ff5e00;
    }

    .card h3 {
        margin: 0;
        color: #ffa06a;
        font-size: 18px;
    }

    .card p {
        margin: 10px 0 0;
        font-size: 28px;
        font-weight: bold;
        color: #ffba8c;
    }

    /* CHART */
    .chart-container {
        width: 90%;
        height: 260px;
        margin-top: 10px;
    }

    /* TABLE */
    table {
        width: 90%;
        margin-top: 10px;
        border-collapse: collapse;
        background: #1a0f0a;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 15px #ff5e00;
        font-size: 14px;
    }
    th, td {
        padding: 10px;
        text-align: center;
    }
    th {
        background: #662200;
        color: #fff;
    }
    tr:nth-child(even) {
        background: #331500;
    }
</style>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Dashboard</h2>
     <a href="https://sunfra.com/farm/sensor/expo_sensor/dosing_pump_web_page.php">🏠 Home</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/acid_used_web_page.php">🧪 Acid Used</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/chlorine_used_web_page.php">🧼 Chlorine Used</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/ph_data_web_page.php">📊 pH Data</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/orp_data_web_page.php">⚡ ORP Data</a>
      <a href="https://sunfra.com/farm/sensor/expo_sensor/water_data_web_page.php">💧 Water Data</a>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    <h1>ORP Data Report</h1>

    <!-- Cards -->
    <div class="cards">
        <div class="card">
            <h3>Current ORP</h3>
            <p>552 mV</p>
        </div>
        <div class="card">
            <h3>Today's Average</h3>
            <p>538 mV</p>
        </div>
        <div class="card">
            <h3>Lowest Today</h3>
            <p>510 mV</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
        <canvas id="orpChart"></canvas>
    </div>

    <!-- Table -->
    <table>
        <tr>
            <th>Date</th>
            <th>ORP Value (mV)</th>
            <th>Time</th>
        </tr>
        <tr><td>25 Nov 2025</td><td>552</td><td>08:25 AM</td></tr>
        <tr><td>25 Nov 2025</td><td>545</td><td>07:40 AM</td></tr>
        <tr><td>24 Nov 2025</td><td>538</td><td>06:55 AM</td></tr>
        <tr><td>23 Nov 2025</td><td>525</td><td>07:10 AM</td></tr>
        <tr><td>22 Nov 2025</td><td>518</td><td>06:50 AM</td></tr>
    </table>
</div>

<!-- CHART SCRIPT -->
<script>
const ctx = document.getElementById("orpChart").getContext("2d");

new Chart(ctx, {
    type: "line",
    data: {
        labels: ["6 AM", "7 AM", "8 AM", "9 AM", "10 AM"],
        datasets: [{
            label: "ORP (mV)",
            data: [520, 535, 545, 552, 540],
            borderColor: "#ff7b39",
            backgroundColor: "rgba(255, 123, 57, 0.25)",
            borderWidth: 3,
            tension: 0.4,
            pointRadius: 6,
            pointBackgroundColor: "#ffb894"
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: "#fff" } } },
        scales: {
            x: { ticks: { color: "#fff" }},
            y: { ticks: { color: "#fff" }}
        }
    }
});
</script>

</body>
</html>
