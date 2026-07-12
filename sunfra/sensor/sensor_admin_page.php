<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sunfra Admin Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: radial-gradient(circle at top, #1e293b, #020617);
    color: #fff;
    padding: 40px;
}

.dashboard {
    max-width: 1200px;
    margin: auto;
}

.top-bar {
    background: linear-gradient(135deg, #6366f1, #22d3ee);
    padding: 10px;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    margin-bottom: 35px;
}

.top-bar h1 {
    margin: 0;
    font-size: 26px;
    font-weight: 600;
}

.load-btn {
    padding: 12px 26px;
    border: none;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    background: linear-gradient(135deg, #facc15, #fde047);
}

.add-btn {
    padding: 12px 26px;
    border-radius: 30px;
    border: none;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #22c55e, #16a34a);
}

.table-card {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.5);
    margin-bottom: 40px;
}


.table-header, .row {
    display: grid;
    grid-template-columns: 120px 1fr 1.5fr 160px;
    align-items: center;
}

.table-header {
    opacity: 0.6;
    margin-bottom: 10px;
}

.row {
    padding: 15px;
    border-radius: 15px;
    background: rgba(255,255,255,0.05);
    margin-bottom: 10px;
}


.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th, .data-table td {
    padding: 14px;
    text-align: left;
}

.data-table th {
    cursor: pointer;
    background: rgba(255,255,255,0.1);
}

.data-table tr:hover {
    background: rgba(255,255,255,0.07);
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    font-size: 12px;
}

.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: none;
    justify-content: center;
    align-items: center;
}

.modal-box {
    background: rgba(255,255,255,0.1);
    padding: 30px;
    border-radius: 20px;
    width: 350px;
}

.modal-box input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: none;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
</head>

<body>

<div class="dashboard">

<div class="top-bar">
    <h1>Sunfra Admin Panel</h1>
    <div style="display:flex;gap:10px;">
        <button class="load-btn" onclick="toggleClients()">View Clients</button>
        <button class="add-btn" onclick="openModal()">Add New Data</button>
    </div>
</div>

<div class="table-card" id="tableCard" style="display:none;">
    <div class="table-header">
        <div>CLIENT ID</div>
        <div>USERNAME</div>
        <div>COMPANY</div>
        <div>STATUS</div>
    </div>
    <div id="tableBody"></div>
</div>

<div class="table-card">
    <h2>Sensor MAC Data</h2>

    <input type="text" id="searchInput" placeholder="Search..."
        onkeyup="filterTable()"
        style="padding:10px;border-radius:20px;border:none;width:260px;margin-bottom:15px;">

    <table class="data-table">
        <thead>
            <tr>
                <th onclick="sortTable('client_id')">Client ID</th>
                <th onclick="sortTable('mac_address')">MAC Address</th>
                <th onclick="sortTable('mac_address_name')">Name</th>
                <th onclick="sortTable('type')">Type</th>
                <th onclick="sortTable('date')">Date</th>
            </tr>
        </thead>
        <tbody id="sensorTableBody"></tbody>
    </table>
</div>

</div>

<div class="modal" id="addModal">
	<div class="modal-box">
		<h3>Add New Sensor</h3>
		<input id="client_id" placeholder="Client ID">
		<input id="mac_address" placeholder="MAC Address">
		<select id="type" style="width:100%;padding:12px;margin-bottom:15px;border-radius:10px;border:none;">
			<option value="">Select Sensor Type</option>
			<option value="Silo Monitoring">Silo Monitoring</option>
			<option value="Water Flow Monitoring">Water Flow Monitoring</option>
			<option value="Temperature Monitoring">Temperature Monitoring</option>
			<option value="Water Level Monitoring">Water Level Monitoring</option>
		</select>

		<input id="mac_address_name" placeholder="Sensor Name">
		<div class="modal-actions">
			<button onclick="closeModal()">Cancel</button>
			<button onclick="saveMacData()">Save</button>
		</div>
	</div>
</div>

<script>
let macData = [];
let sortOrder = {};
let dataLoaded = false;
let visible = false;

document.addEventListener("DOMContentLoaded", loadMacData);

function loadMacData() {
    fetch("https://sunfra.com/farm/sunfra/sensor/mac_data_json.php")
        .then(res => res.json())
        .then(res => {
            macData = res.data || [];
            renderSensorTable(macData);
        });
}

function renderSensorTable(data) {
    const body = document.getElementById("sensorTableBody");
    body.innerHTML = "";
    data.forEach(d => {
        body.innerHTML += `
            <tr>
                <td>#${d.client_id}</td>
                <td>${d.mac_address}</td>
                <td>${d.mac_address_name}</td>
                <td>${d.type}</td>
                <td><span class="badge">${d.date}</span></td>
            </tr>`;
    });
}

function sortTable(key) {
    sortOrder[key] = !sortOrder[key];
    macData.sort((a,b)=>sortOrder[key]
        ? a[key].localeCompare(b[key])
        : b[key].localeCompare(a[key]));
    renderSensorTable(macData);
}

function filterTable() {
    const v = searchInput.value.toLowerCase();
    renderSensorTable(macData.filter(d =>
        Object.values(d).join(" ").toLowerCase().includes(v)
    ));
}

function toggleClients() {
    const card = tableCard;
    if (!dataLoaded) {
        fetch("https://sunfra.com/farm/sunfra/login/sunfra_client_json.php")
            .then(r=>r.json())
            .then(data=>{
                tableBody.innerHTML="";
                Object.values(data).flat().forEach(c=>{
                    tableBody.innerHTML+=`
                    <div class="row">
                        <div>#${c.client_id}</div>
                        <div>${c.username}</div>
                        <div>${c.company_name}</div>
                        <div>${c.status}</div>
                    </div>`;
                });
                dataLoaded=true;
                card.style.display="block";
            });
        return;
    }
    card.style.display = card.style.display==="none"?"block":"none";
}

function openModal(){ addModal.style.display="flex"; }
function closeModal(){ addModal.style.display="none"; }

function saveMacData() {
    if (!client_id.value || !mac_address.value || !type.value || !mac_address_name.value) {
        alert("Please fill all fields");
        return;
    }

    const url = `https://sunfra.com/farm/sunfra/sensor/mac_data_save.php?client_id=${client_id.value}&mac_address=${mac_address.value}&type=${type.value}&mac_address_name=${mac_address_name.value}`;

    fetch(url).then(()=>{
        closeModal();
        loadMacData();
    });
}


addModal.onclick=e=>{ if(e.target===addModal) closeModal(); }

</script>

</body>
</html>
