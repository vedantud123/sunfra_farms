<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Silo Stock Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/utc.js"></script>
<script>
  dayjs.extend(dayjs_plugin_utc);
</script>
<style>
body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#1b1b2d; color:#f0f0f0; }
header { padding:20px; text-align:center; background:#2c2c44; box-shadow:0 2px 5px rgba(0,0,0,0.5); }
header h1 { margin:0; font-size:28px; }
.container { max-width:1100px; margin:20px auto; padding:10px; }
.controls { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px; align-items:center; }
.controls select, .controls input { padding:8px 12px; border-radius:6px; border:none; outline:none; font-size:14px; background:#3a3a5c; color:#fff; }
.card { background:#2b2b44; border-radius:10px; padding:20px; margin-bottom:20px; box-shadow:0 4px 15px rgba(0,0,0,0.4); }
#mainChart { width:100%; height:400px; }
.latest-list { max-height:400px; overflow-y:auto; margin-top:10px; }
.latest-item { display:flex; justify-content:space-between; padding:8px 12px; background:#3a3a5c; border-radius:6px; margin-bottom:6px; font-size:14px; }
footer { text-align:center; padding:15px; font-size:13px; color:#aaa; }header {
  padding: 20px;
  text-align: center;
  background: #2c2c44;
  box-shadow: 0 2px 5px rgba(0,0,0,0.5);
  position: relative;
}

header h1 {
  margin: 0;
  font-size: 28px;
}

.home-btn {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  padding: 8px 16px;
  background: #4caf50;
  color: #fff;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.3s;
}

.home-btn:hover {
  background: #45a049;
}

</style>
</head>
<body>
<header>
  <h1>Silo Stock Usage Dashboard</h1>
  <a href="https://sunfra.com/farm/sensor/expo_sensor/index_page_for_display.php" class="home-btn">Home</a>
</header>
<div class="container">
  <div class="controls">
    <select id="rangeSelect">
      <option value="today">Today</option>
      <option value="yesterday">Yesterday</option>
      <option value="weekly">Weekly</option>
      <option value="monthly">Monthly</option>
      <option value="yearly">Yearly</option>
      <option value="custom">Custom</option>
    </select>
    <input type="date" id="fromDate" style="display:none">
    <input type="date" id="toDate" style="display:none">
  </div>

  <div class="card"><canvas id="mainChart"></canvas></div>

  <div class="card">
    <h3>Latest Readings</h3>
    <div class="latest-list" id="latestList"></div>
  </div>
</div>

<footer>Data Dashboard • Powered by Chart.js & Day.js</footer>
<script>
const baseApi = 'https://sunfra.com/farm/sensor/expo_sensor/silo_indicator_day_json.php';
const rangeSelect = document.getElementById('rangeSelect');
const fromDateInput = document.getElementById('fromDate');
const toDateInput = document.getElementById('toDate');
const latestList = document.getElementById('latestList');
let chart = null;

function buildUrl(from, to){
  const url = new URL(baseApi);
  url.searchParams.set('from_date', from);
  url.searchParams.set('to_date', to);
  console.log('API URL:', url.toString()); // debug
  return url;
}

async function fetchData(from, to){
  try {
    const res = await fetch(buildUrl(from, to));
    const json = await res.json();
    console.log('API Response:', json);
    if(json.status === 'success' && Array.isArray(json.data)) return json.data;
    if(json.status === 'error'){
      console.warn('API returned error:', json.message);
      return [];
    }
    if(Array.isArray(json)) return json;
    console.warn('No data received or unrecognized format', json);
    return [];
  } catch(e){ 
    console.error('Fetch error:', e); 
    return []; 
  }
}

function updateLatestList(data){
  latestList.innerHTML = '';
  if(data.length === 0){
    const div = document.createElement('div');
    div.className = 'latest-item';
    div.textContent = 'No data available for the selected date range';
    latestList.appendChild(div);
    return;
  }
  data.slice()
      .sort((a,b)=>new Date(b.timestamp)-new Date(a.timestamp))
      .slice(0,10)
      .forEach(r=>{
        const div = document.createElement('div');
        div.className='latest-item';
        div.textContent = `${r.date} ${r.timestamp.split(' ')[1]} - ${r.value}`;
        latestList.appendChild(div);
      });
}

function getAllDatesInRange(from, to){
  const dates = [];
  let curr = dayjs(from);
  const end = dayjs(to);
  while(curr.isBefore(end) || curr.isSame(end,'day')){
    dates.push(curr.format('YYYY-MM-DD'));
    curr = curr.add(1,'day');
  }
  return dates;
}

function calculateStockFlow(data, gap = 100, rangeType='hour', from=null, to=null){
  if(data.length===0 && rangeType==='hour') return {labels:[], added:[], used:[]};

  if(rangeType==='hour'){
    const labels=[], added=[], used=[];
    let prev=null;
    data.sort((a,b)=>new Date(a.timestamp)-new Date(b.timestamp)).forEach(d=>{
      const val = Number(d.value);
      let change=0;
      if(prev!==null){ change = val-prev; if(Math.abs(change)<gap) change=0; }
      added.push(change>0?change:0);
      used.push(change<0?Math.abs(change):0);
      labels.push(d.timestamp.split(' ')[1]);
      prev=val;
    });
    return {labels, added, used};
  } else {
    const dailyMap={}, labels=[], added=[], used=[];
    let prev=null;
    data.sort((a,b)=>new Date(a.timestamp)-new Date(b.timestamp)).forEach(d=>{
      const date = d.date;
      const val = Number(d.value);
      let change=0;
      if(prev!==null){ change = val-prev; if(Math.abs(change)<gap) change=0; }
      if(!dailyMap[date]) dailyMap[date]={added:0, used:0};
      if(change>0) dailyMap[date].added += change;
      if(change<0) dailyMap[date].used += Math.abs(change);
      prev=val;
    });

    const allDates = getAllDatesInRange(from, to);
    allDates.forEach(d=>{
      labels.push(d);
      added.push(dailyMap[d] ? dailyMap[d].added : 0);
      used.push(dailyMap[d] ? dailyMap[d].used : 0);
    });

    return {labels, added, used};
  }
}

function createChart(labels, added, used){
  const ctx = document.getElementById('mainChart').getContext('2d');
  if(chart) chart.destroy();
  chart = new Chart(ctx,{
    type:'bar',
    data:{
      labels,
      datasets:[
        {label:'Stock Added', data:added, backgroundColor:'#4caf50'},
        {label:'Stock Used', data:used, type:'line', borderColor:'#f44336', backgroundColor:'#f44336', fill:false, tension:0.3}
      ]
    },
    options:{
      responsive:true,
      plugins:{legend:{display:true}},
      scales:{y:{beginAtZero:true}}
    }
  });
}

function resolveRange(type){
  const now = dayjs(); 
  let from,to,rangeMode='hour';
  
  switch(type){
    case 'today':
      from = now.format('YYYY-MM-DD');
      to = from;
      rangeMode='hour';
      break;
    case 'yesterday':
      from = now.subtract(1,'day').format('YYYY-MM-DD');
      to = from;
      rangeMode='hour';
      break;
    case 'weekly':
      from = now.subtract(6,'day').format('YYYY-MM-DD'); 
      to = now.format('YYYY-MM-DD');
      rangeMode='date';
      break;
    case 'monthly':
      from = now.subtract(29,'day').format('YYYY-MM-DD');
      to = now.format('YYYY-MM-DD');
      rangeMode='date';
      break;
    case 'yearly':
      from = now.subtract(364,'day').format('YYYY-MM-DD');
      to = now.format('YYYY-MM-DD');
      rangeMode='date';
      break;
    default: 
      from = dayjs(fromDateInput.value).format('YYYY-MM-DD');
      to = dayjs(toDateInput.value).format('YYYY-MM-DD');
      rangeMode='date';
  }

  console.log('Resolved Range (local):', {from, to, rangeMode});
  return {from, to, rangeMode};
}

async function loadDashboard(){
  const type = rangeSelect.value;
  const {from,to,rangeMode} = resolveRange(type);

  const data = await fetchData(from,to);
  updateLatestList(data);

  const agg = calculateStockFlow(data, rangeMode==='hour'?100:0, rangeMode, from, to);

  if(agg.labels.length === 0){
    createChart(['No Data'], [0], [0]);
  } else {
    createChart(agg.labels, agg.added, agg.used);
  }
}

rangeSelect.addEventListener('change',()=>{
  if(rangeSelect.value==='custom'){
    fromDateInput.style.display='inline-block';
    toDateInput.style.display='inline-block';
  } else{
    fromDateInput.style.display='none';
    toDateInput.style.display='none';
  }
});

window.addEventListener('load', () => {
  loadDashboard();
  setInterval(loadDashboard, 900000); // refresh every 15 mins
});
</script>


</body>
</html>
