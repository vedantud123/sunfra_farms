<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acid Used Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        margin: 0;
        font-family: "Poppins", sans-serif;
        background: #000;
        color: white;
        display: flex;
    }

    /* Sidebar */
    .sidebar {
        width: 230px;
        background: #0d1b2a;
        height: 100vh;
        padding: 25px;
        box-sizing: border-box;
        color: white;
        position: fixed;
        left: 0;
        top: 0;
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

    /* Main content */
    .main {
        margin-left: 250px;
        padding: 30px;
        flex: 1;
    }

    h1 {
        text-align: center;
        font-size: 32px;
        margin-bottom: 20px;
        color: #00eaff;
    }

    /* Top Stats */
    .stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .card {
        background: #111827;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(255,255,255,0.06);
        text-align: center;
    }

    .card h3 {
        color: #b0bec5;
        margin-bottom: 5px;
    }

    .value {
        font-size: 35px;
        font-weight: bold;
        color: #00ff95;
    }

    /* Graph */
    .graph-container {
        background: #111827;
        padding: 25px;
        border-radius: 15px;
        height: 380px;
        margin-bottom: 30px;
        box-shadow: 0 0 15px rgba(255,255,255,0.06);
    }

    canvas {
        width: 100% !important;
        height: 300px !important;
    }

    /* Data Table */
    table {
        width: 100%;
        border-collapse: collapse;
        background: #111827;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 0 12px rgba(255,255,255,0.06);
    }

    th, td {
        padding: 14px;
        text-align: center;
    }

    th {
        background: #1f2937;
        color: #00eaff;
        font-size: 18px;
    }

    tr:nth-child(even) {
        background: #0f172a;
    }

    tr:nth-child(odd) {
        background: #111827;
    }
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Dashboard</h2>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/dosing_pump_web_page.php">🏠 Home</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/acid_used_web_page.php">🧪 Acid Used</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/chlorine_used_web_page.php">🧼 Chlorine Used</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/ph_data_web_page.php">📊 pH Data</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/orp_data_web_page.php">⚡ ORP Data</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/water_data_web_page.php">💧 Water Data</a>
</div>

<!-- Main Content -->
<div class="main">

<h1>Acid Used – Analytics Dashboard</h1>

<!-- TOP CARDS -->
<div class="stats">
    <div class="card">
        <h3>Today's Acid Used</h3>
        <div class="value" id="todayAcid">1.8 L</div>
    </div>

    <div class="card">
        <h3>Total This Week</h3>
        <div class="value" id="weekAcid">12.4 L</div>
    </div>

    <div class="card">
        <h3>Monthly Total</h3>
        <div class="value" id="monthAcid">51.9 L</div>
    </div>
</div>

<!-- GRAPH -->
<div class="graph-container">
    <canvas id="acidGraph"></canvas>
</div>

<!-- DEMO TABLE -->
<table>
    <tr>
        <th>Date</th>
        <th>Acid Used (L)</th>
        <th>Time</th>
    </tr>
    <tr><td>25 Nov 2025</td><td>1.8</td><td>06:20 AM</td></tr>
    <tr><td>24 Nov 2025</td><td>2.1</td><td>08:10 AM</td></tr>
    <tr><td>23 Nov 2025</td><td>1.6</td><td>06:55 AM</td></tr>
    <tr><td>22 Nov 2025</td><td>2.0</td><td>07:12 AM</td></tr>
    <tr><td>21 Nov 2025</td><td>1.9</td><td>06:45 AM</td></tr>
    <tr><td>20 Nov 2025</td><td>2.0</td><td>08:01 AM</td></tr>
    <tr><td>19 Nov 2025</td><td>1.8</td><td>06:34 AM</td></tr>
</table>

</div>

<script>
    const labels = ["19 Nov", "20 Nov", "21 Nov", "22 Nov", "23 Nov", "24 Nov", "25 Nov"];
    const acidValues = [1.8, 2.0, 1.9, 2.0, 1.6, 2.1, 1.8];

    const ctx = document.getElementById("acidGraph").getContext("2d");

    new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: "Acid Used (L)",
                data: acidValues,
                borderWidth: 3,
                borderColor: "#00eaff",
                backgroundColor: "rgba(0,234,255,0.25)",
                pointRadius: 5,
                pointBackgroundColor: "#00eaff",
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { color: "#9ca3af" } },
                x: { ticks: { color: "#9ca3af" } }
            }
        }
    });
</script>

</body>
</html>
