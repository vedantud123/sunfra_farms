<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>pH Data</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

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
        color: #55ff88;
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
        background: #55ff88;
        color: #000;
    }

    /* MAIN PAGE DESIGN */
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
        color: #55ff88;
        font-size: 30px;
    }

    /* Cards */
    .cards {
        display: flex;
        gap: 25px;
        margin-bottom: 10px;
    }

    .card {
        width: 260px;
        background: linear-gradient(135deg, #003316, #001a0c);
        padding: 20px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0px 0px 15px #00994d;
    }

    .card h3 {
        margin: 0;
        color: #55ff88;
        font-size: 18px;
    }
    .card p {
        margin: 10px 0 0;
        font-size: 28px;
        font-weight: bold;
    }

    /* Chart */
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
        background: #0f1f17;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px #006633;
        font-size: 14px;
    }
    th, td {
        padding: 10px;
        text-align: center;
    }
    th {
        background: #004d26;
        color: #fff;
    }
    tr:nth-child(even) {
        background: #0d261a;
    }/* FILTER BAR */
	.filter-section {
		width: 90%;
		margin-top: 10px;
		display: flex;
		justify-content: flex-end;   /* ⬅️ Moves filters to the right side */
		align-items: center;
		gap: 20px;
	}

	.filter-dropdown {
		padding: 10px 15px;
		background: #0f1f17;
		color: #55ff88;
		border: 2px solid #055d2d;
		border-radius: 8px;
		font-size: 16px;
		cursor: pointer;
		outline: none;
		transition: 0.3s;
	}
	.filter-dropdown:hover {
		background: #55ff88;
		color: black;
	}

	.custom-date-box {
		display: none;
		gap: 10px;
	}

	.date-input {
		padding: 10px;
		background: #0f1f17;
		color: #55ff88;
		border: 2px solid #055d2d;
		border-radius: 8px;
		outline: none;
		font-size: 15px;
	}

	.apply-btn {
		padding: 10px 20px;
		background: #55ff88;
		color: black;
		border: none;
		border-radius: 8px;
		font-weight: bold;
		cursor: pointer;
		transition: 0.3s;
	}
	.apply-btn:hover {
		background: #33cc66;
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
    <h1>pH Data Report</h1>
	
	<!-- Filter Section -->
	<div class="filter-section">
		<select id="filterSelect" class="filter-dropdown" onchange="handleFilterChange()">
			<option value="today">Today</option>
			<option value="yesterday">Yesterday</option>
			<option value="weekly">Weekly</option>
			<option value="monthly">Monthly</option>
			<option value="yearly">Yearly</option>
			<option value="custom">Custom</option>
		</select>

		<div id="customDateBox" class="custom-date-box">
			<input type="date" id="fromDate" class="date-input">
			<input type="date" id="toDate" class="date-input">
			<button class="apply-btn">Apply</button>
		</div>
	</div>

    <div class="cards">
        <div class="card">
            <h3>Current pH</h3>
            <p>7.12</p>
        </div>
        <div class="card">
            <h3>Today's Average</h3>
            <p>7.08</p>
        </div>
        <div class="card">
            <h3>Lowest Today</h3>
            <p>6.85</p>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
        <canvas id="phChart"></canvas>
    </div>

    <!-- Table -->
    <table>
        <tr>
            <th>Date</th>
            <th>pH Value</th>
            <th>Time</th>
        </tr>
        <tr><td>25 Nov 2025</td><td>7.12</td><td>08:22 AM</td></tr>
        <tr><td>25 Nov 2025</td><td>7.09</td><td>07:40 AM</td></tr>
        <tr><td>24 Nov 2025</td><td>7.06</td><td>06:55 AM</td></tr>
        <tr><td>24 Nov 2025</td><td>7.10</td><td>05:30 AM</td></tr>
        <tr><td>23 Nov 2025</td><td>6.98</td><td>07:12 AM</td></tr>
    </table>
</div>

<script>
const ctx = document.getElementById("phChart").getContext("2d");

new Chart(ctx, {
    type: "line",
    data: {
        labels: ["6 AM", "7 AM", "8 AM", "9 AM", "10 AM"],
        datasets: [{
            label: "pH Value",
            data: [7.01, 7.06, 7.09, 7.12, 7.10],
            borderColor: "#55ff88",
            backgroundColor: "rgba(85,255,136,0.2)",
            borderWidth: 3,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: "#99ffc2"
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { ticks: { color: "#fff" }},
            y: { ticks: { color: "#fff" }, beginAtZero: false }
        },
        plugins: {
            legend: { labels: { color: "#fff" }}
        }
    }
});
</script>

<script>
function handleFilterChange() {
    let box = document.getElementById("customDateBox");
    let value = document.getElementById("filterSelect").value;

    if (value === "custom") {
        box.style.display = "flex";
    } else {
        box.style.display = "none";
    }
}
</script>

</body>
</html>
