<?php 
session_start(); 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login/login.php");
    exit;
}

$clientName = $_SESSION['client_name'] ?? 'Yours';
$client_id = $_SESSION['client_id'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home Page</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root{
      --accent:#016795;
      --card-bg: rgba(255,255,255,0.96);
      --page-bg: #096C6C;
    }

    html,body{ height:100%; }
    body {
      margin: 0;
      font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background-color: var(--page-bg);
      color: #111;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    #filterBtn {
      position: fixed;
      top: 14px;
      right: 14px;
      z-index: 1200;
      background-color: var(--accent);
      color: white;
      padding: 10px 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      box-shadow: 0 6px 16px rgba(1,103,149,0.25);
    }

    /* Dropdown (moved to CSS instead of inline styles) */
    #dropdown {
      position: fixed;
      top: 60px;
      right: 14px;
      z-index: 1300;
      background-color: #f7f7f7;
      color: #111;
      border-radius: 10px;
      border: 1px solid rgba(0,0,0,0.08);
      box-shadow: 0 12px 36px rgba(0,0,0,0.12);
      padding: 14px;
      width: min(700px, 95vw); /* responsive */
      max-width: 700px;
      display: none;
      max-height: 72vh;
      overflow: auto;
      -webkit-overflow-scrolling: touch;
    }

    #dropdown .section-title {
      margin-bottom: 8px;
      font-weight: 700;
      color: white;
      background: var(--accent);
      padding: 6px 10px;
      border-radius: 6px;
      display: inline-block;
    }

    #dropdown label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 8px;
      margin: 4px;
      border-radius: 6px;
      background: #ececec;
      cursor: pointer;
      user-select: none;
    }
    #dropdown label:hover { background:#e0e0e0; }
    #dropdown input[type="checkbox"], #dropdown input[type="radio"] {
      transform: scale(1.05);
      accent-color: var(--accent);
    }

    .time-options-row, .graph-options-row {
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-bottom: 12px;
    }

    #graphContainer {
	  margin: 110px 28px 28px 28px;
	  display: grid;
	  grid-template-columns: repeat(2, 1fr); 
	  gap: 24px;
	}

	.card {
	  background: var(--card-bg);
	  border-radius: 12px;
	  padding: 16px;
	  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
	  min-height: 340px; /* ✅ Bigger height for graph */
	  display:flex;
	  flex-direction:column;
	  justify-content:flex-start;
	}

	.card h3 {
	  margin:0 0 14px 0;
	  color:var(--accent);
	  font-size:1.1rem;
	}

	.card canvas {
	  width: 100% !important;
	  height: 260px !important; /* ✅ More space for chart */
	  display:block;
	}

	/* Medium screens (tablet) */
	@media (max-width: 900px) {
	  #graphContainer {
		grid-template-columns: repeat(2, 1fr); /* ✅ Still 2 cards per row */
		gap: 16px;
	  }
	  .card {
		min-height: 300px;
		padding: 14px;
	  }
	  .card canvas { height: 220px !important; }
	}

	/* Small screens (mobile) */
	@media (max-width: 600px) {
	  #graphContainer {
		grid-template-columns: 1fr; /* ✅ Only 1 card per row */
		margin: 96px 12px 18px 12px;
		gap: 18px;
	  }
	  .card {
		min-height: 320px;
	  }
	  .card canvas { height: 240px !important; }
	}
  </style>
</head>
<body>

  <button id="filterBtn" aria-haspopup="true" aria-expanded="false">📊 Filter Graphs</button>

  <div id="dropdown" role="dialog" aria-label="Filter graphs panel">
    <div style="margin-bottom:12px;">
      <span class="section-title">Select Format:</span>
    </div>

    <div class="time-options-row">
      <label><input type="radio" name="timeRange" value="today" checked> <span>Today</span></label>
      <label><input type="radio" name="timeRange" value="yesterday"> <span>Yesterday</span></label>
      <label><input type="radio" name="timeRange" value="weekly"> <span>Weekly</span></label>
      <label><input type="radio" name="timeRange" value="monthly"> <span>Monthly</span></label>
      <label><input type="radio" name="timeRange" value="yearly"> <span>Yearly</span></label>
    </div>

    <hr style="margin:8px 0; border:none; border-top:1px solid rgba(0,0,0,0.06);">

    <div style="margin-bottom:10px;"><span class="section-title">Graph Options:</span></div>
    <div class="graph-options-row">
      <label><input type="checkbox" value="profitandloss" onchange="renderGraphs()"> <span>Profit And Loss</span></label>
      <label><input type="checkbox" value="sheadMortality" onchange="renderGraphs()"> <span>Shead Mortality</span></label>
      <label><input type="checkbox" value="eggProduction" onchange="renderGraphs()"> <span>Egg Production</span></label>
      <label><input type="checkbox" value="eggDamage" onchange="renderGraphs()"> <span>Egg Damage</span></label>
	  <label><input type="checkbox" value="feedintake" onchange="renderGraphs()"> <span>Feed Intake</span></label>
      <label><input type="checkbox" value="eggWeight" onchange="renderGraphs()"> <span>Egg Weight</span></label>
	  <label><input type="checkbox" value="openingandclosingbalance" onchange="renderGraphs()"> <span>Opening & Closing</span></label>
      <label><input type="checkbox" value="productionpercentage" onchange="renderGraphs()"> <span>Production %</span></label>
      <label><input type="checkbox" value="eggprice" onchange="renderGraphs()"> <span>Egg Price</span></label>
      <label><input type="checkbox" value="livebirds" onchange="renderGraphs()"> <span>Live Birds</span></label>
    </div>
  </div>

  <div id="graphContainer"></div>

<script>
const clientId = <?= $client_id ?>;

function toggleDropdown() {
  const dropdown = document.getElementById('dropdown');
  const btn = document.getElementById('filterBtn');
  const isOpen = dropdown.style.display === 'block';
  dropdown.style.display = isOpen ? 'none' : 'block';
  btn.setAttribute('aria-expanded', !isOpen);
}

document.addEventListener('click', (evt) => {
  const dropdown = document.getElementById('dropdown');
  const btn = document.getElementById('filterBtn');
  if (!dropdown.contains(evt.target) && !btn.contains(evt.target)) {
    dropdown.style.display = 'none';
    btn.setAttribute('aria-expanded', false);
  }
});

document.getElementById('filterBtn').addEventListener('click', (e) => {
  e.stopPropagation();
  toggleDropdown();
});

async function fetchProfitLoss(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/profit_and_loss_details/profit_loss_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url, { method: 'GET', credentials: 'same-origin' });
    const text = await res.text();
    console.log('API raw response:', text);
    try {
      return JSON.parse(text);
    } catch (parseErr) {
      console.error('JSON parse error:', parseErr);
      return null;
    }
  } catch (err) {
    console.error('Fetch error:', err);
    return null;
  }
}

async function fetchMortality(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/mortality_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Mortality API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Mortality Fetch Error:", err);
    return null;
  }
}

async function fetchEggProduction(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/egg_stock_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Egg Production API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Egg Production Fetch Error:", err);
    return null;
  }
}

async function fetchEggDamage(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/egg_damage_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Egg Damage API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Egg Damage Fetch Error:", err);
    return null;
  }
}

async function fetchFeedIntake(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/feed_intake_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    return JSON.parse(text);
  } catch (err) {
    console.error("Feed Intake Fetch Error:", err);
    return null;
  }
}

async function fetchEggWeight(fromDate, toDate) {
  const url = `https://sunfra.com/farm/sunfra/index/egg_weight_json.php?client_id=${clientId}&from_date=${fromDate}&to_date=${toDate}`;
  try {
    const res = await fetch(url);
    const text = await res.text();
    console.log("Egg Weight API Response:", text);
    return JSON.parse(text);
  } catch (err) {
    console.error("Egg Weight Fetch Error:", err);
    return null;
  }
}

function getDateRange(selectedTime) {
  const today = new Date();
  let fromDate, toDate;
  if (selectedTime === 'today') {
    fromDate = toDate = today.toISOString().split('T')[0];
  } else if (selectedTime === 'yesterday') {
    const y = new Date(today); y.setDate(today.getDate() - 1);
    fromDate = toDate = y.toISOString().split('T')[0];
  } else if (selectedTime === 'weekly') {
    const end = today.toISOString().split('T')[0];
    const start = new Date(today); start.setDate(today.getDate() - 6);
    fromDate = start.toISOString().split('T')[0]; toDate = end;
  } else if (selectedTime === 'monthly') {
    const end = today.toISOString().split('T')[0];
    const start = new Date(today.getFullYear(), today.getMonth(), 1);
    fromDate = start.toISOString().split('T')[0]; toDate = end;
  } else if (selectedTime === 'yearly') {
    const end = today.toISOString().split('T')[0];
    const start = new Date(today.getFullYear(), 0, 1);
    fromDate = start.toISOString().split('T')[0]; toDate = end;
  } else {
    fromDate = toDate = today.toISOString().split('T')[0];
  }
  return { fromDate, toDate };
}

async function renderGraphs() {
  const container = document.getElementById('graphContainer');
  container.innerHTML = '';

  const selectedGraphs = Array.from(document.querySelectorAll('#dropdown input[type="checkbox"]:checked'));
  const selectedTime = document.querySelector('#dropdown input[name="timeRange"]:checked')?.value || 'today';
  const { fromDate, toDate } = getDateRange(selectedTime);


  await Promise.all(selectedGraphs.map(async (cb, idx) => {
    const type = cb.value;
    const titleText = `${cb.closest('label').innerText.trim()} (${selectedTime[0].toUpperCase() + selectedTime.slice(1)})`;

    const wrapper = document.createElement('div');
    wrapper.className = 'card';

    const canvasId = `${type}_canvas_${idx}_${Date.now()}`;

    wrapper.innerHTML = `<h3>${titleText}</h3><canvas id="${canvasId}"></canvas>`;
    container.appendChild(wrapper);

    const canvas = document.getElementById(canvasId);
    if (!canvas) { console.error('Canvas not found:', canvasId); return; }
    const ctx = canvas.getContext('2d');

    if (type === 'profitandloss') {
	  const apiData = await fetchProfitLoss(fromDate, toDate);

	  if (apiData && Array.isArray(apiData.data) && apiData.data.length > 0) {
		const sheadNames = apiData.data.map(d => d.shead_name);

		const profits = apiData.data.map(d => {
		  const val = (d.profit || "0").toString().replace(/,/g, "");
		  return Number(val) || 0;
		});

		const totalProfit = profits.reduce((a, b) => a + b, 0);

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		wrapper.innerHTML = `
		  <div class="graph-card">
			<h3>${titleText}</h3>
			<div class="chart-container">
			  <canvas id="${canvasId}"></canvas>
			</div>
		  </div>
		`;

		const ctx = document.getElementById(canvasId).getContext("2d");

		canvas._chartInstance = new Chart(ctx, {
		  type: 'bar',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Profit (₹)',
			  data: profits,
			  backgroundColor: profits.map(v =>
				v >= 0 ? 'rgba(40,167,69,0.85)' : 'rgba(220,53,69,0.85)'
			  ),
			  borderRadius: 6
			}]
		  },
		  options: {
			maintainAspectRatio: false,
			plugins: {
			  tooltip: {
				callbacks: {
				  label: c => `₹ ${Number(c.raw).toLocaleString()}`
				}
			  },
			  legend: { display: false }
			},
			scales: {
			  x: { ticks: { autoSkip: false } },
			  y: {
				beginAtZero: true,
				ticks: {
				  callback: v => '₹ ' + v
				}
			  }
			},
			animation: { duration: 800, easing: 'easeOutCubic' }
		  }
		});

		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = totalProfit >= 0 ? "#28a745" : "#dc3545"; 
		summary.style.color = "#fff";
		summary.style.padding = "12px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = totalProfit >= 0
		  ? `🚀 Total Profit: ₹ ${totalProfit.toLocaleString()}`
		  : `📉 Total Loss: ₹ ${Math.abs(totalProfit).toLocaleString()}`;

		wrapper.querySelector(".graph-card").appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Profit & Loss data available for selected range.</div>`;
	  }
	}
	else if (type === 'sheadMortality') {
	  const apiData = await fetchMortality(fromDate, toDate);
	  console.log("Mortality Parsed JSON:", apiData);

	  let mortalityArray = [];
	  if (Array.isArray(apiData)) {
		mortalityArray = apiData;
	  } else if (apiData && Array.isArray(apiData.records)) {
		mortalityArray = apiData.records;
	  }

	  if (Array.isArray(mortalityArray) && mortalityArray.length > 0) {
		const sheadNames = mortalityArray.map(d => d.sheadNo);
		const deaths = mortalityArray.map(d => Number(d.noOfBirds || d.totalBirds || 0));
		const totalDeaths = deaths.reduce((a, b) => a + b, 0);

		wrapper.innerHTML = "";

		const title = document.createElement("h3");
		title.innerText = titleText;
		wrapper.appendChild(title);

		const newCanvas = document.createElement("canvas");
		newCanvas.width = 400;
		newCanvas.height = 400;
		wrapper.appendChild(newCanvas);
		const ctx = newCanvas.getContext("2d");

		if (newCanvas._chartInstance) {
		  newCanvas._chartInstance.destroy();
		}

		const colors = mortalityArray.map((_, i) => {
		  const hue = (i * 40) % 360;
		  return `hsl(${hue}, 70%, 55%)`;
		});

		newCanvas._chartInstance = new Chart(ctx, {
		  type: 'doughnut',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Mortality',
			  data: deaths,
			  backgroundColor: colors,
			  borderWidth: 2
			}]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  title: { display: false }, 
			  tooltip: {
				callbacks: {
				  label: ctx => `${ctx.label}: ${ctx.raw} birds`
				}
			  },
			  legend: { display: false }
			},
			layout: { padding: 20 }
		  }
		});

		const legendDiv = document.createElement("div");
		legendDiv.style.display = "flex";
		legendDiv.style.flexWrap = "wrap";
		legendDiv.style.justifyContent = "center";
		legendDiv.style.marginTop = "15px";
		legendDiv.style.gap = "10px";

		sheadNames.forEach((name, index) => {
		  const item = document.createElement("div");
		  item.style.display = "flex";
		  item.style.alignItems = "center";
		  item.style.cursor = "pointer";
		  item.style.padding = "4px 10px";
		  item.style.border = "1px solid #ddd";
		  item.style.borderRadius = "6px";
		  item.style.background = "#f9f9f9";

		  const colorBox = document.createElement("span");
		  colorBox.style.display = "inline-block";
		  colorBox.style.width = "14px";
		  colorBox.style.height = "14px";
		  colorBox.style.background = colors[index];
		  colorBox.style.marginRight = "6px";
		  colorBox.style.borderRadius = "3px";

		  const label = document.createElement("span");
		  label.textContent = name;
		  label.style.fontSize = "13px";
		  label.style.color = "#333";

		  item.appendChild(colorBox);
		  item.appendChild(label);

		  item.onclick = function () {
			const dataset = newCanvas._chartInstance.data.datasets[0];

			if (!dataset._originalData) {
			  dataset._originalData = [...dataset.data];
			}

			if (dataset.data[index] === 0) {
			  dataset.data[index] = dataset._originalData[index];
			  colorBox.style.opacity = "1";
			  label.style.textDecoration = "none";
			} else {
			  dataset.data[index] = 0;
			  colorBox.style.opacity = "0.3";
			  label.style.textDecoration = "line-through";
			}
			newCanvas._chartInstance.update();
		  };

		  legendDiv.appendChild(item);
		});

		wrapper.appendChild(legendDiv);

		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#ff4d6d";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `☠️ Total Mortality: ${totalDeaths.toLocaleString()}`;
		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Mortality data available for selected range.</div>`;
	  }
	}
	else if (type === 'eggProduction') {
	  const apiData = await fetchEggProduction(fromDate, toDate);

	  if (Array.isArray(apiData) && apiData.length > 0) {
		const sheadNames = apiData.map(d => d.shead_name);
		const good = apiData.map(d => parseFloat(d.Good || 0));
		const small = apiData.map(d => parseFloat(d.Small || 0));
		const big = apiData.map(d => parseFloat(d.Big || 0));
		const damaged = apiData.map(d => parseFloat(d.Damaged || 0));

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		canvas._chartInstance = new Chart(ctx, {
		  type: 'bar',
		  data: {
			labels: sheadNames,
			datasets: [
			  { label: 'Good', data: good, backgroundColor: '#4caf50' },
			  { label: 'Small', data: small, backgroundColor: '#2196f3' },
			  { label: 'Big', data: big, backgroundColor: '#ff9800' },
			  { label: 'Damaged', data: damaged, backgroundColor: '#f44336' }
			]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  title: {
				display: true,
				text: 'Egg Production by Shead',
				font: { size: 18 }
			  },
			  tooltip: {
				mode: 'index',
				intersect: false
			  }
			},
			scales: {
			  x: { stacked: true },
			  y: { stacked: true, beginAtZero: true }
			},
			onClick: (evt, elements) => {
			  if (elements.length > 0) {
				const index = elements[0].index; 
				const shead = sheadNames[index];
				const details = `
				  <b>${shead}</b><br>
				  ✅ Good: ${good[index].toLocaleString()}<br>
				  📏 Small: ${small[index].toLocaleString()}<br>
				  🍳 Big: ${big[index].toLocaleString()}<br>
				  ❌ Damaged: ${damaged[index].toLocaleString()}
				`;

				//let popup = document.getElementById("eggPopup");
				if (!popup) {
				  popup = document.createElement("div");
				  popup.id = "eggPopup";
				  popup.style.position = "fixed";
				  popup.style.top = "50%";
				  popup.style.left = "50%";
				  popup.style.transform = "translate(-50%, -50%)";
				  popup.style.background = "#fff";
				  popup.style.padding = "15px";
				  popup.style.border = "2px solid #016795";
				  popup.style.borderRadius = "8px";
				  popup.style.boxShadow = "0 4px 12px rgba(0,0,0,0.2)";
				  popup.style.zIndex = "9999";
				  popup.style.maxWidth = "250px";
				  popup.style.textAlign = "left";
				  document.body.appendChild(popup);
				}
				popup.innerHTML = details + `<br><br><button onclick="document.getElementById('eggPopup').remove()">Close</button>`;
			  }
			}
		  }
		});

		const totalEggs = good.reduce((a,b)=>a+b,0) + small.reduce((a,b)=>a+b,0) + big.reduce((a,b)=>a+b,0) + damaged.reduce((a,b)=>a+b,0);
		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#016795";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `🥚 Total Eggs: ${totalEggs.toLocaleString()}`;
		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Egg Production data available for selected range.</div>`;
	  }
	}else if (type === 'eggDamage') {
	  const apiData = await fetchEggDamage(fromDate, toDate);

	  if (Array.isArray(apiData) && apiData.length > 0) {
		const sheadNames = apiData.map(d => d.shead_name);
		const trays = apiData.map(d => parseFloat(d.trays || 0));

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		canvas._chartInstance = new Chart(ctx, {
		  type: 'line',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Damaged Trays',
			  data: trays,
			  fill: false,
			  borderColor: '#e63946',   // Red line
			  backgroundColor: '#e63946',
			  tension: 0.3,             // Smooth curve
			  pointRadius: 5,
			  pointHoverRadius: 7,
			  pointStyle: 'circle'
			}]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  title: {
				display: true,
				font: { size: 18 }
			  },
			  tooltip: {
				callbacks: {
				  label: ctx => `🟥 ${ctx.dataset.label}: ${ctx.raw} trays`
				}
			  },
			  legend: { display: false }
			},
			scales: {
			  x: {
				ticks: { autoSkip: false }
			  },
			  y: {
				beginAtZero: true,
				title: {
				  display: true,
				  text: 'Trays'
				}
			  }
			},
			animation: { duration: 800, easing: 'easeOutCubic' }
		  }
		});

		const totalTrays = trays.reduce((a, b) => a + b, 0).toFixed(2);
		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#e63946";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `🥚 Total Damaged Trays: ${totalTrays}`;
		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Egg Damage data available for selected range.</div>`;
	  }
	}else if (type === 'feedintake') {
	  const apiData = await fetchFeedIntake(fromDate, toDate);
	  if (apiData && Array.isArray(apiData.data) && apiData.data.length > 0) {
		const sheadNames = apiData.data.map(d => d.shead_name);
		const avgFeed = apiData.data.map(d => parseFloat(d.average_feed_intake) || 0);

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		const colors = sheadNames.map((_, i) => {
		  const hue = (i * 40) % 360;
		  return `hsl(${hue}, 70%, 55%)`;
		});

		canvas._chartInstance = new Chart(ctx, {
		  type: 'pie',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Average Feed Intake (g)',
			  data: avgFeed,
			  backgroundColor: colors,
			  borderColor: '#fff',
			  borderWidth: 2
			}]
		  },
		  options: {
			  responsive: true,
			  maintainAspectRatio: false,
			  plugins: {
				legend: { display: false },
				tooltip: {
				  callbacks: {
					label: ctx => `${ctx.label}: ${ctx.raw.toFixed(2)} g`
				  }
				},
				title: {
				  display: true,
				  text: 'Feed Intake by Shead',
				  font: { size: 16 }
				}
			  }
			}
		});

		const legendDiv = document.createElement("div");
		legendDiv.style.display = "flex";
		legendDiv.style.flexWrap = "wrap";
		legendDiv.style.justifyContent = "center";
		legendDiv.style.marginTop = "12px";
		legendDiv.style.gap = "10px";

		sheadNames.forEach((name, index) => {
		  const item = document.createElement("div");
		  item.style.display = "flex";
		  item.style.alignItems = "center";
		  item.style.cursor = "pointer";
		  item.style.padding = "4px 10px";
		  item.style.border = "1px solid #ddd";
		  item.style.borderRadius = "6px";
		  item.style.background = "#f9f9f9";

		  const colorBox = document.createElement("span");
		  colorBox.style.display = "inline-block";
		  colorBox.style.width = "14px";
		  colorBox.style.height = "14px";
		  colorBox.style.background = colors[index];
		  colorBox.style.marginRight = "6px";
		  colorBox.style.borderRadius = "3px";

		  const label = document.createElement("span");
		  label.textContent = name;
		  label.style.fontSize = "13px";
		  label.style.color = "#333";

		  item.appendChild(colorBox);
		  item.appendChild(label);

		  item.onclick = function () {
			  const chart = canvas._chartInstance;
			  const meta = chart.getDatasetMeta(0);

			  meta.data[index].hidden = !meta.data[index].hidden;

			  if (meta.data[index].hidden) {
				colorBox.style.opacity = "0.3";
				label.style.textDecoration = "line-through";
			  } else {
				colorBox.style.opacity = "1";
				label.style.textDecoration = "none";
			  }

			  chart.update();
			};

		  legendDiv.appendChild(item);
		});

		wrapper.appendChild(legendDiv);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Feed Intake data available for selected range.</div>`;
	  }
	}
	else if (type === 'eggWeight') {
	  const apiData = await fetchEggWeight(fromDate, toDate);

	  if (apiData && Array.isArray(apiData.data) && apiData.data.length > 0) {
		const sheadNames = apiData.data.map(d => d.shead_name);
		const avgWeights = apiData.data.map(d => parseFloat(d.average_egg_weight || 0));
		const days = apiData.data.map(d => d.days);

		if (canvas._chartInstance) { canvas._chartInstance.destroy(); }

		canvas._chartInstance = new Chart(ctx, {
		  type: 'bar',
		  data: {
			labels: sheadNames,
			datasets: [{
			  label: 'Avg Egg Weight (g)',
			  data: avgWeights,
			  backgroundColor: '#6a5acd', // purple shade
			  borderRadius: 8
			}]
		  },
		  options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
			  tooltip: {
				callbacks: {
				  label: (ctx) => 
					`${ctx.dataset.label}: ${ctx.raw.toFixed(2)} g (Days: ${days[ctx.dataIndex]})`
				}
			  },
			  title: {
				display: true,
				text: 'Average Egg Weight by Shead',
				font: { size: 18 }
			  },
			  legend: { display: false }
			},
			scales: {
			  x: { ticks: { autoSkip: false } },
			  y: {
				beginAtZero: true,
				title: {
				  display: true,
				  text: 'Weight (g)'
				}
			  }
			},
			animation: { duration: 800, easing: 'easeOutCubic' }
		  }
		});

		const overallAvg = (
		  avgWeights.reduce((a, b) => a + b, 0) / avgWeights.length
		).toFixed(2);

		const summary = document.createElement("div");
		summary.className = "summary-tile";
		summary.style.background = "#6a5acd";
		summary.style.color = "#fff";
		summary.style.padding = "10px";
		summary.style.marginTop = "12px";
		summary.style.textAlign = "center";
		summary.style.borderRadius = "8px";
		summary.innerText = `⚖️ Overall Avg Weight: ${overallAvg} g`;

		wrapper.appendChild(summary);

	  } else {
		wrapper.innerHTML = `<h3>${titleText}</h3><div class="muted">No Egg Weight data available for selected range.</div>`;
	  }
	}
	else {
      if (canvas._chartInstance) { canvas._chartInstance.destroy(); }
      canvas._chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
          datasets: [{
            label: cb.nextSibling?.textContent?.trim() || 'Series',
            data: Array.from({length:5}, ()=>Math.floor(Math.random()*100)),
            borderColor: '#016795',
            backgroundColor: 'rgba(1,103,149,0.12)',
            fill: true,
            tension: 0.3
          }]
        },
        options: { maintainAspectRatio:false }
      });
    }
  }));
}

document.querySelectorAll('#dropdown input[type="checkbox"]').forEach(i => i.addEventListener('change', renderGraphs));
document.querySelectorAll('#dropdown input[name="timeRange"]').forEach(r => r.addEventListener('change', renderGraphs));

renderGraphs();
</script>
</body>
</html>