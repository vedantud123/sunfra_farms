<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>pH Data</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* =======================
   GENERAL RESET & BODY
======================= */
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: #0b0b0b;
    color: #fff;
    display: flex;
}

/* =======================
   SIDEBAR
======================= */
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

/* =======================
   MAIN CONTENT
======================= */
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

/* =======================
   CARDS
======================= */
.cards {
    display: flex;
    gap: 25px;
    margin-bottom: 15px;
    flex-wrap: wrap;
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

/* =======================
   FILTER & CUSTOM DATE
======================= */
.filter-section {
    width: 100%;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
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
    display: none; /* hidden initially */
    gap: 10px;
    align-items: center;
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

/* =======================
   CHART
======================= */
.chart-container {
    width: 90%;
    height: 320px;
    margin-top: 10px;
}

/* =======================
   TABLE
======================= */
#dataTable {
    width: 90%;
    margin-top: 20px;
    border-collapse: collapse;
    background: #0f1f17;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 10px #006633;
    font-size: 14px;
}

#dataTable th, #dataTable td {
    padding: 10px;
    text-align: center;
}

#dataTable th {
    background: #004d26;
    color: #fff;
}

#dataTable tr:nth-child(even) {
    background: #0d261a;
}

/* =======================
   SUMMARY BOX
======================= */
.summary {
    width: 90%;
    margin-top: 12px;
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    align-items: center;
}

.summary .box {
    background: rgba(85,255,136,0.08);
    border: 1px solid rgba(85,255,136,0.15);
    padding: 10px 14px;
    border-radius: 8px;
    color: #dfffe6;
    font-weight: 600;
}

/* =======================
   RESPONSIVE
======================= */
@media (max-width: 900px) {
    .cards { flex-direction: column; align-items: stretch; }
    .filter-section { justify-content: center; }
    .content { padding: 12px; }
}

/* =======================
   FILTER CONTAINER (TOP)
======================= */
#filterContainer {
    display: flex;
    align-items: center;
    width: 100%;
    margin-bottom: 20px;
    gap: 12px;
    font-family: 'Poppins', sans-serif;
}

/* This will keep the filter (Today dropdown + custom dates) in the middle */
#filterLeft {
    flex: 1;
    display: flex;
    justify-content: center;   /* center the Today dropdown */
    align-items: center;
    gap: 12px;
}


/* Dropdown filter */
#filterSelect {
    padding: 8px 14px;
    border-radius: 6px;
    border: 1px solid #055d2d;
    background-color: #0f1f17;
    color: #55ff88;
    font-size: 15px;
    cursor: pointer;
    outline: none;
    transition: all 0.3s ease;
}

#filterSelect:hover,
#filterSelect:focus {
    background-color: #55ff88;
    color: #000;
    border-color: #33cc66;
}

/* Custom date box (hidden initially) */
#customDateBox {
    display: none; /* will show only if 'Custom' is selected */
    gap: 10px;
    align-items: center;
}

/* Date input fields */
#customDateBox input[type="date"] {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #055d2d;
    background-color: #0f1f17;
    color: #55ff88;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}

#customDateBox input[type="date"]:focus {
    border-color: #33cc66;
    background-color: #001a0c;
    color: #55ff88;
}

/* Apply button */
#applyBtn {
    padding: 8px 18px;
    background-color: #55ff88;
    color: #000;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

#applyBtn:hover {
    background-color: #33cc66;
    transform: translateY(-2px);
}

.range-btn {
    padding: 8px 14px;
    border-radius: 6px;
    border: 1px solid #055d2d;
    background-color: #0f1f17;   /* SAME as Today */
    color: #55ff88;              /* SAME text color */
    font-size: 15px;
    cursor: pointer;
    outline: none;
    transition: all 0.3s ease;
}

.range-btn:hover,
.range-btn:focus {
    background-color: #55ff88;  /* SAME hover */
    color: #000;
    border-color: #33cc66;
}


/* Responsive for smaller screens */
@media (max-width: 600px) {
    #filterContainer {
        flex-direction: column;
        align-items: stretch;
    }

    #filterLeft {
        flex-wrap: wrap;
    }

    #customDateBox {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    #applyBtn {
        width: 100%;
    }
}

/* =======================
   MODAL (pH RANGE)
======================= */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    display: none;              /* hidden by default */
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal {
    background: #111;
    border-radius: 14px;
    padding: 20px 22px;
    width: 320px;
    max-width: 90vw;
    box-shadow: 0 0 20px rgba(0,0,0,0.7);
    border: 1px solid #55ff88;
}
.modal h2 {
    margin: 0 0 14px;
    font-size: 20px;
    color: #55ff88;
}

/* Form elements */
.form-row {
    margin-bottom: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.form-row label {
    font-size: 13px;
    color: #c2ffd8;
}
.form-row input {
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #055d2d;
    background: #0f1f17;
    color: #55ff88;
    outline: none;
    font-size: 14px;
}
.form-row input:focus {
    border-color: #33cc66;
}

/* Buttons in modal */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 6px;
}

.btn-primary, .btn-secondary {
    padding: 7px 14px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
}

.btn-primary {
    background: #55ff88;
    color: #000;
}
.btn-primary:hover {
    background: #33cc66;
}

.btn-secondary {
    background: #333;
    color: #fff;
}
.btn-secondary:hover {
    background: #555;
}

/* Status message */
.status-msg {
    margin-top: 8px;
    font-size: 12px;
    min-height: 16px;
}
.status-msg.ok {
    color: #55ff88;
}
.status-msg.error {
    color: #ff6b6b;
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
    <h1>pH Data Report</h1>

    <!-- TOP FILTER + RANGE BUTTON -->
    <div id="filterContainer">
        <div id="filterLeft">
            <select id="filterSelect">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="custom">Custom</option>
            </select>

            <div id="customDateBox">
                <input type="date" id="fromDate">
                <input type="date" id="toDate">
                <button id="applyBtn">Apply</button>
            </div>
        </div>

        <button id="phRangeBtn" class="range-btn">pH Range</button>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Last Updated pH</h3>
            <p id="cardCurrent">—</p>
        </div>
        <div class="card">
            <h3>Average</h3>
            <p id="cardAvg">—</p>
        </div>
        <div class="card">
            <h3>Lowest</h3>
            <p id="cardLow">—</p>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="phChart"></canvas>
    </div>

    <table id="dataTable">
        <thead>
            <tr>
                <th>Label</th>
                <th>pH Value</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <div class="summary" id="summaryBoxes">
        <div class="box" id="summaryAvg">Avg: —</div>
        <div class="box" id="summaryLow">Lowest: —</div>
        <div class="box" id="summaryCount">Count: —</div>
    </div>
</div>

<!-- pH RANGE MODAL -->
<div id="phRangeModalOverlay" class="modal-overlay">
    <div class="modal">
        <h2>pH Range Settings</h2>
        <form id="phRangeForm">
            <input type="hidden" id="rangeId">

            <div class="form-row">
                <label for="firstRange">First Range (Min pH)</label>
                <input type="number" step="0.01" id="firstRange" required>
            </div>

            <div class="form-row">
                <label for="secondRange">Second Range (Max pH)</label>
                <input type="number" step="0.01" id="secondRange" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save</button>
                <button type="button" id="phRangeCloseBtn" class="btn-secondary">Close</button>
            </div>

            <p id="phRangeStatus" class="status-msg"></p>
        </form>
    </div>
</div>

<script>
const apiBase        = 'https://sunfra.com/farm/iot_part/dosing_pump_ph_json.php';
const phRangeApi     = 'https://sunfra.com/farm/iot_part/dosing_pump_ph_range_json.php';
const phRangeSaveApi = 'https://sunfra.com/farm/iot_part/dosing_pump_ph_range_save.php';

const filterSelect   = document.getElementById('filterSelect');
const customDateBox  = document.getElementById('customDateBox');
const fromInput      = document.getElementById('fromDate');
const toInput        = document.getElementById('toDate');
const applyBtn       = document.getElementById('applyBtn');

filterSelect.addEventListener('change', handleFilterChange);

const cardCurrent = document.getElementById('cardCurrent');
const cardAvg     = document.getElementById('cardAvg');
const cardLow     = document.getElementById('cardLow');

const tableBody   = document.querySelector('#dataTable tbody');
const summaryAvg  = document.getElementById('summaryAvg');
const summaryLow  = document.getElementById('summaryLow');
const summaryCount= document.getElementById('summaryCount');

/* pH RANGE MODAL ELEMENTS */
const phRangeBtn       = document.getElementById('phRangeBtn');
const phRangeModal     = document.getElementById('phRangeModalOverlay');
const phRangeForm      = document.getElementById('phRangeForm');
const phRangeCloseBtn  = document.getElementById('phRangeCloseBtn');
const rangeIdInput     = document.getElementById('rangeId');
const firstRangeInput  = document.getElementById('firstRange');
const secondRangeInput = document.getElementById('secondRange');
const phRangeStatus    = document.getElementById('phRangeStatus');

let phChart = null;

/* INITIAL LOAD */
handleFilterChange();

/* ------------- FILTER HANDLING ------------- */
function handleFilterChange() {
    const v = filterSelect.value;
    if (v === 'custom') {
        customDateBox.style.display = 'flex';
    } else {
        customDateBox.style.display = 'none';
        loadAndRender(v);
    }
}

applyBtn.addEventListener('click', () => {
    const from = fromInput.value;
    const to   = toInput.value;
    if (!from || !to) { alert('Select both from and to dates'); return; }
    loadAndRender('custom', from, to);
});

/* ------------- FETCH DATA ------------- */
async function fetchData(filter, from=null, to=null) {
    try {
        let url = `${apiBase}?filter=${encodeURIComponent(filter)}`;
        if (filter === 'custom' && from && to) {
            url += `&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
        }
        const res = await fetch(url);
        if (!res.ok) throw new Error('Network response not ok');
        const json = await res.json();
        if (json.status && json.status === 'error') throw new Error(json.message || 'API returned error');
        const data = json.data || json;
        return (data || []).map(r => {
            return {
                id:  r.id,
                ph:  parseFloat(String(r.ph_value).replace(',', '.')) || null,
                ts:  r.timestamp ? new Date(r.timestamp.replace(' ', 'T')) : null,
                raw: r
            };
        }).filter(x => x.ph !== null && x.ts !== null)
          .sort((a,b) => a.ts - b.ts); // ascending by time
    } catch (err) {
        console.error('Fetch error', err);
        alert('Error fetching data: ' + err.message);
        return [];
    }
}

/* ------------- HELPERS ------------- */
function average(arr) {
    if (!arr || arr.length===0) return null;
    const s = arr.reduce((acc, v) => acc + v, 0);
    return s / arr.length;
}

function formatPH(v) {
    if (v === null || v === undefined) return '—';
    return parseFloat(v).toFixed(2);
}

function formatDate(d) {
    const opts = { day: '2-digit', month: 'short', year: 'numeric' };
    return d.toLocaleDateString('en-GB', opts);
}
function formatTime(d) {
    return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function dayKey(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${dd}`;
}

function weekKey(d) {
    const firstDay = new Date(d.getFullYear(), d.getMonth(), 1);
    const dayOfMonth = d.getDate();
    const weekNo = Math.ceil((dayOfMonth + ((firstDay.getDay()+6)%7)) / 7);
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    return `${y}-${m}-W${weekNo}`;
}

function monthKey(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    return `${y}-${m}`;
}

/* ------------- MAIN LOAD & RENDER ------------- */
async function loadAndRender(filter, from=null, to=null) {
    cardCurrent.textContent = 'Loading...';
    cardAvg.textContent     = 'Loading...';
    cardLow.textContent     = 'Loading...';
    tableBody.innerHTML     = `<tr><td colspan="4" style="padding:20px">Loading...</td></tr>`;
    updateChart([], []);

    const data = await fetchData(filter, from, to); // ascending order

    if (!data || data.length === 0) {
        cardCurrent.textContent = '—';
        cardAvg.textContent     = '—';
        cardLow.textContent     = '—';
        tableBody.innerHTML     = `<tr><td colspan="4" style="padding:20px">No data</td></tr>`;
        updateSummary(null, null, 0);
        return;
    }

    if (filter === 'today' || filter === 'yesterday') {
        const lastRows = data.slice(-5).reverse(); // newest first
        renderTableRawRows(lastRows);
        const phs  = data.map(d=>d.ph);
        const avg  = average(phs);
        const low  = Math.min(...phs);
        const last = lastRows.length>0 ? lastRows[0].ph : phs[phs.length-1];
        cardCurrent.textContent = formatPH(last);
        cardAvg.textContent     = formatPH(avg);
        cardLow.textContent     = formatPH(low);
        const chartData = data.slice(-20);
        updateChart(chartData.map(d=>formatTime(d.ts)), chartData.map(d=>d.ph));
        updateSummary(avg, low, data.length);

    } else if (filter === 'weekly') {
        const groups = {};
        data.forEach(d => {
            const k = dayKey(d.ts);
            if (!groups[k]) groups[k] = [];
            groups[k].push(d.ph);
        });
        const labels = [];
        const values = [];
        for (let i=6;i>=0;i--) {
            const dt = new Date();
            dt.setDate(dt.getDate() - i);
            const k = dayKey(dt);
            const dayArr = groups[k] || [];
            const avg = dayArr.length ? average(dayArr) : null;
            labels.push(formatDate(dt));
            values.push(avg);
        }
        renderTableAggregated(labels, values, 'Day');
        const valNums = values.filter(v=>v!==null);
        const avgAll  = valNums.length ? average(valNums) : null;
        const lowAll  = valNums.length ? Math.min(...valNums) : null;
        const lastVal = values[values.length-1] || null;
        cardCurrent.textContent = lastVal ? formatPH(lastVal) : '—';
        cardAvg.textContent     = avgAll ? formatPH(avgAll) : '—';
        cardLow.textContent     = lowAll ? formatPH(lowAll) : '—';
        updateChart(labels, values.map(v=>v===null?null:parseFloat(v.toFixed(2))));
        updateSummary(avgAll, lowAll, data.length);

    } else if (filter === 'monthly') {
        const groups = {};
        data.forEach(d => {
            const k = weekKey(d.ts);
            if (!groups[k]) groups[k] = [];
            groups[k].push(d.ph);
        });
        const weekKeys = Object.keys(groups).sort();
        const labels = [];
        const values = [];
        weekKeys.forEach(k => {
            labels.push(k); // e.g. 2025-11-W1
            const arr = groups[k];
            values.push(arr.length ? average(arr) : null);
        });
        if (labels.length === 0) {
            const dayGroups = {};
            data.forEach(d => {
                const k = dayKey(d.ts);
                if (!dayGroups[k]) dayGroups[k] = [];
                dayGroups[k].push(d.ph);
            });
            const dayKeys = Object.keys(dayGroups).sort();
            dayKeys.forEach(k => {
                labels.push(k);
                values.push(average(dayGroups[k]));
            });
        }

        renderTableAggregated(labels, values, 'Week');
        const valNums = values.filter(v=>v!==null);
        const avgAll  = valNums.length ? average(valNums) : null;
        const lowAll  = valNums.length ? Math.min(...valNums) : null;
        const lastVal = values[values.length-1] || null;
        cardCurrent.textContent = lastVal ? formatPH(lastVal) : '—';
        cardAvg.textContent     = avgAll ? formatPH(avgAll) : '—';
        cardLow.textContent     = lowAll ? formatPH(lowAll) : '—';
        updateChart(labels, values.map(v=>v===null?null:parseFloat(v.toFixed(2))));
        updateSummary(avgAll, lowAll, data.length);

    } else if (filter === 'yearly') {
        const groups = {};
        data.forEach(d => {
            const k = monthKey(d.ts);
            if (!groups[k]) groups[k] = [];
            groups[k].push(d.ph);
        });
        const keys = Object.keys(groups).sort();
        const labels = [];
        const values = [];
        keys.forEach(k => {
            const arr = groups[k];
            labels.push(k); // YYYY-MM
            values.push(arr.length ? average(arr) : null);
        });
        renderTableAggregated(labels, values, 'Month');
        const valNums = values.filter(v=>v!==null);
        const avgAll  = valNums.length ? average(valNums) : null;
        const lowAll  = valNums.length ? Math.min(...valNums) : null;
        const lastVal = values[values.length-1] || null;
        cardCurrent.textContent = lastVal ? formatPH(lastVal) : '—';
        cardAvg.textContent     = avgAll ? formatPH(avgAll) : '—';
        cardLow.textContent     = lowAll ? formatPH(lowAll) : '—';
        updateChart(labels, values.map(v=>v===null?null:parseFloat(v.toFixed(2))));
        updateSummary(avgAll, lowAll, data.length);

    } else if (filter === 'custom') {
        const fromDate = new Date(from);
        const toDate   = new Date(to);
        const diffDays = Math.ceil((toDate - fromDate) / (1000*60*60*24)) + 1;

        if (diffDays <= 1) {
            const lastRows = data.slice(-5).reverse();
            renderTableRawRows(lastRows);
            const phs = data.map(d=>d.ph);
            cardCurrent.textContent = lastRows.length?formatPH(lastRows[0].ph):'—';
            cardAvg.textContent     = phs.length?formatPH(average(phs)):'—';
            cardLow.textContent     = phs.length?formatPH(Math.min(...phs)):'—';
            updateChart(data.slice(-20).map(d=>formatTime(d.ts)), data.slice(-20).map(d=>d.ph));
            updateSummary(average(phs), Math.min(...phs), data.length);

        } else if (diffDays <= 7) {
            const groups = {};
            data.forEach(d => {
                const k = dayKey(d.ts);
                if (!groups[k]) groups[k] = [];
                groups[k].push(d.ph);
            });
            const labels = Object.keys(groups).sort();
            const values = labels.map(k => average(groups[k]));
            renderTableAggregated(labels, values, 'Day');
            const valNums = values.filter(v=>v!==null);
            updateCardAndChartFromArray(values, labels);
            updateSummary(average(valNums), Math.min(...valNums), data.length);

        } else if (diffDays <= 90) {
            const groups = {};
            data.forEach(d => {
                const k = weekKey(d.ts);
                if (!groups[k]) groups[k] = [];
                groups[k].push(d.ph);
            });
            const labels = Object.keys(groups).sort();
            const values = labels.map(k => average(groups[k]));
            renderTableAggregated(labels, values, 'Week');
            updateCardAndChartFromArray(values, labels);
            const valNums = values.filter(v=>v!==null);
            updateSummary(average(valNums), Math.min(...valNums), data.length);

        } else {
            const groups = {};
            data.forEach(d => {
                const k = monthKey(d.ts);
                if (!groups[k]) groups[k] = [];
                groups[k].push(d.ph);
            });
            const labels = Object.keys(groups).sort();
            const values = labels.map(k => average(groups[k]));
            renderTableAggregated(labels, values, 'Month');
            updateCardAndChartFromArray(values, labels);
            const valNums = values.filter(v=>v!==null);
            updateSummary(average(valNums), Math.min(...valNums), data.length);
        }

    } else {
        const lastRows = data.slice(-5).reverse();
        renderTableRawRows(lastRows);
        const phs = data.map(d=>d.ph);
        cardCurrent.textContent = lastRows.length?formatPH(lastRows[0].ph):'—';
        cardAvg.textContent     = phs.length?formatPH(average(phs)):'—';
        cardLow.textContent     = phs.length?formatPH(Math.min(...phs)):'—';
        updateChart(data.slice(-20).map(d=>formatTime(d.ts)), data.slice(-20).map(d=>d.ph));
        updateSummary(average(phs), Math.min(...phs), data.length);
    }
}

/* ------------- TABLE RENDERING ------------- */
function renderTableRawRows(rows) {
    tableBody.innerHTML = '';
    rows.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>Raw</td>
                        <td>${formatPH(r.ph)}</td>
                        <td>${formatDate(r.ts)}</td>
                        <td>${formatTime(r.ts)}</td>`;
        tableBody.appendChild(tr);
    });
}

function renderTableAggregated(labels, values, labelName) {
    tableBody.innerHTML = '';
    for (let i=0;i<labels.length;i++) {
        const lbl = labels[i];
        const val = values[i];
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${labelName}: ${lbl}</td>
                        <td>${val===null?'—':formatPH(val)}</td>
                        <td>-</td>
                        <td>-</td>`;
        tableBody.appendChild(tr);
    }
}

/* ------------- CHART ------------- */
function updateChart(labels, dataArr) {
    const ctx = document.getElementById('phChart').getContext('2d');
    const mapped = dataArr.map(v => (v===null?NaN:v));
    if (phChart) {
        phChart.data.labels = labels;
        phChart.data.datasets[0].data = mapped;
        phChart.update();
        return;
    }
    phChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'pH Value',
                data: mapped,
                borderColor: "#55ff88",
                backgroundColor: "rgba(85,255,136,0.12)",
                borderWidth: 3,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: "#99ffc2",
                spanGaps: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { ticks: { color: "#fff" } },
                y: { ticks: { color: "#fff" }, beginAtZero: false }
            },
            plugins: {
                legend: { labels: { color: "#fff" } }
            }
        }
    });
}

/* ------------- SUMMARY BOXES ------------- */
function updateCardAndChartFromArray(values, labels) {
    const valNums = values.filter(v=>v!==null);
    const avgAll  = valNums.length ? average(valNums) : null;
    const lowAll  = valNums.length ? Math.min(...valNums) : null;
    const lastVal = values.length ? values[values.length-1] : null;
    cardCurrent.textContent = lastVal ? formatPH(lastVal) : '—';
    cardAvg.textContent     = avgAll ? formatPH(avgAll) : '—';
    cardLow.textContent     = lowAll ? formatPH(lowAll) : '—';
    updateChart(labels, values.map(v=>v===null?null:parseFloat(v.toFixed(2))));
}

function updateSummary(avg, low, count) {
    summaryAvg.textContent   = 'Avg: '    + (avg ? formatPH(avg) : '—');
    summaryLow.textContent   = 'Lowest: ' + (low ? formatPH(low) : '—');
    summaryCount.textContent = 'Count: '  + (count || 0);
}

/* ------------- pH RANGE MODAL LOGIC ------------- */
phRangeBtn.addEventListener('click', openPhRangeModal);
phRangeCloseBtn.addEventListener('click', closePhRangeModal);
phRangeModal.addEventListener('click', (e) => {
    if (e.target === phRangeModal) closePhRangeModal();
});
phRangeForm.addEventListener('submit', savePhRange);

function openPhRangeModal() {
    phRangeStatus.textContent = '';
    phRangeStatus.className   = 'status-msg';
    phRangeModal.style.display = 'flex';
    loadCurrentPhRange();
}

function closePhRangeModal() {
    phRangeModal.style.display = 'none';
}

async function loadCurrentPhRange() {
    phRangeStatus.textContent = 'Loading...';
    phRangeStatus.className   = 'status-msg';

    try {
        const res = await fetch(phRangeApi);
        if (!res.ok) throw new Error('Network error: ' + res.status);
        const json = await res.json();

        if (json.status !== 'success' || !Array.isArray(json.data) || json.data.length === 0) {
            phRangeStatus.textContent = 'No range found, you can insert new.';
            rangeIdInput.value        = '';
            firstRangeInput.value     = '';
            secondRangeInput.value    = '';
            return;
        }

        const r = json.data[0];
        rangeIdInput.value     = r.id || '';
        firstRangeInput.value  = (typeof r.first_range  !== 'undefined') ? Number(r.first_range).toFixed(2)  : '';
        secondRangeInput.value = (typeof r.second_range !== 'undefined') ? Number(r.second_range).toFixed(2) : '';

        phRangeStatus.textContent = 'Loaded current range.';
        phRangeStatus.classList.add('ok');

    } catch (err) {
        console.error('Failed to load pH range:', err);
        phRangeStatus.textContent = 'Error loading range.';
        phRangeStatus.classList.add('error');
    }
}

async function savePhRange(e) {
    e.preventDefault();
    phRangeStatus.textContent = '';
    phRangeStatus.className   = 'status-msg';

    const id     = rangeIdInput.value ? parseInt(rangeIdInput.value, 10) : 0;
    const first  = parseFloat(firstRangeInput.value);
    const second = parseFloat(secondRangeInput.value);

    if (isNaN(first) || isNaN(second)) {
        phRangeStatus.textContent = 'Please enter valid numeric values.';
        phRangeStatus.classList.add('error');
        return;
    }
    if (first >= second) {
        phRangeStatus.textContent = 'First range must be less than second range.';
        phRangeStatus.classList.add('error');
        return;
    }

    try {
        phRangeStatus.textContent = 'Saving...';

        const body = new URLSearchParams({
            id: id > 0 ? String(id) : '',
            first_range: first.toFixed(2),
            second_range: second.toFixed(2)
        });

        const res = await fetch(phRangeSaveApi, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });

        if (!res.ok) throw new Error('Network error: ' + res.status);
        const json = await res.json();

        if (json.status !== 'success') {
            phRangeStatus.textContent = json.message || 'Failed to save range.';
            phRangeStatus.classList.add('error');
            return;
        }

        if (json.id) {
            rangeIdInput.value = json.id;
        }

        phRangeStatus.textContent = 'Saved successfully.';
        phRangeStatus.classList.add('ok');

    } catch (err) {
        console.error('Save error:', err);
        phRangeStatus.textContent = 'Error saving range.';
        phRangeStatus.classList.add('error');
    }
}
</script>

</body>
</html>
