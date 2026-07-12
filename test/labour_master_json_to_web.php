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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sunfra Labour Master with Pagination</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
	/* General Body Styles - Keep these as they are for the overall page */
		body {
		  font-family: 'Roboto', sans-serif;
		  background-color: #e0e7ff;
		  margin: 0;
		  padding: 20px;
		  /* Ensure text-align: center applies to content outside main-content if desired */
		}

		h1, h2 {
		  text-align: center;
		  color: #333;
		  margin-bottom: 10px;
		}

		/* Top Bar and Buttons */
		.top-bar {
		  display: flex;
		  justify-content: space-between; /* Changed to space-between for better distribution */
		  align-items: center;
		  gap: 10px;
		  margin-bottom: 20px;
		  flex-wrap: wrap; /* Allows items to wrap on smaller screens */
		}

		.search-box {
		  padding: 10px;
		  border: 1px solid #ccc;
		  border-radius: 8px;
		  font-size: 16px;
		  width: 250px; /* Base width */
		  max-width: 100%; /* Ensure it doesn't overflow */
		  flex-shrink: 1;
		}

		.add-button, .home-button { /* Combined for similar styling */
		  padding: 10px 16px;
		  background-color: #10b981; /* Add button color */
		  color: white;
		  text-decoration: none;
		  border-radius: 8px;
		  font-weight: bold;
		  white-space: nowrap;
		  transition: background-color 0.3s;
		  flex-shrink: 0; /* Prevents shrinking */
		  display: inline-block; /* Ensure padding applies correctly */
		}

		.add-button:hover, .home-button:hover {
		  background-color: #059669;
		}

		.home-button {
		  background-color: #3b82f6; /* Home button specific color */
		}

		.home-button:hover {
		  background-color: #2563eb;
		}

		/* Specific back button style */
		.home-button[style*="background-color: rgb(107, 114, 128)"] {
		  background-color: #6b7280 !important;
		}

		.home-button[style*="background-color: rgb(107, 114, 128)"]:hover {
		  background-color: #4b5563 !important; /* Darker gray on hover */
		}


		/* Grid layout for cards */
		.grid {
		  display: grid;
		  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
		  gap: 15px;
		}

		/* Card styling */
		.card {
		  padding: 15px;
		  border-radius: 12px;
		  color: white;
		  cursor: pointer;
		  transition: transform 0.3s, box-shadow 0.3s;
		  position: relative;
		  overflow: hidden;
		  word-wrap: break-word;
		}

		.card:hover {
		  transform: translateY(-5px);
		  box-shadow: 0 8px 12px rgba(0,0,0,0.15);
		}

		.card-header {
		  display: flex;
		  justify-content: space-between;
		  align-items: center;
		}

		.card h3 {
		  margin: 0;
		  font-size: 18px;
		}

		.edit-btn {
		  padding: 5px 10px;
		  background: rgba(255, 255, 255, 0.3);
		  color: white;
		  border: none;
		  border-radius: 5px;
		  font-size: 12px;
		  cursor: pointer;
		}

		.edit-btn:hover {
		  background: rgba(255, 255, 255, 0.5);
		}

		.card p {
		  margin: 8px 0 0;
		  font-size: 14px;
		}

		.details {
		  max-height: 0;
		  overflow: hidden;
		  transition: max-height 0.4s ease-out, opacity 0.4s ease-out;
		  opacity: 0;
		  margin-top: 10px;
		  font-size: 13px;
		  background-color: rgba(255, 255, 255, 0.2);
		  padding: 8px;
		  border-radius: 8px;
		}

		.card.active .details {
		  max-height: 500px; /* Adjusted to allow content expansion */
		  opacity: 1;
		}

		/* Pagination */
		.pagination {
		  display: flex;
		  justify-content: center;
		  margin-top: 20px;
		  gap: 8px;
		  flex-wrap: wrap;
		}

		.pagination button {
		  padding: 8px 12px;
		  background-color: #3b82f6;
		  color: white;
		  border: none;
		  border-radius: 6px;
		  cursor: pointer;
		}

		.pagination button.active-page {
		  background-color: #2563eb;
		  font-weight: bold;
		}

		.pagination button:disabled {
		  background-color: #a5b4fc;
		  cursor: not-allowed;
		}

		/* Modal Overlay (for the form) */
		#formModalOverlay {
		  position: fixed;
		  top: 0;
		  left: 0;
		  width: 100%;
		  height: 100%;
		  background-color: rgba(0,0,0,0.6);
		  display: none; /* Hidden by default, shown by JS */
		  justify-content: center;
		  align-items: center;
		  z-index: 1000;
		  /* Ensure it covers everything without horizontal scroll */
		  overflow-y: auto; /* Allows scrolling within modal if content is long */
		  -webkit-overflow-scrolling: touch; /* For smoother scrolling on iOS */
		}

		/* Modal Content */
		.form-modal-content {
		  background: white;
		  padding: 20px; /* Reduced padding to give more space */
		  border-radius: 15px;
		  width: 95%; /* Use percentage for responsiveness */
		  max-width: 500px; /* Max width for larger screens */
		  box-shadow: 0 10px 20px rgba(0,0,0,0.3);
		  animation: fadeIn 0.3s ease;
		  position: relative;
		  box-sizing: border-box; /* Include padding in width calculation */
		  margin: 20px auto; /* Add vertical margin for small screens if content pushes it */
		}

		/* Animation for modal */
		@keyframes fadeIn {
		  from { opacity: 0; transform: translateY(-20px); }
		  to { opacity: 1; transform: translateY(0); }
		}

		/* Close Button (X) */
		.close-btn {
		  position: absolute;
		  top: 10px; /* Adjusted position */
		  right: 10px; /* Adjusted position */
		  background: #ef4444;
		  color: white;
		  border: none;
		  border-radius: 5px;
		  padding: 5px 10px;
		  cursor: pointer;
		  font-size: 14px; /* Ensure readability */
		  z-index: 10; /* Ensure it's above other content if necessary */
		}

		.close-btn:hover {
		  background: #dc2626;
		}

		.form-group {
		  margin-bottom: 20px;
		  position: relative;
		}

		.form-group label {
		  position: absolute;
		  top: -10px;
		  left: 10px;
		  background: white;
		  padding: 0 5px;
		  font-size: 12px;
		  color: #555;
		  z-index: 1; /* Ensure label is above input field border */
		}

		.form-group input,
		.form-group textarea,
		.form-group select {
		  width: 100%; /* Full width of its parent */
		  padding: 12px 10px;
		  border: 2px solid #ddd;
		  border-radius: 8px;
		  outline: none;
		  transition: 0.3s;
		  background: #f9fafb;
		  box-sizing: border-box; /* Include padding and border in element's total width and height */
		}

		.form-group input:focus,
		.form-group textarea:focus,
		.form-group select:focus {
		  border-color: #4facfe;
		  background: #fff;
		}

		/* Submit and Delete Buttons in Modal */
		.submit-btn {
		  width: 100%;
		  padding: 12px;
		  border: none;
		  border-radius: 8px;
		  background: #10b981;
		  color: white;
		  font-size: 16px;
		  font-weight: bold;
		  cursor: pointer;
		  transition: 0.3s;
		  margin-top: 10px; /* Spacing from last form group */
		}

		.submit-btn:hover {
		  background: #059669;
		}

		button[type="button"].submit-btn { /* For delete button if you add one */
		  background-color: #ef4444;
		}

		button[type="button"].submit-btn:hover {
		  background-color: #dc2626;
		}

		/* Success & Error Messages */
		.success-message,
		.error-message {
		  text-align: center;
		  margin-top: 10px;
		  font-weight: bold;
		}

		.success-message { color: green; }
		.error-message { color: red; }

		/* Sidebar - No changes needed here based on previous requests */
		.sidebar {
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

		/* Mobile Responsiveness for Modal and General Layout */
		@media (max-width: 768px) {
		  /* Sidebar adjustments */
		  .sidebar {
			transform: translateX(-100%);
			width: 250px;
		  }
		  .sidebar.show {
			transform: translateX(0);
		  }
		  .main-content, .main-content.collapsed {
			margin-left: 0 !important;
		  }

		  /* Adjust top bar layout for small screens */
		  .top-bar {
			flex-direction: column;
			align-items: stretch; /* Stretch items to fill width */
			padding: 0 10px; /* Add some horizontal padding */
		  }

		  .search-box, .add-button, .home-button {
			width: 100%; /* Full width for better touch targets */
			margin-bottom: 10px; /* Space out buttons */
			text-align: center;
		  }

		.form-modal-content {
		  max-width: 350px !important;    /* as you have */
		  width: 85% !important;
		  padding: 15px 20px !important;
		  max-height: 40vh !important;    /* limit max height to 60% of viewport height */
		  overflow-y: auto !important;    /* vertical scroll if content overflows */
		  box-sizing: border-box !important;
		}

		/* Reduce spacing in form groups */
		.form-group {
		  margin-bottom: 8px !important; /* less vertical space */
		}

		/* Smaller labels */
		.form-group label {
		  font-size: 11px !important;    /* smaller font */
		  top: -8px !important;           /* adjust label position */
		}

		/* Smaller inputs, textareas, selects */
		.form-group input,
		.form-group textarea,
		.form-group select {
		  padding: 8px 10px !important;   /* reduced padding */
		  font-size: 13px !important;     /* smaller font size */
		}

		/* Smaller submit button */
		.submit-btn {
		  padding: 10px !important;
		  font-size: 14px !important;
		}

		/* Adjust modal close button */
		.close-btn {
		  padding: 4px 8px !important;
		  font-size: 12px !important;
		  top: 8px !important;
		  right: 8px !important;
		}@media (max-width: 480px) {
		  .form-modal-content {
			max-width: 95% !important;
			padding: 12px 15px !important;
		  }
		  .form-group input,
		  .form-group textarea,
		  .form-group select {
			padding: 6px 8px !important;
			font-size: 12px !important;
		  }
		  .submit-btn {
			padding: 8px !important;
			font-size: 13px !important;
		  }
		  .close-btn {
			padding: 3px 6px !important;
			font-size: 11px !important;
			top: 6px !important;
			right: 6px !important;
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

  <h1>Sunfra Labour Master</h1>
<div class="top-bar" style="justify-content: space-between;">
  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <a href="https://sunfra.com/farm/test/test_show_attendance.php" 
       class="home-button" 
       style="background-color: #6b7280;">
       ← Back
    </a>
  </div>

  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <input type="text" class="search-box" id="searchInput" placeholder="Search...">
    <button class="add-button" onclick="showAddForm()">+ Add New Labour</button>
    <a href="https://sunfra.com/farm/test/test_dashboard.php" class="home-button">🏠 Home</a>
  </div>
</div>



	<div class="grid" id="labourGrid"></div>

	<div class="pagination" id="paginationControls"></div>
	<div id="formModalOverlay" onclick="modalClickClose(event)">
	  <div class="form-modal-content">
		<button class="close-btn" onclick="hideForm()">X</button>
		<h2 id="formTitle">Add / Edit Labour</h2>
		<form id="labourForm">
		  <input type="hidden" id="id" name="id">
		  <input type="hidden" id="client_id" name="client_id" value="<?php echo $_SESSION['client_id']; ?>">

		  <div class="form-group">
			<label>Name</label>
			<input type="text" id="name" name="name" required>
		  </div>
		  
		  <div class="form-group">
			<label>Date of Birth</label>
			<input type="date" id="dateOfBirth" name="dateOfBirth" required>
		  </div>
		  
		  <div class="form-group">
			<label>Address</label>
			<textarea id="address" name="address" required></textarea>
		  </div>
		  
		  <div class="form-group">
			<label>Phone Number</label>
			<input type="text" id="phoneNumber" name="phoneNumber" required>
		  </div>
		  
		  <div class="form-group">
			<label>Aadhar</label>
			<input type="text" id="aadhar" name="aadhar" required>
		  </div>
		  
		  <div class="form-group">
			<label>Joining Reference</label>
			<input type="text" id="joiningReference" name="joiningReference" required>
		  </div>
		  
		  <div class="form-group">
			<label>Related To</label>
			<input type="text" id="relatedTo" name="relatedTo" required>
		  </div>
		  
		  <div class="form-group">
			<label>Start Date</label>
			<input type="date" id="startDate" name="startDate" required>
		  </div>
		  
		  <div class="form-group">
			<label>End Date</label>
			<input type="date" id="endDate" name="endDate">
		  </div>
		  
		  <button type="submit" class="submit-btn">Submit</button>
		  <div id="responseMessage"></div>
		</form>
	  </div>
	</div>
	</main>
	</div>
  <script>
	const clientId = <?php echo json_encode($_SESSION['client_id'] ?? 0); ?>;

	const colors = [
	  '#EF476F', '#06D6A0', '#118AB2', 
	  '#8338EC', '#FF6F61', '#00B4D8', '#8E44AD'
	];

	let allData = [];
	let filteredData = [];
	let currentPage = 1;
	const pageSize = 25;

	async function loadLabourData() {
	  try {
		const response = await fetch('https://sunfra.com/farm/test/labour_master_json.php');
		const data = await response.json();

		allData = data[clientId] || [];
		filteredData = [...allData];
		displayPage();
	  } catch (error) {
		console.error('Fetch error:', error);
		document.getElementById('labourGrid').innerHTML = '<p>Error loading data.</p>';
	  }
	}

	function displayPage() {
	  const grid = document.getElementById('labourGrid');
	  grid.innerHTML = '';

	  const start = (currentPage - 1) * pageSize;
	  const end = start + pageSize;
	  const pageData = filteredData.slice(start, end);

	  pageData.forEach(item => {
		const color = colors[Math.floor(Math.random() * colors.length)];
		const card = document.createElement('div');
		card.className = 'card';
		card.style.backgroundColor = color;

		card.innerHTML = `
		  <div class="card-header">
			<h3>${item.name}</h3>
			<button class="edit-btn" onclick="showEditForm(${item.id}, event)">Edit</button>
		  </div>
		  <p>${item.address}</p>
		  <div class="details">
			<p><strong>Date of Birth:</strong> ${item.dateOfBirth}</p>
			<p><strong>Phone:</strong> ${item.phoneNumber}</p>
			<p><strong>Aadhar:</strong> ${item.aadhar}</p>
			<p><strong>Joining Reference:</strong> ${item.joiningReference}</p>
			<p><strong>Related To:</strong> ${item.relatedTo}</p>
			<p><strong>Start Date:</strong> ${item.startDate}</p>
			<p><strong>End Date:</strong> ${item.endDate || '-'}</p>
		  </div>
		`;

		card.onclick = function(e) {
		  if (e.target.classList.contains('edit-btn')) return;
		  card.classList.toggle('active');
		};

		grid.appendChild(card);
	  });

	  renderPaginationControls();
	}

	function renderPaginationControls() {
	  const totalPages = Math.ceil(filteredData.length / pageSize);
	  const pagination = document.getElementById('paginationControls');
	  pagination.innerHTML = '';

	  const prevBtn = document.createElement('button');
	  prevBtn.textContent = 'Previous';
	  prevBtn.disabled = currentPage === 1;
	  prevBtn.onclick = () => {
		currentPage--;
		displayPage();
	  };
	  pagination.appendChild(prevBtn);

	  for (let i = 1; i <= totalPages; i++) {
		const pageBtn = document.createElement('button');
		pageBtn.textContent = i;
		if (i === currentPage) pageBtn.classList.add('active-page');
		pageBtn.onclick = () => {
		  currentPage = i;
		  displayPage();
		};
		pagination.appendChild(pageBtn);
	  }

	  const nextBtn = document.createElement('button');
	  nextBtn.textContent = 'Next';
	  nextBtn.disabled = currentPage === totalPages;
	  nextBtn.onclick = () => {
		currentPage++;
		displayPage();
	  };
	  pagination.appendChild(nextBtn);
	}

	function filterData() {
	  const query = document.getElementById('searchInput').value.toLowerCase();
	  filteredData = allData.filter(item =>
		Object.values(item).some(value =>
		  value && value.toString().toLowerCase().includes(query)
		)
	  );
	  currentPage = 1;
	  displayPage();
	}

	document.getElementById('searchInput').addEventListener('input', filterData);

	function showAddForm() {
	  document.getElementById('formModalOverlay').style.display = 'flex';
	  document.getElementById('formTitle').innerText = 'Add New Labour';
	  document.getElementById('labourForm').reset();
	  document.getElementById('id').value = '';
	}

	function showEditForm(id, event) {
	  event.stopPropagation();
	  const labour = allData.find(l => l.id == id);
	  if (!labour) return;

	  document.getElementById('formModalOverlay').style.display = 'flex';
	  document.getElementById('formTitle').innerText = 'Edit Labour';

	  document.getElementById('id').value = labour.id;
	  document.getElementById('name').value = labour.name;
	  document.getElementById('dateOfBirth').value = labour.dateOfBirth;
	  document.getElementById('address').value = labour.address;
	  document.getElementById('phoneNumber').value = labour.phoneNumber;
	  document.getElementById('aadhar').value = labour.aadhar;
	  document.getElementById('joiningReference').value = labour.joiningReference;
	  document.getElementById('relatedTo').value = labour.relatedTo;
	  document.getElementById('startDate').value = labour.startDate;
	  document.getElementById('endDate').value = labour.endDate || '';
	}

	function hideForm() {
	  document.getElementById('formModalOverlay').style.display = 'none';
	}

	function modalClickClose(e) {
	  if (e.target.id === 'formModalOverlay') {
		hideForm();
	  }
	}

	document.getElementById('labourForm').addEventListener('submit', async function(e) {
	  e.preventDefault();
	  const formData = new FormData(this);

	  try {
		const response = await fetch('https://sunfra.com/farm/test/labour_master_save.php', {
		  method: 'POST',
		  body: formData
		});

		const result = await response.text();
		document.getElementById('responseMessage').innerText = 'Saved successfully!';
		hideForm();
		loadLabourData();
	  } catch (error) {
		console.error('Save error:', error);
		document.getElementById('responseMessage').innerText = 'Error saving data.';
	  }
	});

	loadLabourData();
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
