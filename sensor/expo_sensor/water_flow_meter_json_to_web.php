<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Water Flow Meter Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg,#1a1f2b,#222a35,#1c2838);
        color: #fff;
    }
    .container {
        display: flex;
        gap: 20px;
        padding: 20px;
    }
    .card {
        flex: 1;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 20px;
        backdrop-filter: blur(12px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    }
    .card h2 { margin-top: 0; }
    select, input[type="date"], button {
        padding: 10px;
        border-radius: 10px;
        border: none;
        font-size: 15px;
        margin-right: 10px;
        margin-top: 10px;
    }
    #customDates { display: none; margin-top: 10px; }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    table th, table td {
        padding: 10px;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        font-size: 14px;
    }.home-btn {
		position: fixed;
		top: 15px;
		right: 20px;
		padding: 10px 22px;
		background: rgba(0, 255, 255, 0.25);
		color: #ffffff;
		backdrop-filter: blur(10px);
		border: 2px solid rgba(0,255,255,0.7);
		border-radius: 14px;
		font-weight: 600;
		text-decoration: none;
		transition: 0.3s;
		box-shadow: 0 0 18px rgba(0,255,255,0.6);
		z-index: 9999;  /* ⭐ VERY IMPORTANT */
	}
	.home-btn:hover {
		background: rgba(0,255,255,0.45);
		box-shadow: 0 0 25px rgba(0,255,255,1);
		transform: scale(1.08);
	}


</style>
</head>
<body>

<a href="https://sunfra.com/farm/sensor/expo_sensor/index_page_for_display.php" class="home-btn">Home</a>

<div class="container">

    <div class="card" style="flex: 2;">
        <h2>Water Consumption Graph</h2>

        <select id="filterSelect">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
            <option value="custom">Custom</option>
        </select>

        <div id="customDates">
            <input type="date" id="fromDate">
            <input type="date" id="toDate">
            <button onclick="loadCustom()" style="background:#00d9ff;color:#000;font-weight:600;">Load</button>
        </div>

        <canvas id="waterChart" height="150"></canvas>
    </div>

	<div class="card" style="flex: 1;">
		<h2>Used Hours Summary</h2>

		<table id="summaryTable">
			<thead id="summaryHeader">
				<tr>
					<th>Hour</th>
					<th>Liters Used</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>


</div>


<script>

let chart;

function createGraph(labels, values) {
    if (chart) chart.destroy();

    const ctx = document.getElementById('waterChart').getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(0,255,255,0.9)');
    gradient.addColorStop(1, 'rgba(0,128,255,0.3)');

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
			fullTS: window.fullTS,
            datasets: [{
                label: "Liters Used",
                data: values,
                fill: true,
                backgroundColor: gradient,
                borderColor: "#00eaff",
                borderWidth: 3,
                tension: 0.4
            }]
        },
        options: {
			plugins: { 
				legend: { labels: { color: "#fff" } },
				tooltip: {
					callbacks: {
						label: function(context) {

							// full timestamp stored in hidden array
							const idx = context.dataIndex;
							const fullTimestamp = context.chart.data.fullTS[idx];

							const [date, time] = fullTimestamp.split(" ");

							return `Date: ${date} | Time: ${time} | Used: ${context.parsed.y} L`;
						}
					}
				}
			},
			scales: {
				x: { ticks: { color: "#fff" } },
				y: { ticks: { color: "#fff" } }
			}
		}
    });
}

function updateSummaryTable(hourData) {
    const tbody = document.querySelector("#summaryTable tbody");
    const header = document.getElementById("summaryHeader");
    tbody.innerHTML = "";

    const filter = document.getElementById("filterSelect").value;

    const isDateMode = ["weekly", "monthly", "yearly", "custom"].includes(filter);

    // Change table header dynamically
    if (isDateMode) {
        header.innerHTML = `
            <tr>
                <th>Date</th>
                <th>Total Liters Used</th>
            </tr>
        `;
    } else {
        header.innerHTML = `
            <tr>
                <th>Hour</th>
                <th>Liters Used</th>
            </tr>
        `;
    }

    // =============================
    // 1️⃣ TODAY / YESTERDAY -> HOURLY
    // =============================
    if (!isDateMode) {
        hourData.forEach(row => {
            const time = row.timestamp.split(" ")[1].substring(0, 5);
            const liters = parseFloat(row.liters_used);

            if (liters > 0) {
                tbody.innerHTML += `
                    <tr>
                        <td>${time}</td>
                        <td>${liters.toFixed(2)} L</td>
                    </tr>
                `;
            }
        });
        return;
    }

    // =============================
    // 2️⃣ WEEK / MONTH / YEAR / CUSTOM -> DATEwise
    // =============================
    const dailyTotals = {};

    hourData.forEach(row => {
        const [date] = row.timestamp.split(" ");
        const liters = parseFloat(row.liters_used);

        if (liters > 0) {
            if (!dailyTotals[date]) dailyTotals[date] = 0;
            dailyTotals[date] += liters;
        }
    });

    Object.keys(dailyTotals).forEach(date => {
        tbody.innerHTML += `
            <tr>
                <td>${date}</td>
                <td>${dailyTotals[date].toFixed(2)} L</td>
            </tr>
        `;
    });
}


function getDateRange(filter) {
    const today = new Date();
    let from, to;

    switch(filter) {
        case "today":     from = to = today; break;
        case "yesterday": let y = new Date(); y.setDate(today.getDate() - 1); from = to = y; break;
        case "weekly":    let w = new Date(); w.setDate(today.getDate() - 7); from = w; to = today; break;
        case "monthly":   let m = new Date(); m.setMonth(today.getMonth() - 1); from = m; to = today; break;
        case "yearly":    let yr = new Date(); yr.setFullYear(today.getFullYear() - 1); from = yr; to = today; break;
    }

    return {
        from: from.toISOString().split("T")[0],
        to: to.toISOString().split("T")[0]
    };
}

async function fetchData(from, to) {
    const url = `https://sunfra.com/farm/sensor/expo_sensor/water_flow_meter_day_json.php?from=${from}&to=${to}`;

    const res = await fetch(url);
    const json = await res.json();

    if (json.status === "success") {

        const filter = document.getElementById("filterSelect").value;

        let labels = [];
        let values = [];

        if (["today", "yesterday"].includes(filter)) {
            labels = json.data.map(d => d.timestamp.split(" ")[1].substring(0, 5));
        } else {
            const dailyMap = {};

            json.data.forEach(row => {
                const [date, time] = row.timestamp.split(" ");
                const liters = parseFloat(row.liters_used);

                if (!dailyMap[date]) dailyMap[date] = 0;
                dailyMap[date] += liters;
            });

            labels = Object.keys(dailyMap);        
            values = Object.values(dailyMap);      

            createGraph(labels, values);
            updateSummaryTable(json.data);
            return;
        }

        values = json.data.map(d => parseFloat(d.liters_used));
		window.fullTS = json.data.map(d => d.timestamp);
        createGraph(labels, values);

        updateSummaryTable(json.data);
    }
}


document.getElementById("filterSelect").addEventListener("change", function() {
    const filter = this.value;

    if (filter === "custom") {
        document.getElementById("customDates").style.display = "block";
        return;
    }

    document.getElementById("customDates").style.display = "none";

    const { from, to } = getDateRange(filter);
    fetchData(from, to);
});

function loadCustom() {
    const from = document.getElementById("fromDate").value;
    const to = document.getElementById("toDate").value;
    if (from && to) fetchData(from, to);
}

window.onload = () => {
    const { from, to } = getDateRange("today");
    fetchData(from, to);
};
</script>

</body>
</html>
