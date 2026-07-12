<?php 
session_start(); 
$clientName = $_SESSION['client_name'] ?? 'Your Company';
$client_id = $_SESSION['client_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register User - <?= htmlspecialchars($clientName) ?></title>
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

    #userList {
      display: none;
    }
  </style>
</head>
<body>
<a href="https://sunfra.com/farm/test2/index.php" style="display:inline-block; background:#ffca28; color:#000; padding:10px 20px; text-decoration:none; border-radius:8px; font-weight:bold; margin-bottom:20px;">⬅ Go Back</a>

<div class="form-box">
  <h2><?= htmlspecialchars($clientName) ?> - Register New User</h2>
  <form id="userForm">
    <input type="hidden" name="client_id" value="<?= $client_id ?>">
    <input type="hidden" name="client_name" value="<?= htmlspecialchars($clientName) ?>">

    <label for="username">Username</label>
    <input type="text" name="username" id="username" required />

    <label for="password">Password</label>
    <input type="password" name="password" id="password" required />

    <label for="confirm_password">Confirm Password</label>
    <input type="password" name="confirm_password" id="confirm_password" required />

    <button type="submit">Register</button>
    <div id="msg" class="message"></div>
  </form>

  <button onclick="loadUsers()">View Registered Users</button>
</div>

<div class="form-box" id="userList">
  <h3>Registered Users (<?= htmlspecialchars($clientName) ?>)</h3>
  <table id="usersTable">
    <thead>
      <tr><th>Username</th><th>Status</th></tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  const client_id = <?= json_encode($client_id) ?>;

  document.getElementById("userForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form).entries());

    if (data.password !== data.confirm_password) {
      showMessage("Passwords do not match.", "error");
      return;
    }

    try {
      const res = await fetch("https://sunfra.com/farm/test2/login/client_user_save.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          username: data.username,
          password: data.password,
          client_id: data.client_id,
          client_name: data.client_name
        })
      });

      const result = await res.json();
      if (result.status === "success") {
        form.reset();
        showMessage(result.message, "success");
        loadUsers();
      } else {
        showMessage(result.message, "error");
      }
    } catch (err) {
      showMessage("Server error. Please try again.", "error");
    }
  });

  function showMessage(message, type) {
    const msg = document.getElementById("msg");
    msg.textContent = message;
    msg.className = "message " + type;
    msg.style.display = "block";
    setTimeout(() => msg.style.display = "none", 4000);
  }

  async function loadUsers() {
    try {
      const res = await fetch("https://sunfra.com/farm/test2/login/farm_users_json.php");
      const data = await res.json();
      const tbody = document.querySelector("#usersTable tbody");
      tbody.innerHTML = "";

      if (data.status === "success") {
        const users = data.users.filter(user => user.client_id == client_id);
        if (users.length > 0) {
          users.forEach(user => {
            const row = `<tr><td>${user.username}</td><td>${user.status}</td></tr>`;
            tbody.innerHTML += row;
          });
          document.getElementById("userList").style.display = "block";
        } else {
          document.getElementById("userList").style.display = "none";
          showMessage("No users found for this client.", "error");
        }
      } else {
        showMessage("Failed to fetch users.", "error");
      }
    } catch (err) {
      showMessage("Error fetching user data.", "error");
    }
  }
</script>

</body>
</html>
