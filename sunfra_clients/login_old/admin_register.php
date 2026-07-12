<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register User</title>
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

<div class="form-box">
  <h2>Register New User</h2>
  <form id="userForm">
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
  <h3>Registered Users</h3>
  <table id="usersTable">
    <thead>
      <tr><th>Username</th><th>Status</th></tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  document.getElementById("userForm").addEventListener("submit", async function (e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target).entries());

    try {
      const res = await fetch("register_user_post.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      if (!res.ok) throw new Error("Server error");

      const result = await res.json();
      const msg = document.getElementById("msg");

      msg.textContent = result.message;
      msg.className = result.status === "success" ? "message success" : "message error";
      msg.style.display = "block";

      if (result.status === "success") {
        e.target.reset();
        loadUsers();
        setTimeout(() => msg.style.display = "none", 3000);
      }

    } catch (error) {
      const msg = document.getElementById("msg");
      msg.textContent = "Server error. Please try again.";
      msg.className = "message error";
      msg.style.display = "block";
    }
  });

  async function loadUsers() {
    try {
      const res = await fetch("get_registered_users.php");
      if (!res.ok) throw new Error();

      const data = await res.json();
      const tbody = document.querySelector("#usersTable tbody");
      tbody.innerHTML = "";

      for (const clientId in data) {
        data[clientId].forEach(user => {
          const row = `<tr><td>${user.username}</td><td>${user.status}</td></tr>`;
          tbody.innerHTML += row;
        });
      }

      document.getElementById("userList").style.display = "block";

    } catch (err) {
      alert("Failed to load users");
    }
  }
</script>

</body>
</html>
