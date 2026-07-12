<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$client_id = $_SESSION['client_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Feed Material</title>
  <script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
		  font-family: 'Inter', sans-serif;
		  background-color: #ADD8E6;
		  color: #333;
		  margin: 0;
		  padding: 0;
		}
		.container {
		  max-width: 1200px;
		  margin: auto;
		  padding: 1.5rem;
		}
		.header {
		  display: flex;
		  justify-content: space-between;
		  align-items: flex-start;
		  flex-wrap: wrap;
		  margin-bottom: 2rem;
		}
		.header h1 {
		  font-size: 2.2rem;
		  font-weight: 700;
		  color: #1f2937;
		  margin: 0;
		  display: flex;
		  align-items: center;
		  gap: 0.5rem;
		}
		.header p {
		  color: #6b7280;
		  font-size: 0.875rem;
		}

		.controls {
		  display: flex;
		  flex-direction: column;
		  gap: 0.75rem;
		  margin-bottom: 1.5rem;
		}
		@media (min-width: 640px) {
		  .controls {
			flex-direction: row;
			align-items: stretch;
		  }
		}

		.controls input,
		.controls select,
		.controls a.add-btn {
		  padding: 0.75rem;
		  font-size: 1rem;
		  border: 1px solid #d1d5db;
		  border-radius: 0.5rem;
		  width: 100%;
		  box-sizing: border-box;
		  height: 100%;
		  display: flex;
		  align-items: center;
		  justify-content: center;
		  text-align: center;
		}

		.add-btn {
		  background-color: #4CAF50;
		  color: white;
		  font-weight: 500;
		  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		  text-decoration: none;
		  border: none;
		}
		.add-btn:hover {
		  background-color: #1d4ed8;
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

		.card-grid {
		  display: grid;
		  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
		  gap: 1.5rem;
		}
		.card {
		  background-color: #fff;
		  border: 1px solid #e5e7eb;
		  border-radius: 0.75rem;
		  padding: 1.25rem;
		  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
		  transition: transform 0.2s ease;
		}
		.card:hover {
		  transform: translateY(-4px);
		}
		.card h2 {
		  font-size: 1.25rem;
		  font-weight: 600;
		  margin: 0;
		}
		.material-name {
		  color: #4f46e5;
		}
		.badge {
		  padding: 0.25rem 0.5rem;
		  font-size: 0.75rem;
		  border-radius: 9999px;
		  font-weight: 500;
		  display: inline-block;
		}
		.water { background-color: #dbeafe; color: #1e40af; }
		.feed-med { background-color: #d1fae5; color: #065f46; }
		.raw { background-color: #fef3c7; color: #92400e; }
		.empty {
		  text-align: center;
		  color: #6b7280;
		  margin-top: 2rem;
		}.modal-overlay {
		  position: fixed;
		  top: 0; left: 0;
		  width: 100%; height: 100%;
		  background-color: rgba(0, 0, 0, 0.5);
		  display: none;
		  justify-content: center;
		  align-items: center;
		  z-index: 9999;
		}

		.modal {
		  background: #ffffff;
		  border-radius: 12px;
		  padding: 1.5rem;
		  width: 90%;
		  max-width: 400px;
		  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
		  animation: fadeIn 0.3s ease;
		}

		.modal h2 {
		  margin-top: 0;
		  color: #2563eb;
		  font-size: 1.5rem;
		  text-align: center;
		  margin-bottom: 1rem;
		}

		.form-grid p {
		  display: flex;
		  flex-direction: column;
		  margin-bottom: 1rem;
		}

		.form-grid label {
		  margin-bottom: 0.3rem;
		  font-weight: 600;
		  color: #374151;
		}

		.form-grid input,
		.form-grid select {
		  padding: 0.75rem;
		  border-radius: 8px;
		  border: 1.5px solid #d1d5db;
		  font-size: 1rem;
		  transition: border-color 0.3s;
		}

		.form-grid input:focus,
		.form-grid select:focus {
		  border-color: #2563eb;
		  outline: none;
		  background-color: #f0f9ff;
		}

		.modal-actions {
		  display: flex;
		  justify-content: space-between;
		  margin-top: 1.5rem;
		}

		.submit-btn {
		  background-color: #10b981;
		  color: white;
		  padding: 0.5rem 1.2rem;
		  border: none;
		  border-radius: 8px;
		  font-weight: 600;
		  cursor: pointer;
		}

		.cancel-btn {
		  background-color: #f87171;
		  color: white;
		  padding: 0.5rem 1rem;
		  border: none;
		  border-radius: 8px;
		  font-weight: 500;
		  cursor: pointer;
		}

		.form-message {
		  margin-top: 1rem;
		  font-size: 0.95rem;
		  text-align: center;
		  color: green;
		}

		@keyframes fadeIn {
		  from { opacity: 0; transform: translateY(-10px); }
		  to { opacity: 1; transform: translateY(0); }
		}.sidebar {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 250px;
		  height: 100vh;
		  background: #0d6efd;
		  color: #fff;
		  padding: 20px 10px;
		  transition: width 0.3s;
		  overflow-y: auto;
		  z-index: 1050;
		}

		.sidebar.collapsed {
		  width: 50px !important;
		  padding: 20px 0 !important;
		}

		.sidebar.collapsed .sidebar-text {
		  display: none !important;
		}

		.main-content {
		  margin-left: 250px;
		  transition: margin-left 0.3s;
		}

		.main-content.collapsed {
		  margin-left: 50px;
		}

		@media (max-width: 768px) {
		  .sidebar {
			position: fixed;
			left: 0; top: 0;
			height: 100vh;
			width: 250px;
			transform: translateX(-100%);
			transition: transform 0.3s;
			z-index: 1100;
			background: #0d6efd;
		  }
		  .sidebar.show {
			transform: translateX(0);
		  }
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
		}@media (max-width: 768px) {
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }
		}

  </style>
</head>
<body>
<div>
<aside id="sidebar" class="sidebar bg-blue-800 text-white p-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-xl font-semibold sidebar-text"><?= htmlspecialchars($clientName) ?></h2>
		<button id="collapse-btn" class="text-white">
		  <i class="fas fa-angle-double-left"></i>
		</button>
      </div>

      <nav class="space-y-2">
         <a href="https://sunfra.com/farm/test/test_dashboard.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-home"></i> <span class="sidebar-text">Home</span>
        </a>
        <a href="https://sunfra.com/farm/test/batch_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-globe"></i> <span class="sidebar-text">Batch</span>
        </a>
        <a href="https://sunfra.com/farm/test/weighbridge_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-truck"></i> <span class="sidebar-text">WeighBridge</span>
        </a>
        <a href="https://sunfra.com/farm/test/tractor_production_mortality_json_to_web.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-tractor"></i> <span class="sidebar-text">Tractor Production Mortality</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_attendance.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-check"></i> <span class="sidebar-text">Attendance</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_shead_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-user-tie"></i> <span class="sidebar-text">Shead Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-warehouse"></i> <span class="sidebar-text">Feed Plant Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/egg_godown/egg_godown.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-egg"></i> <span class="sidebar-text">Egg Godown Supervisor</span>
        </a>
        <a href="https://sunfra.com/farm/test/test_show_profit_loss_details.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-chart-line"></i> <span class="sidebar-text">Profit And Loss</span>
        </a>
        <a href="https://sunfra.com/farm/test/settings.php" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-sliders-h"></i> <span class="sidebar-text">Feature Settings</span>
        </a>
        <a href="https://sunfra.com" class="flex items-center gap-3 p-2 rounded hover:bg-blue-600">
          <i class="fas fa-life-ring"></i> <span class="sidebar-text">Support</span>
        </a>
        <a href="https://sunfra.com/farm/test/logout.php" class="flex items-center gap-3 p-2 rounded hover:bg-red-600">
          <i class="fas fa-sign-out-alt"></i> <span class="sidebar-text">Logout</span>
        </a>
      </nav>
    </aside>
	<main class="main-content">
  <div class="container">
    <div class="header">
	<a href="https://sunfra.com/farm/test/test_show_feed_plant_supervisor.php" class="back-button">← Go Back</a>
      <div>
        <h1>🌾Feed Material</h1>
      </div>
	  
    </div>

    <div class="controls">
	  <input id="searchBox" type="text" placeholder="🔍 Search by name...">
	  <select id="typeFilter">
		<option value="All">All Types</option>
		<option value="Water Medicine">💧 Water Medicine</option>
		<option value="Feed Medicine">💊 Feed Medicine</option>
		<option value="Raw Material">🌿 Raw Material</option>
	  </select>
		<a href="#" class="add-btn" onclick="openModal()">➕ Add New</a>

		<div id="modalOverlay" class="modal-overlay">
		  <div class="modal">
			<h2>Add New Material</h2>
			<form id="materialForm" class="form-grid">
			  <p>
				<label for="name">Name</label>
				<input type="text" name="name" id="name" required />
			  </p>
			  <p>
				<label for="stock">Stock</label>
				<input type="number" name="stock" id="stock" required />
			  </p>
			  <p>
				<label for="metric">Metric</label>
				<select name="metric" id="metric" required>
				  <option value="">Select Metric</option>
				  <option value="KG">KG</option>
				  <option value="Lit">Lit</option>
				</select>
			  </p>
			  <p>
				<label for="type">Type</label>
				<select name="type" id="type" required>
				  <option value="">Select Type</option>
				  <option value="Feed Medicine">Feed Medicine</option>
				  <option value="Water Medicine">Water Medicine</option>
				  <option value="Raw Material">Raw Material</option>
				</select>
			  </p>
			  <div class="modal-actions">
				<button type="submit" class="submit-btn">✅ Submit</button>
				<button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
			  </div>
			</form>
			<div id="formMessage" class="form-message"></div>
		  </div>
		</div>

	</div>

    <div id="cardContainer" class="card-grid"></div>
    <div id="emptyMessage" class="empty" style="display: none;">😕 No materials found.</div>
  </div>
</main>
	</div>
  <script>
    const clientId = <?= json_encode($client_id); ?>;

    const apiUrl = "https://sunfra.com/farm/test/feedrawmaterial/feed_raw_material_json.php";
    let allMaterials = [];

    const cardContainer = document.getElementById("cardContainer");
    const searchBox = document.getElementById("searchBox");
    const typeFilter = document.getElementById("typeFilter");
    const emptyMessage = document.getElementById("emptyMessage");

    function getBadgeClass(type) {
      if (type === "Water Medicine") return "badge water";
      if (type === "Feed Medicine") return "badge feed-med";
      if (type === "Feed Raw Material") return "badge raw";
      return "badge";
    }

    async function fetchData() {
	  try {
		const res = await fetch(apiUrl);
		const json = await res.json();

		console.log("Client ID:", clientId); // ✅ DEBUG
		console.log("Full JSON data:", json); // ✅ DEBUG
		console.log("Data for this client:", json[clientId]); // ✅ DEBUG

		allMaterials = json[String(clientId)] || [];
		renderCards();
	  } catch (err) {
		console.error("❌ Fetch error:", err);
		cardContainer.innerHTML = "<p style='color:red;'>❌ Failed to load data</p>";
	  }
	}

    function renderCards() {
      const query = searchBox.value.toLowerCase();
      const selectedType = typeFilter.value;

      const filtered = allMaterials.filter(item => {
        const matchesType = selectedType === "All" || item.type === selectedType;
        const matchesSearch = item.name.toLowerCase().includes(query);
        return matchesType && matchesSearch;
      });

      cardContainer.innerHTML = "";
      emptyMessage.style.display = filtered.length ? "none" : "block";

      filtered.forEach(item => {
        const card = document.createElement("div");
        card.className = "card";
        card.innerHTML = `
          <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom: 10px;">
            <div>
              <h2 class="material-name">${item.name}</h2>
            </div>
            <span class="${getBadgeClass(item.type)}">${item.type}</span>
          </div>
          <p><strong>Stock:</strong> ${parseFloat(item.stock).toFixed(2)} ${item.metric}</p>
          <p><strong>Days Remaining:</strong> ${parseFloat(item.days).toFixed(1)} days</p>
        `;
        cardContainer.appendChild(card);
      });
    }

    searchBox.addEventListener("input", renderCards);
    typeFilter.addEventListener("change", renderCards);
    fetchData();
	 function openModal() {
		document.getElementById("modalOverlay").style.display = "flex";
		document.getElementById("formMessage").innerText = "";
	  }

	  function closeModal() {
		document.getElementById("modalOverlay").style.display = "none";
		document.getElementById("materialForm").reset();
	  }

	  document.getElementById("materialForm").addEventListener("submit", async function (e) {
		e.preventDefault();

		const form = e.target;
		const formData = new FormData(form);
		const payload = Object.fromEntries(formData.entries());
		payload.client_id = clientId;

		const response = await fetch("https://sunfra.com/farm/test/feedrawmaterial/feed_raw_material_save.php", {
		  method: "POST",
		  body: JSON.stringify(payload),
		  headers: {
			"Content-Type": "application/json"
		  }
		});

		const result = await response.json();

		const messageBox = document.getElementById("formMessage");
		if (result.status === "success") {
		  messageBox.style.color = "green";
		  messageBox.innerText = "✅ Material added successfully!";
		  setTimeout(() => {
			closeModal();
			location.reload();
		  }, 1000);
		} else {
		  messageBox.style.color = "red";
		  messageBox.innerText = "❌ Failed to add material.";
		}
	  });

	  window.addEventListener("keydown", (e) => {
		if (e.key === "Escape") closeModal();
	  });
	  document.addEventListener('DOMContentLoaded', function() {
			  const sidebar = document.getElementById('sidebar');
			  const mainContent = document.querySelector('.main-content');
			  const collapseBtn = document.getElementById('collapse-btn');

			  collapseBtn?.addEventListener('click', function () {
				sidebar.classList.toggle('collapsed');
				mainContent.classList.toggle('collapsed');
				const icon = this.querySelector('i');
				if (icon) {
				  icon.classList.toggle('fa-angle-double-left');
				  icon.classList.toggle('fa-angle-double-right');
				}
			  });

			  menuBtn?.addEventListener('click', function () {
				sidebar.classList.toggle('show');
			  });
			});
	  </script>
</body>
</html>
