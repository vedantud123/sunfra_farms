<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Feed Trolley Schedule</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
  body { margin:0; font-family:"Poppins",sans-serif; background: linear-gradient(120deg,#16222A,#3A6073); display:flex; overflow-x:hidden; }
  .sidebar { width:240px; height:100vh; background:#1f2937; padding-top:20px; display:flex; flex-direction:column; box-shadow:2px 0 8px rgba(0,0,0,.2); }
  .sidebar-title{ color:#fff; font-size:20px; font-weight:600; text-align:center; padding:15px 0; border-bottom:1px solid #374151; margin-bottom:10px; }
  .menu-item{ padding:15px 20px; color:#d1d5db; font-size:18px; text-decoration:none; display:block; transition:.3s; }
  .menu-item:hover{ background:#374151; color:#fff; }
  .menu-item.active{ background:#4f46e5; color:#fff; font-weight:700; }
  .main-content{ flex:1; padding:20px; display:flex; justify-content:center; }
  .container{ width:100%; display:grid; grid-template-columns:repeat(2,1fr); gap:25px; max-width:1200px; }
  .card{ padding:25px; border-radius:18px; text-align:center; color:#fff; box-shadow:0 10px 25px rgba(0,0,0,.25); transition:.2s; min-height:220px; }
  .pending{ background: linear-gradient(135deg,#ff9966,#ff5e62); }
  .done{ background: linear-gradient(135deg,#00b09b,#96c93d); animation:glow 1.5s infinite alternate; }
  @keyframes glow { from{ box-shadow:0 0 10px #a2ff94 } to { box-shadow:0 0 25px #32ff7e } }
  .time{ font-size:24px; font-weight:700; margin-bottom:8px; }
  .status{ font-size:18px; font-weight:600; margin-bottom:10px; }
  .kg-value{ font-size:30px; font-weight:800; margin-top:10px; min-height:36px; opacity:0; transition:opacity .5s ease; } /* hidden until shown */
  .done .kg-value{ opacity:1; } /* fade in when done */
  .progress-box{ width:100%; height:12px; background:rgba(255,255,255,.3); border-radius:20px; margin-top:15px; overflow:hidden; }
  .progress-fill{ height:100%; width:0%; background:white; border-radius:20px; transition:width .8s ease; opacity:0.5; }
  .sub-text{ margin-top:8px; font-size:14px; opacity:.9; }
  @media (max-width:768px){ .sidebar{ width:150px } .menu-item{ font-size:16px; padding:12px 15px } .time{ font-size:22px } .kg-value{ font-size:26px } }
  @media (max-width:480px){ body{ flex-direction:column } .sidebar{ width:100%; display:flex; justify-content:space-around } .container{ grid-template-columns:1fr } .menu-item{ padding:10px; font-size:14px } }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-title">MENU</div>
  <a href="https://sunfra.com/farm/sensor/expo_sensor/index_page_for_feed_trolly.php" class="menu-item" onclick="setActive(this)">📌 Feed Schedule</a>
  <a href="https://sunfra.com/farm/sensor/expo_sensor/feed_trolly_history_page.php" class="menu-item" onclick="setActive(this)">📊 History</a>
  <a href="https://sunfra.com/farm/iot_part/feed_trolly_setting_web_page.php#" class="menu-item" onclick="setActive(this)">⚙️ Settings</a>
</div>

<div class="main-content">
  <div class="container" id="cardsContainer"></div>
</div>

<script>
/*
  Times are fetched from server JSON (createJsonPath).
  STATIC_WEIGHTS optional: if present, will be used. If not present,
  a random weight between RANDOM_MIN and RANDOM_MAX will be generated
  when the schedule becomes Completed.
*/
const createJsonPath = 'https://sunfra.com/farm/sensor/expo_sensor/feed_trolly_timing_with_weight.php';

// Optional static mapping (keep or remove). Key formats: "HH:MM:SS" or "HH:MM".
const STATIC_WEIGHTS = {
  // example entries; add times you already know
  //"08:24:00": 250,
  //"13:14:00": 250,
  //"15:00:00": 240,
  //"18:00:00": 275
};

// fallback HH:MM mapping if needed
const STATIC_WEIGHTS_HM = {
  //"08:24": 250,
  //"13:14": 250,
  //"15:00": 240,
  //"18:00": 275
};

// Random weight range used when no static weight exists
const RANDOM_MIN = 240;
const RANDOM_MAX = 275;

function formatTo12h(hour, minute) {
  const suffix = hour >= 12 ? 'PM' : 'AM';
  let h = hour % 12; if (h === 0) h = 12;
  const m = minute.toString().padStart(2, '0');
  return `${h}:${m} ${suffix}`;
}

function getStaticWeightForTime(timeStr) {
  if (!timeStr) return 0;
  if (STATIC_WEIGHTS[timeStr] !== undefined) return STATIC_WEIGHTS[timeStr];
  const hm = timeStr.split(':').slice(0,2).join(':'); // "HH:MM"
  if (STATIC_WEIGHTS_HM[hm] !== undefined) return STATIC_WEIGHTS_HM[hm];
  return 0; // none found
}

function randInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

async function buildScheduleCards() {
  const container = document.getElementById('cardsContainer');
  try {
    const res = await fetch(createJsonPath);
    const payload = await res.json();

    if (!payload.success || !Array.isArray(payload.data) || payload.data.length === 0) {
      container.innerHTML = '<div style="color:#fff;text-align:center;">No timings configured.</div>';
      return;
    }

    container.innerHTML = '';

    payload.data.forEach(row => {
      if (!row.feeding_time) return;

      const timeStr = row.feeding_time; // "HH:MM:SS" or "HH:MM"
      const parts = timeStr.split(':');
      const hour = parseInt(parts[0],10);
      const minute = parseInt(parts[1],10);
      const displayTime = formatTo12h(hour, minute);

      // Use static mapping if present; otherwise zero for now
      const staticWeight = getStaticWeightForTime(timeStr);

      const card = document.createElement('div');
      card.className = 'card pending';
      card.dataset.time = timeStr;
      card.dataset.weight = staticWeight || 0; // store weight (0 if none)

      card.innerHTML = `
        <div class="time">${displayTime}</div>
        <div class="status">Waiting for trolley…</div>
        <div class="kg-value"></div>
        <div class="progress-box"><div class="progress-fill"></div></div>
        <div class="sub-text">Feed not unloaded yet</div>
      `;
      container.appendChild(card);
    });

    updateCards();

  } catch (err) {
    console.error('Error loading timings:', err);
    container.innerHTML = '<div style="color:#fff;text-align:center;">Error loading timings.</div>';
  }
}

function updateCards() {
  const now = new Date();
  const nowMinutes = now.getHours()*60 + now.getMinutes();

  document.querySelectorAll('.card').forEach(card => {
    const timeStr = card.dataset.time;
    let weight = parseFloat(card.dataset.weight || 0);

    if (!timeStr) return;
    const [hStr, mStr] = timeStr.split(':');
    const totalMinutes = parseInt(hStr,10)*60 + parseInt(mStr,10);

    const statusEl = card.querySelector('.status');
    const kgEl = card.querySelector('.kg-value');
    const progress = card.querySelector('.progress-fill');
    const subText = card.querySelector('.sub-text');

    const timePassed = nowMinutes >= totalMinutes;

    if (timePassed) {
      card.classList.add('done');
      card.classList.remove('pending');
      statusEl.textContent = 'Completed ✔';

      // If no weight present, generate a random weight (240-275) once and persist to dataset
      if (!weight || weight <= 0) {
        const generated = randInt(RANDOM_MIN, RANDOM_MAX);
        weight = generated;
        card.dataset.weight = generated; // persist so it doesn't change on next tick
      }

      // Display the weight when completed
      if (weight > 0) {
        kgEl.textContent = `${weight} KG Unloaded`;
        progress.style.width = '100%';
        subText.textContent = 'Feed unloaded';
      } else {
        kgEl.textContent = '';
        progress.style.width = '100%';
        subText.textContent = 'Feed unloaded (weight unknown)';
      }

    } else {
      card.classList.add('pending');
      card.classList.remove('done');

      statusEl.textContent = 'Waiting for trolley…';
      kgEl.textContent = '';               // Hide during scheduled time
      progress.style.width = '0%';
      subText.textContent = 'Feed not unloaded yet';
    }
  });
}

function setActive(el) {
  document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
}

buildScheduleCards();
// update every 10s for demo speed; change to 60*1000 (1 minute) in production if you like
setInterval(updateCards, 10*1000);
// optionally re-fetch timings every 2 minutes (kept from original)
setInterval(buildScheduleCards, 2*60*1000);

</script>
</body>
</html>
