<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feed Usage History</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root{
  --glass: rgba(255,255,255,0.14);
  --border: rgba(255,255,255,0.25);
  --accent: #22c55e;
}

body{
  margin:0;
  font-family:'Poppins',sans-serif;
  background: linear-gradient(135deg,#0f2027,#203a43,#2c5364);
  color:#fff;
  display:flex;
}

/* -------- SIDEBAR -------- */
.sidebar{
  width:240px;
  height:100vh;
  background:rgba(0,0,0,0.4);
  backdrop-filter: blur(12px);
  padding-top:25px;
  position:fixed;
}

.sidebar-title{
  text-align:center;
  font-size:22px;
  font-weight:600;
  margin-bottom:30px;
}

.menu-item{
  display:block;
  padding:15px 25px;
  color:#fff;
  font-size:17px;
  text-decoration:none;
  transition:.3s;
}

.menu-item:hover{background:rgba(255,255,255,0.15);}
.menu-item.active{background:rgba(255,255,255,0.3);}

/* -------- MAIN -------- */
.page-content{
  margin-left:260px;
  width:100%;
  padding:25px;
}

h1{
  text-align:center;
  font-size:34px;
  font-weight:600;
  margin-bottom:18px;
}

/* -------- DROPDOWN -------- */
.dropdown-box{
  text-align:center;
  margin-bottom:20px;
}

select{
  padding:10px 18px;
  border-radius:10px;
  border:none;
  font-size:17px;
  outline:none;
}

/* -------- KPI TOTAL -------- */
.total-box{
  margin:25px auto;
  max-width:400px;
  background:linear-gradient(135deg,#16a34a,#22c55e);
  padding:18px;
  border-radius:18px;
  font-size:26px;
  font-weight:700;
  text-align:center;
  box-shadow:0 10px 30px rgba(0,0,0,0.4);
}

/* -------- GRID -------- */
.section-container{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:25px;
}

/* -------- CARD -------- */
.feed-card{
  background:var(--glass);
  border:1px solid var(--border);
  backdrop-filter: blur(10px);
  border-radius:20px;
  padding:20px;
  box-shadow:0 10px 30px rgba(0,0,0,0.25);
}

.feed-card h2{
  margin:0 0 12px 0;
  font-size:22px;
  font-weight:600;
}

/* -------- TABLE -------- */
.table-box table{
  width:100%;
  border-collapse:collapse;
}

th,td{
  padding:10px;
  border-bottom:1px solid rgba(255,255,255,0.15);
}

th{
  background:rgba(255,255,255,0.15);
  font-weight:600;
}

td{
  font-size:15px;
}

/* -------- MOBILE -------- */
@media(max-width:900px){
  .section-container{
    grid-template-columns:1fr;
  }
  .sidebar{width:180px;}
  .page-content{margin-left:190px;}
}

@media(max-width:600px){
  body{flex-direction:column;}
  .sidebar{
    width:100%;
    height:auto;
    display:flex;
    justify-content:space-around;
  }
  .page-content{
    margin:0;
  }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-title">MENU</div>
  <a href="https://sunfra.com/farm/sensor/expo_sensor/index_page_for_feed_trolly.php" class="menu-item">📌 Feed Schedule</a>
  <a href="https://sunfra.com/farm/sensor/expo_sensor/feed_trolly_history_page.php" class="menu-item active">📊 History</a>
  <a href="https://sunfra.com/farm/iot_part/feed_trolly_setting_web_page.php#" class="menu-item">⚙️ Settings</a>
</div>

<!-- MAIN -->
<div class="page-content">

<h1>Feed Usage History</h1>

<div class="dropdown-box">
  <select id="modeSelect">
    <option value="yesterday">Yesterday</option>
    <option value="weekly">Weekly</option>
    <option value="monthly">Monthly</option>
    <option value="yearly">Yearly</option>
  </select>
</div>

<div class="total-box">TOTAL USED: <span id="totalUsed">—</span> KG</div>

<div class="section-container">

  <div class="feed-card">
    <h2>📊 Feed Usage Graph</h2>
    <canvas id="historyGraph" height="200"></canvas>
  </div>

  <div class="feed-card">
    <h2>📘 Feed Usage Table</h2>
    <div class="table-box">
      <table id="feedTable"></table>
    </div>
  </div>

</div>

</div>

<script>
let chart = null;
const apiUrl = "https://sunfra.com/farm/sensor/expo_sensor/feed_trolly_history_api.php";

function loadMode(mode){
  fetch(apiUrl + "?mode=" + mode)
    .then(res => res.json())
    .then(json => {
      if(!json.success){
        alert("Failed to load history");
        return;
      }

      let labels = [];
      let values = [];

      if(mode === "yesterday"){
        json.data.forEach(row=>{
          labels.push(row.feeding_time);
          values.push(parseInt(row.feed_weight_kg));
        });
      } else {
        json.data.forEach(row=>{
          labels.push(row.label);
          values.push(parseInt(row.total));
        });
      }

      updateTable(labels, values);
      updateGraph(labels, values);
    });
}

function updateTable(labels,values){
  const table=document.getElementById("feedTable");
  table.innerHTML="<tr><th>Time</th><th>Used (KG)</th></tr>";
  labels.forEach((lbl,i)=>{
    table.innerHTML+=`<tr><td>${lbl}</td><td>${values[i]}</td></tr>`;
  });
  document.getElementById("totalUsed").textContent = values.reduce((a,b)=>a+b,0);
}

function updateGraph(labels,values){
  const ctx=document.getElementById('historyGraph').getContext('2d');
  if(chart) chart.destroy();
  chart=new Chart(ctx,{
    type:'line',
    data:{
      labels:labels,
      datasets:[{
        label:'Feed Used (KG)',
        data:values,
        borderWidth:3,
        fill:false
      }]
    },
    options:{responsive:true}
  });
}

loadMode("yesterday");

document.getElementById("modeSelect").addEventListener("change",function(){
  loadMode(this.value);
});
</script>


</body>
</html>
