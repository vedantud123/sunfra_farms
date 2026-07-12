<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>ORP Data</title>

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

    tfoot tr td {
        background: #220a04;
        color: #ffd9c6;
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
        border: 1px solid #ff7b39;
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
        border: 1px solid #ff7b39;
        border-radius: 8px;
        color: #fff;
        font-size: 14px;
    }

    .apply-btn {
        padding: 7px 14px;
        background: #ff7b39;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        color: #000;
        font-weight: 600;
    }

    .loading {
        color: #ffba8c;
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
    <h1>ORP Data Report</h1>

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
            <h3>Last Updated ORP</h3>
            <p id="cardCurrent">-- mV</p>
        </div>
        <div class="card">
            <h3>Average</h3>
            <p id="cardAvg">-- mV</p>
        </div>
        <div class="card">
            <h3>Lowest</h3>
            <p id="cardLow">-- mV</p>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="orpChart"></canvas>
    </div>

    <table id="dataTable">
        <thead>
            <tr>
                <th>Date</th>
                <th>ORP Value (mV)</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>25 Nov 2025</td><td>552</td><td>08:25 AM</td></tr>
            <tr><td>25 Nov 2025</td><td>545</td><td>07:40 AM</td></tr>
            <tr><td>24 Nov 2025</td><td>538</td><td>06:55 AM</td></tr>
            <tr><td>23 Nov 2025</td><td>525</td><td>07:10 AM</td></tr>
            <tr><td>22 Nov 2025</td><td>518</td><td>06:50 AM</td></tr>
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
const API_BASE = "https://sunfra.com/farm/iot_part/dosing_pump_orp_json.php";
let orpChart = null;

function showLoading(show) {
    document.getElementById("loadingIndicator").style.display = show ? "inline-block" : "none";
}

function parseTS(ts) {
    const s = ts.replace(" ", "T");
    return new Date(s);
}

function fmtDate(d) {
    return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
}
function fmtTime(d) {
    return d.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
}

function normalizeData(raw) {
    return raw
        .filter(r => r && r.orp_value != null && r.timestamp)
        .map(r => ({
            orp: Number(r.orp_value),
            ts: parseTS(r.timestamp),
            rawTimestamp: r.timestamp
        }))
        .sort((a,b) => b.ts - a.ts);
}

function avg(arr) {
    if (!arr || arr.length === 0) return 0;
    return arr.reduce((s,v) => s+v, 0) / arr.length;
}

async function loadORPData(filter, from=null, to=null) {
    showLoading(true);
    try {
        let url = API_BASE + "?filter=" + encodeURIComponent(filter);
        if (filter === "custom") {
            url += "&from=" + encodeURIComponent(from) + "&to=" + encodeURIComponent(to);
        }

        const res = await fetch(url);
        if (!res.ok) {
            throw new Error("Network response not ok: " + res.status);
        }
        const json = await res.json();

        const raw = json.data || [];
        const rows = normalizeData(raw);

        if (!rows.length) {
            renderEmpty();
            showLoading(false);
            return;
        }

        // Determine behavior per filter
        if (filter === "today" || filter === "yesterday") {
            renderLatestRows(rows, 5);
        } else if (filter === "weekly") {
            renderGroupByDay(rows);
        } else if (filter === "monthly") {
            renderGroupByWeek(rows);
        } else if (filter === "yearly") {
            renderGroupByMonth(rows);
        } else if (filter === "custom") {
            renderGroupByDay(rows);
        } else {
            // default fallback
            renderLatestRows(rows, 5);
        }

    } catch (err) {
        console.error("Error fetching ORP:", err);
        alert("Failed to load ORP data. See console.");
    } finally {
        showLoading(false);
    }
}

function renderEmpty() {
    document.getElementById("cardCurrent").innerText = "-- mV";
    document.getElementById("cardAvg").innerText = "-- mV";
    document.getElementById("cardLow").innerText = "-- mV";
    updateChart([], []);
    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = `<tr><td colspan="3">No data available</td></tr>`;
    document.getElementById("tableFooter").innerHTML = `<tr><td>Summary</td><td>-</td><td>-</td></tr>`;
}

function renderLatestRows(rows, N) {
    const slice = rows.slice(0, N);

    const current = slice[0].orp;
    const values = slice.map(r => r.orp);
    const average = avg(values);
    const lowest = Math.min(...values);

    document.getElementById("cardCurrent").innerText = current + " mV";
    document.getElementById("cardAvg").innerText = average.toFixed(2) + " mV";
    document.getElementById("cardLow").innerText = lowest + " mV";

    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = "";
    slice.forEach(r => {
        const d = fmtDate(r.ts);
        const t = fmtTime(r.ts);
        tbody.innerHTML += `<tr><td>${d}</td><td>${r.orp}</td><td>${t}</td></tr>`;
    });

    document.getElementById("tableFooter").innerHTML = `<tr><td>Summary</td><td>Avg: ${average.toFixed(2)} mV (Lowest: ${lowest} mV)</td><td>-</td></tr>`;

    const chartData = slice.slice().reverse();
    updateChart(chartData.map(r => fmtTime(r.ts)), chartData.map(r => r.orp));
}

function renderGroupByDay(rows) {
    const groups = {};
    rows.forEach(r => {
        const key = fmtDate(r.ts); // date string
        if (!groups[key]) groups[key] = [];
        groups[key].push(r.orp);
    });

    const sortedDates = Object.keys(groups)
        .map(dstr => {
            const parts = dstr.split(' ');
            return { dstr, dateObj: new Date(groups[dstr] && groups[dstr].length ? rows.find(r=>fmtDate(r.ts)===dstr).ts : new Date()) };
        })
        .sort((a,b) => a.dateObj - b.dateObj)
        .map(x => x.dstr);

    const tableRows = sortedDates.map(d => {
        const values = groups[d];
        return { date: d, orp: avg(values).toFixed(2), time: "-" , rawVals: values };
    });

    const allValues = rows.map(r => r.orp);
    const current = rows[0].orp;
    const average = avg(allValues);
    const lowest = Math.min(...allValues);

    document.getElementById("cardCurrent").innerText = current + " mV";
    document.getElementById("cardAvg").innerText = average.toFixed(2) + " mV";
    document.getElementById("cardLow").innerText = lowest + " mV";

    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = "";
    tableRows.forEach(r => {
        tbody.innerHTML += `<tr><td>${r.date}</td><td>${r.orp}</td><td>${r.time}</td></tr>`;
    });

    document.getElementById("tableFooter").innerHTML = `<tr><td>Summary</td><td>Avg: ${average.toFixed(2)} mV (Lowest: ${lowest} mV)</td><td>-</td></tr>`;

    updateChart(tableRows.map(r => r.date), tableRows.map(r => Number(r.orp)));
}

function renderGroupByWeek(rows) {
    const weeks = {};

    rows.forEach(r => {
        const dt = r.ts;
        const year = dt.getFullYear();
        const month = dt.getMonth() + 1; // 1..12
        const day = dt.getDate();
        const weekNo = Math.ceil(day / 7); // week 1..5
        const key = `${year}-${String(month).padStart(2,'0')}-W${weekNo}`;
        if (!weeks[key]) weeks[key] = { label: `Week ${weekNo}`, vals: [] };
        weeks[key].vals.push(r.orp);
    });

    const sortedKeys = Object.keys(weeks).sort((a,b) => {
        const pa = a.split('-W'); // ["YYYY-MM", "N"]
        const pb = b.split('-W');
        const [ya,ma] = pa[0].split('-').map(Number);
        const [yb,mb] = pb[0].split('-').map(Number);
        const wa = Number(pa[1].replace('W',''));
        const wb = Number(pb[1].replace('W',''));
        if (ya !== yb) return ya - yb;
        if (ma !== mb) return ma - mb;
        return wa - wb;
    });

    const tableRows = sortedKeys.map(k => ({
        date: weeks[k].label,
        orp: avg(weeks[k].vals).toFixed(2),
        time: "-"
    }));

    const allValues = rows.map(r => r.orp);
    const current = rows[0].orp;
    const average = avg(allValues);
    const lowest = Math.min(...allValues);

    document.getElementById("cardCurrent").innerText = current + " mV";
    document.getElementById("cardAvg").innerText = average.toFixed(2) + " mV";
    document.getElementById("cardLow").innerText = lowest + " mV";

    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = "";
    tableRows.forEach(r => {
        tbody.innerHTML += `<tr><td>${r.date}</td><td>${r.orp}</td><td>${r.time}</td></tr>`;
    });

    document.getElementById("tableFooter").innerHTML = `<tr><td>Summary</td><td>Avg: ${average.toFixed(2)} mV (Lowest: ${lowest} mV)</td><td>-</td></tr>`;

    updateChart(tableRows.map(r => r.date), tableRows.map(r => Number(r.orp)));
}

function renderGroupByMonth(rows) {
    const months = {};
    rows.forEach(r => {
        const dt = r.ts;
        const key = dt.toLocaleString('default', { month: 'short', year: 'numeric' }); // e.g. "Dec 2025"
        if (!months[key]) months[key] = [];
        months[key].push(r.orp);
    });

    const monthKeys = Object.keys(months).sort((a,b) => {
        const da = new Date(a);
        const db = new Date(b);
        return da - db;
    });

    const tableRows = monthKeys.map(k => ({
        date: k,
        orp: avg(months[k]).toFixed(2),
        time: "-"
    }));

    const allValues = rows.map(r => r.orp);
    const current = rows[0].orp;
    const average = avg(allValues);
    const lowest = Math.min(...allValues);

    document.getElementById("cardCurrent").innerText = current + " mV";
    document.getElementById("cardAvg").innerText = average.toFixed(2) + " mV";
    document.getElementById("cardLow").innerText = lowest + " mV";

    const tbody = document.querySelector("#dataTable tbody");
    tbody.innerHTML = "";
    tableRows.forEach(r => {
        tbody.innerHTML += `<tr><td>${r.date}</td><td>${r.orp}</td><td>${r.time}</td></tr>`;
    });

    document.getElementById("tableFooter").innerHTML = `<tr><td>Summary</td><td>Avg: ${average.toFixed(2)} mV (Lowest: ${lowest} mV)</td><td>-</td></tr>`;

    updateChart(tableRows.map(r => r.date), tableRows.map(r => Number(r.orp)));
}

function updateChart(labels, values) {
    const ctx = document.getElementById("orpChart").getContext("2d");

    if (orpChart) {
        orpChart.destroy();
    }

    orpChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: "ORP (mV)",
                data: values,
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
                x: { ticks: { color: "#fff" } },
                y: { ticks: { color: "#fff" } }
            }
        }
    });
}

document.getElementById("filterSelect").addEventListener("change", function () {
    const customBox = document.getElementById("customDateBox");
    const val = this.value;
    if (val === "custom") {
        customBox.style.display = "flex";
    } else {
        customBox.style.display = "none";
        // load
        loadORPData(val);
    }
});

function applyCustomDate() {
    const from = document.getElementById("fromDate").value;
    const to = document.getElementById("toDate").value;
    if (!from || !to) {
        alert("Please select both dates.");
        return;
    }
    loadORPData("custom", from, to);
}

loadORPData("today");
</script>

</body>
</html>
