<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Client</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff8e1;
      padding: 20px;
      text-align: center;
    }

    .form-box {
      background: #fffbea;
      border: 3px dashed #fbc02d;
      padding: 20px;
      border-radius: 10px;
      max-width: 400px;
      margin: auto;
      margin-bottom: 30px;
    }

    input {
      width: 90%;
      margin: 8px 0;
      padding: 10px;
      border: 2px solid #ffe082;
      border-radius: 5px;
    }

    button {
      background: #fdd835;
      border: none;
      padding: 10px;
      cursor: pointer;
      border-radius: 5px;
      width: 100%;
      margin-top: 10px;
      font-weight: bold;
    }

    .message {
      margin-top: 10px;
      font-weight: bold;
      padding: 10px;
      border-radius: 5px;
      display: none;
    }

    .message.success {
      background-color: #e6ffed;
      color: #2e7d32;
      border: 1px solid #a5d6a7;
    }

    .message.error {
      background-color: #ffe6e6;
      color: #d32f2f;
      border: 1px solid #ef9a9a;
    }

    table {
      margin-top: 10px;
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 8px;
      border: 1px solid #ccc;
    }

    th {
      background: #fbc02d;
    }

    #clientList {
      display: none;
    }.sidebar {
			position: fixed;
			top: 0;
			left: 0;
			width: 70px;
			height: 100vh;
			background-color: #016795;
			display: flex;
			flex-direction: column;
			align-items: flex-start;
			padding-top: 10px;
			overflow-y: auto;
			transition: width 0.3s ease;
			z-index: 1000;
			box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
		  }
		  .sidebar.expanded {
			width: 250px;
		  }
		  .sidebar a {
			color: white;
			text-decoration: none;
			width: 100%;
			padding: 14px 20px;
			display: flex;
			align-items: center;
			font-size: 15px;
			transition: background-color 0.2s ease-in-out;
			white-space: nowrap;
		  }
		  .sidebar a:hover {
			background-color: #0194c7;
		  }
		  .sidebar i {
			font-size: 16px;
			min-width: 30px;
			text-align: center;
		  }
		  .label {
			margin-left: 10px;
			white-space: nowrap;
			display: none;
		  }
		  .sidebar.expanded .label {
			display: inline;
		  }
		  .toggle-btn {
			width: 100%;
			cursor: pointer;
			padding: 10px 20px;
			background: none;
			border: none;
			color: white;
			font-size: 18px;
			text-align: left;
			outline: none;
			user-select: none;
			display: flex;
			align-items: center;
		  }
		  .toggle-btn i {
			margin-right: 10px;
		  }
		  .attendance-submenu {
			display: none;
			flex-direction: column;
			background: #1e293b;
			width: 100%;
			padding-left: 40px;
			transition: all 0.3s ease;
		  }
		  .attendance-submenu button {
			background: none;
			border: none;
			color: white;
			text-align: left;
			padding: 10px 20px;
			font-size: 14px;
			cursor: pointer;
			transition: background-color 0.2s ease;
		  }
		  .attendance-submenu button:hover {
			background-color: #2563EB;
		  }.main-content {
			  margin-left: 250px;
			  transition: margin-left 0.3s;
			}

			.main-content.collapsed {
			  margin-left: 50px;
			}.content {
			  margin-left: 70px;
			  transition: margin-left 0.3s ease;
			}

			.sidebar.expanded ~ .content {
			  margin-left: 250px;
			}.content.expanded {
			  margin-left: 250px;
			}
  </style>
</head>
<body>
<div>
<div class="sidebar" id="sidebar">
  <button class="toggle-btn" id="sidebarToggleBtn" aria-label="Toggle menu" title="Toggle menu">
    <i class="fas fa-bars"></i>
    <span class="label">Menu</span>
  </button>
  <a href="https://sunfra.com/farm/sunfra/sensor/sensor_admin_page.php"><i class="fas fa-home"></i><span class="label">Sunfra Admin</span></a>
  <a href="https://sunfra.com/farm/sunfra/login/logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a>
</div>

<main class="content main-content">

	<a href="https://sunfra.com/farm/sunfra/index.php" style="display:inline-block; background:#ffca28; color:#000; padding:10px 20px; text-decoration:none; border-radius:8px; font-weight:bold; margin-bottom:20px;">⬅ Go Back</a>

	<div class="form-box">
	  <h2>Register New Client</h2>
	  <form id="clientForm">
		<label for="client_id">Client ID</label>
		<input type="text" name="client_id" id="client_id" required />

		<label for="username">Username</label>
		<input type="text" name="username" id="username" required />

		<label for="password">Password</label>
		<input type="password" name="password" id="password" required />

		<label for="company_name">Company Name</label>
		<input type="text" name="company_name" id="company_name" required />

		<button type="submit">Register</button>
		<div id="msg" class="message"></div>
	  </form>

	  <button onclick="loadClients()">View Registered Clients</button>
	</div>

	<div class="form-box" id="clientList">
	  <h3>Registered Clients</h3>
	  <table id="clientsTable">
		<thead>
		  <tr><th>Client ID</th><th>Username</th><th>Company Name</th><th>Status</th></tr>
		</thead>
		<tbody></tbody>
	  </table>
	</div>
	</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>

<script>
  document.getElementById("clientForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target).entries());

    try {
      const res = await fetch("https://sunfra.com/farm/sunfra/login/sunfra_client_registration_save.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      const result = await res.json();
      const msg = document.getElementById("msg");

      msg.textContent = result.message || "Registration complete.";
      msg.className = result.status === "success" ? "message success" : "message error";
      msg.style.display = "block";

      if (result.status === "success") {
        e.target.reset();
        loadClients();  
        setTimeout(() => msg.style.display = "none", 3000);
      }

    } catch (error) {
      const msg = document.getElementById("msg");
      msg.textContent = "Server error. Please try again.";
      msg.className = "message error";
      msg.style.display = "block";
    }
  });
	function loadClients() {
	  const clientListBox = document.getElementById("clientList");
	  const tbody = document.querySelector("#clientsTable tbody");
	  tbody.innerHTML = "";

	  fetch("https://sunfra.com/farm/sunfra/login/sunfra_client_json.php")
		.then(res => res.json())
		.then(data => {
		  for (const clientId in data) {
			data[clientId].forEach(client => {
			  const row = `
				<tr>
				  <td>${client.client_id}</td>
				  <td>${client.username}</td>
				  <td>${client.company_name}</td>
				  <td>${client.status}</td>
				</tr>`;
			  tbody.innerHTML += row;
			});
		  }

		  clientListBox.style.display = "block"; 
		})
		.catch(err => {
		  console.error("Error loading clients:", err);
		  alert("Failed to load client list.");
		});
	}
	const sidebar = document.getElementById('sidebar');
	const mainContent = document.querySelector('.content'); 
	const toggleBtn = document.getElementById('sidebarToggleBtn');

	toggleBtn.addEventListener('click', () => {
	  sidebar.classList.toggle('expanded');
	  mainContent.classList.toggle('expanded');  

	  const icon = toggleBtn.querySelector('i');
	  if (sidebar.classList.contains('expanded')) {
		icon.classList.remove('fa-bars');
		icon.classList.add('fa-times');
	  } else {
		icon.classList.add('fa-bars');
		icon.classList.remove('fa-times');
	  }
	});

	  function toggleAttendance() {
		toggleSubmenu('attendanceSubmenu');
	  }
	  function toggleFeedPlant() {
		toggleSubmenu('feedPlantSubmenu');
	  }
	  function toggleEggGodown() {
		toggleSubmenu('eggGodownSubmenu');
	  }
	  function toggleProfitLoss() {
		toggleSubmenu('profitLossSubmenu');
	  }
	  function toggleShed() {
		toggleSubmenu('shedSubmenu');
	  }
	  function toggleSubmenu(id) {
		const submenu = document.getElementById(id);
		if (!submenu) return;
		if (submenu.style.display === 'flex') {
		  submenu.style.display = 'none';
		} else {
		  submenu.style.display = 'flex';
		}
	  }
</script>

</body>
</html>
