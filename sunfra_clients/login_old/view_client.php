<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Client</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff8e1;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
    }

    .container-box {
      background: #fffbea;
      border: 4px dashed #fbc02d;
      border-radius: 16px;
      max-width: 400px;
      width: 100%;
      margin: 20px 0;
      padding: 30px 25px 25px;
      position: relative;
    }

    .container-box:first-of-type::before {
      content: "🥚";
      font-size: 40px;
      position: absolute;
      top: -25px;
      left: 50%;
      transform: translateX(-50%);
      background: #fffbea;
      padding: 5px;
      border-radius: 50%;
      border: 2px solid #fbc02d;
    }

    h2 {
      text-align: center;
      color: #f57f17;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #444;
    }

    input {
      width: 90%;
      padding: 10px;
      margin-bottom: 8px;
      border: 2px solid #ffe082;
      border-radius: 6px;
      font-size: 14px;
      background-color: #fffdf4;
    }

    input:focus {
      border-color: #fbc02d;
      outline: none;
    }

    button {
      background-color: #fdd835;
      color: #333;
      font-size: 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      padding: 10px;
      width: 100%;
      margin-top: 10px;
    }

    button:hover {
      background-color: #fbc02d;
    }

    .error {
      color: red;
      font-size: 13px;
      margin: 5px 0;
    }

    #resultTable {
      margin-top: 20px;
      display: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    th, td {
      padding: 8px;
      border: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: #ffb300;
      color: #fff;
    }

    tr:nth-child(even) {
      background-color: #fdf6d9;
    }
  </style>
</head>
<body>

<div class="container-box">
  <h2>Register Client</h2>
  <form id="clientForm">
    <label>Client ID</label>
    <input type="number" name="client_id" required>
    <div class="error" id="errorMsg"></div>


    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Company Name</label>
    <input type="text" name="company_name" required>
    
      <button type="submit">Register</button>
    <div id="errorMsg" class="error"></div>
    </form>

  <button onclick="fetchClients()">View Registered Clients</button>
</div>

<div class="container-box" id="resultTable">
  <h2>Registered Clients</h2>
  <table id="clientsTable">
    <thead>
      <tr>
        <th>Client ID</th>
        <th>Username</th>
        <th>Company</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  document.getElementById("clientForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    const res = await fetch("register_client.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const result = await res.json();
    const errorDiv = document.getElementById("errorMsg");

    if (result.status === "success") {
      errorDiv.style.color = "green";
      errorDiv.textContent = result.message;
      e.target.reset();
    } else {
      errorDiv.style.color = "red";
      errorDiv.textContent = result.message;
    }
  });

async function fetchClients() {
  const res = await fetch("get_clients.php");
  const data = await res.json();

  const tbody = document.querySelector("#clientsTable tbody");
  tbody.innerHTML = "";

for (const clientId in data) {
  data[clientId].forEach(row => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${row.client_id}</td>
      <td>${row.username}</td>
      <td>${row.company_name}</td>
      <td>${row.status}</td>
    `;
    tbody.appendChild(tr);
  });
}

  document.getElementById("resultTable").style.display = "block";
}

</script>

</body>
</html>
