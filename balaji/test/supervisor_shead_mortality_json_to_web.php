<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['client_id'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Shead Mortality</title>
  <style>
	*{
	  box-sizing: border-box;
	}
    body {
	  margin: 0;
	  padding: 0 10px;
	  font-family: 'Segoe UI', sans-serif;
	  background: linear-gradient(to right, #ADD8E6, #ADD8E6);
	  color: #333;
	}
	header {
	  background: linear-gradient(135deg, #4F46E5, #6D28D9);
	  color: white;
	  padding: 30px;
	  text-align: center;
	  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
	}

	header h1 {
	  margin: 0;
	  font-size: 32px;
	  font-weight: 600;
	  letter-spacing: 0.5px;
	}

	.container {
	  max-width: 1200px;
	  margin: 30px auto;
	  padding: 0 20px;
	}

	.filter-section {
	  display: flex;
	  justify-content: flex-end;
	  margin-bottom: 30px;
	  padding-right: 10px;
	}

	.date-filter-right {
	  display: flex;
	  align-items: center;
	  gap: 10px;
	  flex-wrap: wrap;
	}

	.date-filter-right label {
	  font-weight: 600;
	  font-size: 16px;
	  color: #4B5563;
	}

	.styled-date {
	  padding: 10px 14px;
	  border: 1px solid #cbd5e1;
	  border-radius: 10px;
	  font-size: 16px;
	  background-color: #fff;
	  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
	  transition: border-color 0.3s ease;
	}

	.styled-date:focus {
	  border-color: #4F46E5;
	  outline: none;
	  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
	}

	.card-grid {
	  display: grid;
	  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
	  gap: 25px;
	}

	.card {
	  background: linear-gradient(135deg, #ffffff, #f3f4f6);
	  border-radius: 16px;
	  padding: 24px;
	  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
	  position: relative;
	  transition: transform 0.25s ease;
	}

	.card:hover {
	  transform: translateY(-6px);
	}

	.card h3 {
	  color: #4F46E5;
	  font-size: 20px;
	  margin-bottom: 12px;
	}

	.card p {
	  margin: 6px 0;
	  color: #374151;
	  font-size: 15px;
	}

	.edit-btn {
	  position: absolute;
	  top: 20px;
	  right: 20px;
	  background-color: #10B981;
	  color: #fff;
	  padding: 6px 12px;
	  font-size: 13px;
	  border: none;
	  border-radius: 6px;
	  cursor: pointer;
	  transition: background 0.3s ease;
	}

	.edit-btn:hover {
	  background-color: #059669;
	}

	.no-data {
	  text-align: center;
	  margin-top: 40px;
	  font-size: 18px;
	  color: #888;
	}

	.add-button {
	  position: fixed;
	  bottom: 30px;
	  right: 30px;
	  background: linear-gradient(135deg, #4F46E5, #7C3AED);
	  color: white;
	  border: none;
	  padding: 16px 28px;
	  border-radius: 50px;
	  font-size: 16px;
	  font-weight: 600;
	  cursor: pointer;
	  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
	  transition: all 0.3s ease;
	  z-index: 1000;
	}

	.add-button:hover {
	  background: linear-gradient(135deg, #4338CA, #6D28D9);
	  transform: scale(1.05);
	}

	.modal {
	  display: none;
	  position: fixed;
	  z-index: 999;
	  left: 0;
	  top: 0;
	  width: 100%;
	  height: 100%;
	  backdrop-filter: blur(6px);
	  background-color: rgba(0, 0, 0, 0.4);
	}

	.modal-content {
	  background: rgba(255, 255, 255, 0.95);
	  margin: 5% auto;
	  padding: 30px;
	  border-radius: 16px;
	  max-width: 420px;
	  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
	}

	.modal-content h2 {
	  margin-top: 0;
	  margin-bottom: 20px;
	  color: #4F46E5;
	  font-size: 22px;
	}

	.modal-content select,
	.modal-content input {
	  width: 100%;
	  padding: 14px;
	  margin: 10px 0;
	  border: 1px solid #ddd;
	  border-radius: 10px;
	  font-size: 15px;
	  background: #f9fafb;
	  transition: border 0.2s;
	}

	.modal-content select:focus,
	.modal-content input:focus {
	  border-color: #7C3AED;
	  outline: none;
	}

	.modal-content select:focus {
	  border-color: #7C3AED;
	  outline: none;
	}

	.modal-content input:focus {
	  border-color: #7C3AED;
	  outline: none;
	}

	.modal-content button {
	  width: 100%;
	  padding: 14px;
	  background: linear-gradient(to right, #4F46E5, #6D28D9);
	  color: white;
	  border: none;
	  border-radius: 12px;
	  font-size: 16px;
	  font-weight: 600;
	  cursor: pointer;
	  transition: background 0.3s ease;
	}

	.modal-content button:hover {
	  background: linear-gradient(to right, #4338CA, #5B21B6);
	}

	.close {
	  float: right;
	  font-size: 24px;
	  cursor: pointer;
	  color: #999;
	}

	.close:hover {
	  color: black;
	}

	@media (max-width: 600px) {
	  .filter-section {
		flex-direction: column;
		align-items: flex-start;
	  }

	  .filter-section input {
		width: 100%;
	  }

	  .add-button {
		right: 20px;
		bottom: 20px;
		padding: 12px 24px;
		font-size: 15px;
	  }
	}.graph-section {
	  margin-top: 50px;
	  background: #fff;
	  padding: 20px;
	  border-radius: 16px;
	  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
	}

	.graph-controls {
	  display: flex;
	  justify-content: center;
	  flex-wrap: wrap;
	  gap: 10px;
	  margin-bottom: 20px;
	}

	.graph-controls button {
	  padding: 10px 16px;
	  background: linear-gradient(to right, #4F46E5, #7C3AED);
	  color: white;
	  border: none;
	  border-radius: 8px;
	  font-size: 14px;
	  cursor: pointer;
	  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
	  transition: background 0.3s ease;
	}

	.graph-controls button:hover {
	  background: linear-gradient(to right, #4338CA, #6D28D9);
	}

	.chart-wrapper {
	  max-width: 500px;
	  margin: 0 auto;
	}

	@media (max-width: 600px) {
	  #mortalityChart {
		width: 100% !important;
		height: auto !important;
	  }
	}.back-button {
	  display: inline-block;
	  margin-bottom: 1rem;
	  background-color: #3498db;
	  color: white;
	  padding: 10px 16px;
	  border-radius: 6px;
	  text-decoration: none;
	  font-weight: 600;
	  transition: background-color 0.3s ease;
	}

	.back-button:hover {
	  background-color: #217dbb;
	}
  </style>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .sidebar { transition: all 0.3s ease; }
    .sidebar a:hover { background-color: #2b6cb0; color: white; }
    .collapsed { width: 64px !important; }
    .collapsed .sidebar-text { display: none; }
    .collapsed nav a { justify-content: center; }
    @media (max-width: 768px) {
      .sidebar { display: none; }
      .sidebar.show { display: block; }
    }
  </style>
</head>
<?php session_start(); ?>
<?php $clientName = $_SESSION['client_name'] ?? 'Yours'; ?>
<body class="bg-gray-100 text-gray-800">

  <header>
  	<a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="back-button">← Go Back</a>
    <h1>Shead Mortality</h1>
  </header>

  <div class="container">
   <div class="filter-section">
	  <div class="date-filter-right">
		<label for="dateFilter">📅 Select Date:</label>
		<input type="date" id="dateFilter" class="styled-date" />
	  </div>
	</div>
   </div>

    <div class="card-grid" id="cardContainer"></div>
    <div class="no-data" id="noData" style="display: none;">No records found for selected date.</div>
  </div>

  <button class="add-button" id="addNewBtn">+ Add New</button>

  <div class="modal" id="addModal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
      <h2>Add New Record</h2>
      <form id="addForm">
	  <select id="sheadNo" required>
		<option value="">Select Shead</option>
		<option value="Shead_1">Shead_1</option>
		<option value="Shead_2">Shead_2</option>
		<option value="Shead_3">Shead_3</option>
		<option value="Shead_4">Shead_4</option>
		<option value="Shead_5">Shead_5</option>
		<option value="Shead_6">Shead_6</option>
		<option value="Shead_7">Shead_7</option>
		<option value="Shead_8">Shead_8</option>
		<option value="Chick">Chick</option>
		<option value="Grower">Grower</option>
	  </select>

	  <input type="number" id="noOfBirds" placeholder="No. of Mortality" required />
	  <button type="submit">Submit</button>
	</form>
    </div>
  </div>
  <div class="graph-section">
	  <div class="graph-controls">
		<button onclick="updateGraph('today')">Today</button>
		<button onclick="updateGraph('yesterday')">Yesterday</button>
		<button onclick="updateGraph('weekly')">Weekly</button>
		<button onclick="updateGraph('monthly')">Monthly</button>
	  </div>
	  <div class="chart-wrapper">
		<canvas id="mortalityChart"></canvas>
	  </div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
  const clientId = "<?php echo $client_id; ?>";  // Passed from PHP session

  const apiURL = "https://sunfra.com/farm/test/supervisor_shead_mortality_json.php";
  const cardContainer = document.getElementById("cardContainer");
  const dateInput = document.getElementById("dateFilter");
  const noDataDiv = document.getElementById("noData");
  const addModal = document.getElementById("addModal");
  const addForm = document.getElementById("addForm");

  document.getElementById("addNewBtn").addEventListener("click", () => {
    addModal.style.display = "block";
    addForm.reset();
    delete addForm.dataset.editId;
    addForm.querySelector("button").textContent = "Submit";
  });

  window.onclick = function (event) {
    if (event.target === addModal) {
      addModal.style.display = "none";
    }
  };

  // ✅ Fetch data only for logged-in client
  async function fetchData() {
    const response = await fetch(apiURL);
    const data = await response.json();
    return data[clientId] || [];
  }

  function createCard(item) {
    return `
      <div class="card">
        <button class="edit-btn" onclick='openEditForm(${JSON.stringify(item)})'>Edit</button>
        <h3>${item.sheadNo}</h3>
        <p><strong>Birds:</strong> ${item.noOfBirds}</p>
        <p><strong>Date:</strong> ${item.date}</p>
        <p><strong>Time:</strong> ${new Date(item.timestamp).toLocaleTimeString()}</p>
      </div>
    `;
  }

  function openEditForm(item) {
    addModal.style.display = "block";
    document.getElementById("sheadNo").value = item.sheadNo;
    document.getElementById("noOfBirds").value = item.noOfBirds;
    addForm.dataset.editId = item.id;
    addForm.querySelector("button").textContent = "Update";
  }

  async function displayCards(dateFilter = null) {
    const data = await fetchData();
    cardContainer.innerHTML = "";
    noDataDiv.style.display = "none";

    const filtered = dateFilter ? data.filter(item => item.date === dateFilter) : data;
    const topTen = filtered.slice(0, 10);

    if (topTen.length === 0) {
      noDataDiv.style.display = "block";
      return;
    }

    topTen.forEach(item => {
      cardContainer.innerHTML += createCard(item);
    });
  }

  dateInput.addEventListener("change", () => {
    const selectedDate = dateInput.value;
    displayCards(selectedDate);
  });

  addForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const payload = {
      id: addForm.dataset.editId || "",
      sheadNo: document.getElementById("sheadNo").value,
      noOfBirds: document.getElementById("noOfBirds").value,
      client_id: clientId  // ✅ Include client ID in POST request
    };

    const response = await fetch("https://sunfra.com/farm/test/supervisor_shead_mortality_save.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    if (response.ok) {
      alert(payload.id ? "Record updated!" : "New record added!");
      addModal.style.display = "none";
      addForm.reset();
      delete addForm.dataset.editId;
      displayCards(dateInput.value);
    } else {
      alert("Error saving record.");
    }
  });

  let chart;

  function parseDate(dateStr) {
    const [year, month, day] = dateStr.split('-');
    return new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
  }

  async function updateGraph(range) {
    const rawData = await fetchData();
    const now = new Date();
    let filteredData = [];

    if (range === 'today') {
      const todayStr = now.toISOString().split('T')[0];
      filteredData = rawData.filter(d => d.date === todayStr);
    } else if (range === 'yesterday') {
      const yest = new Date(now);
      yest.setDate(now.getDate() - 1);
      const yDate = yest.toISOString().split('T')[0];
      filteredData = rawData.filter(d => d.date === yDate);
    } else if (range === 'weekly') {
      const start = new Date(now);
      start.setDate(now.getDate() - 6);
      filteredData = rawData.filter(d => {
        const dDate = parseDate(d.date);
        return dDate >= start && dDate <= now;
      });
    } else if (range === 'monthly') {
      const start = new Date(now);
      start.setDate(now.getDate() - 29);
      filteredData = rawData.filter(d => {
        const dDate = parseDate(d.date);
        return dDate >= start && dDate <= now;
      });
    }

    const sheadCounts = {};
    filteredData.forEach(d => {
      sheadCounts[d.sheadNo] = (sheadCounts[d.sheadNo] || 0) + parseInt(d.noOfBirds);
    });

    const labels = Object.keys(sheadCounts).sort();
    const values = labels.map(label => sheadCounts[label]);

    if (chart) chart.destroy();

    const ctx = document.getElementById('mortalityChart').getContext('2d');
    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Mortality',
          data: values,
          backgroundColor: '#7C3AED',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#4F46E5',
            titleColor: '#fff',
            bodyColor: '#fff'
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'Shead No',
              color: '#374151'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Birds',
              color: '#374151'
            },
            beginAtZero: true
          }
        }
      }
    });
  }

  window.addEventListener("DOMContentLoaded", () => {
    const today = new Date().toISOString().split("T")[0];
    dateInput.value = today;
    displayCards(today);
    updateGraph('today');
  });
</script>


<div class="flex min-h-screen">
<?php include 'sidebar.php'; ?>
<div class="flex-1 p-4">

<div class="p-4">
</div>
</div>
</div>
</body>
</html>
