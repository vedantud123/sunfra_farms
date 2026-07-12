<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chlorine Used</title>

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        margin: 0;
        font-family: "Poppins", sans-serif;
        background: #0d0d0d;
        color: #fff;
        display: flex;
    }

    /* SIDEBAR */
    .sidebar {
        width: 240px;
        background: #111;
        height: 100vh;
        padding: 20px;
        position: fixed;
        top: 0;
        left: 0;
        box-shadow: 3px 0 10px #000;
    }

    .sidebar h2 {
        text-align: center;
        color: #33aaff;
        font-size: 24px;
        margin-bottom: 20px;
    }

    .sidebar a {
        display: block;
        padding: 14px;
        margin: 8px 0;
        text-decoration: none;
        background: #1a1a1a;
        color: #e6e6e6;
        border-radius: 10px;
        font-size: 16px;
        transition: 0.3s;
    }
    .sidebar a:hover {
        background: #33aaff;
        color: #000;
    }

    /* MAIN CONTENT */
    .content {
        margin-left: 260px;
        padding: 20px;
        width: calc(100% - 260px);
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    h1 {
        text-align: center;
        color: #33aaff;
        margin-bottom: 15px;
    }

    /* SUMMARY CARDS */
    .stats {
        display: flex;
        justify-content: center;
        gap: 25px;
        margin-bottom: 5px;
    }

    .card {
        background: linear-gradient(135deg, #003e66, #001d33);
        padding: 20px;
        width: 260px;
        border-radius: 18px;
        text-align: center;
        box-shadow: 0 0 15px #005f99;
    }

    .card h3 {
        margin: 0;
        font-size: 18px;
        color: #66cfff;
    }
    .card p {
        margin: 10px 0 0;
        font-size: 28px;
        font-weight: bold;
        color: #fff;
    }

    /* CHART */
    .chart-container {
        width: 90%;
        height: 260px;
    }

    /* TABLE */
    table {
        width: 90%;
        border-collapse: collapse;
        margin-top: 10px;
        background: #0f1a24;
        box-shadow: 0 0 12px #003d66;
        border-radius: 10px;
        overflow: hidden;
        font-size: 14px;
    }
    th, td {
        padding: 10px;
        text-align: center;
    }
    th {
        background: #004d80;
        color: white;
    }
    tr:nth-child(even) {
        background: #0d2738;
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
    <h1>Chlorine Usage Report</h1>

    <!-- SUMMARY CARDS -->
    <div class="stats">
        <div class="card">
            <h3>Today’s Chlorine Used</h3>
            <p>2.4 L</p>
        </div>
        <div class="card">
            <h3>This Week</h3>
            <p>14.8 L</p>
        </div>
        <div class="card">
            <h3>This Month</h3>
            <p>62.3 L</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
        <canvas id="chlorineChart"></canvas>
    </div>

    <!-- Table -->
    <table>
        <tr>
            <th>Date</th>
            <th>Chlorine Used (L)</th>
            <th>Time</th>
        </tr>
        <tr><td>25 Nov 2025</td><td>2.4</td><td>06:20 AM</td></tr>
        <tr><td>24 Nov 2025</td><td>2.1</td><td>08:10 AM</td></tr>
        <tr><td>23 Nov 2025</td><td>1.9</td><td>06:55 AM</td></tr>
        <tr><td>22 Nov 2025</td><td>2.3</td><td>07:12 AM</td></tr>
        <tr><td>21 Nov 2025</td><td>2.0</td><td>06:45 AM</td></tr>
        <tr><td>20 Nov 2025</td><td>1.8</td><td>08:01 AM</td></tr>
    </table>
</div>

<script>
const ctx = document.getElementById("chlorineChart").getContext("2d");

new Chart(ctx, {
    type: "line",
    data: {
        labels: ["20 Nov", "21 Nov", "22 Nov", "23 Nov", "24 Nov", "25 Nov"],
        datasets: [{
            label: "Chlorine Used (L)",
            data: [1.8, 2.0, 2.3, 1.9, 2.1, 2.4],
            borderWidth: 3,
            borderColor: "#33aaff",
            backgroundColor: "rgba(51,170,255,0.2)",
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: "#66cfff"
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: "#fff" }
            },
            x: {
                ticks: { color: "#fff" }
            }
        },
        plugins: {
            legend: {
                labels: { color: "#fff" }
            }
        }
    }
});
</script>

</body>
</html>
