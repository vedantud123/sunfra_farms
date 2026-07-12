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
    }
  </style>
</head>
<body>
<a href="https://sunfra.com/farm/sunfra_clients/index.php" style="display:inline-block; background:#ffca28; color:#000; padding:10px 20px; text-decoration:none; border-radius:8px; font-weight:bold; margin-bottom:20px;">⬅ Go Back</a>

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

<script>
  document.getElementById("clientForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target).entries());

    try {
      const res = await fetch("https://sunfra.com/farm/sunfra_clients/login/sunfra_client_registration_save.php", {
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
        loadClients();  // Refresh table after successful registration
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

	  fetch("https://sunfra.com/farm/sunfra_clients/login/sunfra_client_json.php")
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
</script>

</body>
</html>
