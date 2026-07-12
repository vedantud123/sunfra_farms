<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Water Data</title>

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
        color: #55ff88;
        font-size: 30px;
    }

    /* CARDS */
    .cards {
        display: flex;
        gap: 25px;
        margin-bottom: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .card {
        width: 190px;
        background: linear-gradient(135deg, #002533, #00131a);
        padding: 20px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0px 0px 15px #0099ff;
    }

    .card h3 {
        margin: 0;
        color: #a0e7ff;
        font-size: 18px;
    }

    .card p {
        margin: 10px 0 0;
        font-size: 28px;
        font-weight: bold;
        color: #d0f4ff;
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
        background: #06171c;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 15px #0099ff;
        font-size: 14px;
    }
    th, td {
        padding: 10px;
        text-align: center;
    }
    th {
        background: #004766;
        color: #fff;
    }
    tr:nth-child(even) {
        background: #02222b;
    }

    tfoot tr td {
        background: #021218;
        color: #bfeeff;
        font-weight: 600;
    }

    /* FILTER DROPDOWN */
    .filter-box {
        position: absolute;
        top: 20px;
        right: 40px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-select {
        padding: 8px 12px;
        border-radius: 8px;
        background: #1a1a1a;
        color: #fff;
        border: 1px solid #55ff88;
        font-size: 14px;
        cursor: pointer;
    }

    .custom-dates {
        display: none;
        gap: 10px;
    }

    .custom-input {
        padding: 6px 10px;
        background: #1a1a1a;
        border: 1px solid #55ff88;
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
    }

    .apply-btn {
        padding: 7px 14px;
        background: #55ff88;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        color: #000;
        font-weight: 600;
    }

    .loading {
        color: #a0e7ff;
        margin-left: 12px;
        font-weight: 600;
    }
</style>

</head>
<body>

<div class="sidebar">
    <h2>Dashboard</h2>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/dosing_pump_web_page.php">🏠 Home</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/acid_used_web_page.php">🧪 Acid Used</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/chlorine_used_web_page.php">🧼 Chlorine Used</a>
    <a href="https://sunfra.com/farm/iot_part/dosing_pump_ph_web_page.php">📊 pH Data</a>
    <a href="https://sunfra.com/farm/iot_part/dosing_pump_orp_web_page.php">⚡ ORP Data</a>
    <a href="https://sunfra.com/farm/iot_part/dosing_pump_water_web_page.php">💧 Water Data</a>
</div>

<div class="content">
    <h1>Water Data Report</h1>

    <div class="filter-box">
        <select id="filterSelect" class="filter-select">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
            <option value="custom">Custom</option>
        </select>

        <div id="customDateBox" class="custom-dates">
            <input type="date" id="fromDate" class="custom-input">
            <input type="date" id="toDate" class="custom-input">
            <button onclick="applyCustomDate()" class="apply-btn">Apply</button>
        </div>

        <div id="loadingIndicator" class="loading" style="display:none">Loading...</div>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Last Updated</h3>
            <p id="cardCurrent">-- L</p>
        </div>
        <div class="card">
            <h3>Average</h3>
            <p id="cardAvg">-- L</p>
        </div>
        <div class="card">
            <h3>Lowest</h3>
            <p id="cardLow">-- L</p>
        </div>
        <!-- NEW CARD: Total Water Used (today / current range) -->
        <div class="card">
            <h3>Total Water</h3>
            <p id="cardTotal">-- L</p>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="waterChart"></canvas>
    </div>

    <table id="dataTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>Water Value (L)</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <!-- JS will fill this -->
        </tbody>
        <tfoot id="tableFooter">
            <tr>
                <td>Summary</td>
                <td>-</td>
                <td>-</td>
            </tr>
        </tfoot>
    </table>
</div>

<script>
const API_BASE = "https://sunfra.com/farm/iot_part/dosing_pump_water_level_json.php";
let waterChart = null;

function showLoading(show) {
    document.getElementById("loadingIndicator").style.display = show ? "inline-block" : "none";
}

function parseTS(ts) {
    return new Date(ts.replace(" ", "T"));
}

function fmtDate(d) {
    return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
}
function fmtTime(d) {
    return d.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
}

function normalizeData(raw) {
    return raw
        .filter(r => r && r.water_value != null && r.timestamp)
        .map(r => ({
            water: Number(r.water_value),
            ts: parseTS(r.timestamp)
        }))
        .sort((a,b) => b.ts - a.ts);
}

/* ===============================
   ✅ DAILY TOTAL CALCULATION
   =============================== */
function calculateDailyStats(rows) {
    const dailyTotals = {};

    rows.forEach(r => {
        const day = r.ts.toISOString().slice(0,10);
        dailyTotals[day] = (dailyTotals[day] || 0) + r.water;
    });

    const dailyValues = Object.values(dailyTotals);

    return {
        total: dailyValues.reduce((a,b)=>a+b,0),
        avg: dailyValues.reduce((a,b)=>a+b,0) / dailyValues.length,
        lowest: Math.min(...dailyValues)
    };
}

async function loadWaterData(filter, from=null, to=null) {
    showLoading(true);
    try {
        let url = API_BASE + "?filter=" + filter;
        if (filter === "custom") {
            url += "&from=" + from + "&to=" + to;
        }

        const res = await fetch(url);
        const json = await res.json();
        const rows = normalizeData(json.data || []);

        if (!rows.length) {
            renderEmpty();
            return;
        }

        if (filter === "today" || filter === "yesterday") {
            renderToday(rows);
        } else if (filter === "weekly") {
            renderWeekly(rows);
        } else if (filter === "monthly") {
            renderMonthly(rows);
        } else if (filter === "yearly") {
            renderYearly(rows);
        } else {
            renderCustom(rows);
        }
    } finally {
        showLoading(false);
    }
}

/* ===============================
   TODAY / YESTERDAY
   =============================== */
function renderToday(rows) {
    const stats = calculateDailyStats(rows);
    const current = rows[0].water;

    document.getElementById("cardCurrent").innerText = current.toFixed(2) + " L";
    document.getElementById("cardAvg").innerText = stats.avg.toFixed(2) + " L";
    document.getElementById("cardLow").innerText = stats.lowest.toFixed(2) + " L";
    document.getElementById("cardTotal").innerText = stats.total.toFixed(2) + " L";

    const slice = rows.slice(0,5).reverse();
    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = "";

    slice.forEach(r => {
        tbody.innerHTML += `
            <tr>
                <td>${fmtDate(r.ts)}</td>
                <td>${r.water.toFixed(2)}</td>
                <td>${fmtTime(r.ts)}</td>
            </tr>`;
    });

    updateChart(slice.map(r => fmtTime(r.ts)), slice.map(r => r.water));
}

/* ===============================
   WEEKLY
   =============================== */
function renderWeekly(rows) {
    renderRange(rows, r => {
        const d = r.ts.getDate();
        return "Week " + Math.ceil(d / 7);
    });
}

/* ===============================
   MONTHLY
   =============================== */
function renderMonthly(rows) {
    renderRange(rows, r =>
        r.ts.toLocaleString('default', { month:'short', year:'numeric' })
    );
}

/* ===============================
   YEARLY
   =============================== */
function renderYearly(rows) {
    renderRange(rows, r => r.ts.getFullYear().toString());
}

/* ===============================
   CUSTOM
   =============================== */
function renderCustom(rows) {
    renderRange(rows, r => fmtDate(r.ts));
}

/* ===============================
   COMMON RANGE RENDER
   =============================== */
function renderRange(rows, keyFn) {
    const groups = {};

    rows.forEach(r => {
        const k = keyFn(r);
        groups[k] = (groups[k] || 0) + r.water;
    });

    const labels = Object.keys(groups);
    const values = Object.values(groups);

    const total = values.reduce((a,b)=>a+b,0);
    const avg = total / values.length;
    const lowest = Math.min(...values);

    document.getElementById("cardCurrent").innerText = rows[0].water.toFixed(2) + " L";
    document.getElementById("cardAvg").innerText = avg.toFixed(2) + " L";
    document.getElementById("cardLow").innerText = lowest.toFixed(2) + " L";
    document.getElementById("cardTotal").innerText = total.toFixed(2) + " L";

    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = "";

    labels.forEach((l,i) => {
        tbody.innerHTML += `
            <tr>
                <td>${l}</td>
                <td>${values[i].toFixed(2)}</td>
                <td>-</td>
            </tr>`;
    });

    updateChart(labels, values);
}

function renderEmpty() {
    ["cardCurrent","cardAvg","cardLow","cardTotal"]
        .forEach(id => document.getElementById(id).innerText="-- L");
    document.querySelector("#dataTable tbody").innerHTML =
        `<tr><td colspan="3">No data available</td></tr>`;
    updateChart([],[]);
}

function updateChart(labels, values) {
    const ctx = document.getElementById("waterChart").getContext("2d");
    if (waterChart) waterChart.destroy();

    waterChart = new Chart(ctx, {
        type: "line",
        data: {
            labels,
            datasets: [{
                label: "Water Value (L)",
                data: values,
                borderColor: "#55ffdd",
                backgroundColor: "rgba(85,255,221,0.25)",
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: "#fff" } } },
            scales: {
                x: { ticks: { color: "#fff" } },
                y: { ticks: { color: "#fff" } }
            }
        }
    });
}

document.getElementById("filterSelect").addEventListener("change", function () {
    const v = this.value;
    document.getElementById("customDateBox").style.display = v==="custom"?"flex":"none";
    if (v!=="custom") loadWaterData(v);
});

function applyCustomDate() {
    loadWaterData("custom",
        document.getElementById("fromDate").value,
        document.getElementById("toDate").value
    );
}

loadWaterData("today");
</script>


</body>
</html>
