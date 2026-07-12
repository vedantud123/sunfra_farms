<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Feed Trolley Timings — Configuration</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: #fff;
    padding: 0;
  }

  /* SIDEBAR */
	.sidebar {
	  position: fixed;
	  left: 0;
	  top: 0;
	  height: 100vh;
	  width: 230px;
	  background: rgba(255,255,255,0.12);
	  backdrop-filter: blur(6px);
	  padding-top: 30px;
	  box-shadow: 3px 0 10px rgba(0,0,0,0.4);
	}

	.sidebar-title {
	  font-size: 22px;
	  text-align: center;
	  margin-bottom: 25px;
	  font-weight: 600;
	  color: #fff;
	}

	.menu-item {
	  display: block;
	  padding: 15px 20px;
	  font-size: 18px;
	  text-decoration: none;
	  color: #fff;
	  transition: 0.3s;
	}

	.menu-item:hover {
	  background: rgba(255,255,255,0.18);
	  transform: translateX(5px);
	  border-radius: 10px;
	}

	.menu-item.active {
	  background: rgba(255,255,255,0.25);
	  border-radius: 12px;
	}

	.page-content {
	  margin-left: 260px;
	  padding: 30px;
	}

  h1 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 32px;
    font-weight: 600;
  }

  .main-card {
    background: rgba(255,255,255,0.12);
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    backdrop-filter: blur(6px);
    max-width: 850px;
    margin: 0 auto;
  }

  .controls-box {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  input[type=time] {
    padding: 10px 15px;
    border-radius: 10px;
    border: none;
    font-size: 16px;
    background: rgba(255,255,255,0.15);
    color: #fff;
  }

  button {
    padding: 10px 16px;
    border-radius: 10px;
    border: none;
    font-size: 16px;
    cursor: pointer;
    background: #2563eb;
    color: #fff;
    transition: 0.25s;
  }

  button:hover {
    transform: scale(1.05);
  }

  button.danger {
    background: #e11d48;
  }

  button.green {
    background: #10b981;
  }

  #message div {
    margin-top: 10px;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 15px;
  }

  .list-box {
    margin-top: 20px;
  }

  .item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background: rgba(255,255,255,0.15);
    margin-bottom: 10px;
    border-radius: 12px;
    backdrop-filter: blur(4px);
  }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-title">MENU</div>
	 <a href="https://sunfra.com/farm/sensor/expo_sensor/index_page_for_feed_trolly.php" class="menu-item" onclick="setActive(this)">📌 Feed Schedule</a>
    <a href="https://sunfra.com/farm/sensor/expo_sensor/feed_trolly_history_page.php" class="menu-item" onclick="setActive(this)">📊 History</a>
    <a href="https://sunfra.com/farm/iot_part/feed_trolly_setting_web_page.php#" class="menu-item" onclick="setActive(this)">⚙️ Settings</a>
</div>

<div class="page-content">

<h1>Feed Trolley — Timings Configuration</h1>

<div class="main-card">

  <div class="controls-box">
    <input type="time" id="newTime" />
    <button id="addBtn">Add Time</button>
    <button id="refreshBtn" class="green">Refresh</button>
  </div>  
  <div id="message"></div>

  <div class="list-box" id="timingList"></div>

</div>

</div> 

<script>
const apiPath = 'feed_trolly_timing_save.php';
const createJsonPath = 'feed_trolly_timing_json.php';

function showMessage(msg, ok=true) {
  const el = document.getElementById('message');
  el.innerHTML = `
    <div style="background:${ok?'#065f46':'#7f1d1d'};color:white;">
      ${msg}
    </div>`;
  setTimeout(()=> el.innerHTML = "", 4000);
}

async function loadTimings() {
  try {
    const r = await fetch(createJsonPath);
    const payload = await r.json();

    if (!payload.success) {
      showMessage("Could not load timings", false);
      return;
    }

    renderList(payload.data);

  } catch (e) {
    showMessage("Error: " + e.message, false);
  }
}

function renderList(rows) {
  const list = document.getElementById("timingList");
  if (!rows || rows.length === 0) {
    list.innerHTML = '<div style="color:#ddd;text-align:center">No timings added yet.</div>';
    return;
  }

  list.innerHTML = "";

  rows.forEach(r => {
    const div = document.createElement("div");
    div.className = "item";

    div.innerHTML = `
      <div>
        <strong>${r.feeding_time}</strong>
        <div style="font-size:12px;color:#ddd">ID: ${r.id}</div>
      </div>
      <button class="danger delBtn" data-id="${r.id}">Remove</button>
    `;

    list.appendChild(div);
  });

  document.querySelectorAll(".delBtn").forEach(btn => {
    btn.addEventListener("click", async () => {
      const id = btn.getAttribute("data-id");
      if (!confirm("Remove this timing?")) return;

      try {
        const res = await fetch(apiPath, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "delete", id:id })
        });
        const json = await res.json();

        if (json.success) {
          showMessage("Deleted ID " + id);
          loadTimings();
        } else {
          showMessage("Delete Failed", false);
        }

      } catch (e) {
        showMessage("Error: " + e.message, false);
      }
    });
  });
}

document.getElementById("addBtn").addEventListener("click", async () => {
  const t = document.getElementById("newTime").value;
  if (!t) return showMessage("Please select a time", false);

  let timeVal = t.length === 5 ? t + ":00" : t;

  try {
    const res = await fetch(apiPath, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action:"add", feeding_time: timeVal })
    });

    const json = await res.json();
    if (json.success) {
      showMessage("Added " + timeVal);
      document.getElementById("newTime").value = "";
      loadTimings();
    } else {
      showMessage("Add Failed", false);
    }

  } catch (e) {
    showMessage("Error: " + e.message, false);
  }
});

document.getElementById("refreshBtn").addEventListener("click", loadTimings);

loadTimings();
</script>

</body>
</html>
