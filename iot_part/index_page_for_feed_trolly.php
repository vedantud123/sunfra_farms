<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feed Trolley Schedule</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
    body {
        margin: 0;
        font-family: "Poppins", sans-serif;
        background: linear-gradient(120deg, #16222A, #3A6073);
        display: flex;
        overflow-x: hidden;
    }

    .sidebar {
        width: 240px;
        height: 100vh;
        background: #1f2937;
        padding-top: 20px;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 8px rgba(0,0,0,0.2);
    }

    .sidebar-title {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        text-align: center;
        padding: 15px 0;
        border-bottom: 1px solid #374151;
        margin-bottom: 10px;
    }

    .menu-item {
        padding: 15px 20px;
        color: #d1d5db;
        font-size: 18px;
        text-decoration: none;
        display: block;
        transition: 0.3s;
    }

    .menu-item:hover {
        background: #374151;
        color: #fff;
    }

    .menu-item.active {
        background: #4f46e5;
        color: white;
        font-weight: bold;
    }

    .main-content {
        flex: 1;
        padding: 20px;
        display: flex;
        justify-content: center;
    }

    .container {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, 1fr); 
        gap: 25px;
        max-width: 1200px;
    }

    .card {
        padding: 25px;
        border-radius: 18px;
        text-align: center;
        color: white;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        transition: 0.2s;
        min-height: 220px;
    }

    .pending {
        background: linear-gradient(135deg, #ff9966, #ff5e62);
    }

    .done {
        background: linear-gradient(135deg, #00b09b, #96c93d);
        animation: glow 1.5s infinite alternate;
    }

    @keyframes glow {
        from { box-shadow: 0 0 10px #a2ff94; }
        to { box-shadow: 0 0 25px #32ff7e; }
    }

    .time {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .status {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .kg-value {
        font-size: 30px;
        font-weight: 800;
        margin-top: 10px;
    }

    .progress-box {
        width: 100%;
        height: 12px;
        background: rgba(255,255,255,0.3);
        border-radius: 20px;
        margin-top: 15px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        width: 0%;
        background: white;
        border-radius: 20px;
        transition: width 0.8s ease;
    }

    .sub-text {
        margin-top: 8px;
        font-size: 14px;
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 150px;
        }
        .menu-item {
            font-size: 16px;
            padding: 12px 15px;
        }
        .time { font-size: 22px; }
        .kg-value { font-size: 26px; }
    }

    @media (max-width: 480px) {
        body {
            flex-direction: column;
        }
        .sidebar {
            width: 100%;
            display: flex;
            justify-content: space-around;
        }
        .menu-item {
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }
        .container {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>

<body>

<div class="sidebar">
    <div class="sidebar-title">MENU</div>

    <a href="https://sunfra.com/farm/iot_part/index_page_for_feed_trolly.php" class="menu-item" onclick="setActive(this)">📌 Feed Schedule</a>
    <a href="https://sunfra.com/farm/iot_part/feed_trolly_history_page.php" class="menu-item" onclick="setActive(this)">📊 History</a>
    <a href="https://sunfra.com/farm/iot_part/feed_trolly_setting_web_page.php#" class="menu-item" onclick="setActive(this)">⚙️ Settings</a>
</div>

<div class="main-content">
    <div class="container" id="cardsContainer">
        <!-- Cards will be created dynamically from JSON -->
    </div>
</div>

<script>
/*
  MAIN schedule JSON (you already had this)
  It returns payload.data[] with rows that include feeding_time (HH:MM:SS)
  NOTE: we IGNORE feed_weight_kg from this JSON.

  UNLOADED_API should accept a time parameter and return JSON like:
  { "success": true, "unloaded_kg": 120 }   // integer
  or
  { "success": true, "unloaded_kg": 0 }     // zero when nothing unloaded yet
  or { "success": false, "message": "..." }
*/
const createJsonPath = 'https://sunfra.com/farm/iot_part/feed_trolly_timing_with_weight.php';

// CHANGE this to your real unloaded-weight endpoint URL:
const UNLOADED_API = 'https://sunfra.com/farm/iot_part/feed_trolly_unloaded_by_time.php';

// small in-memory cache to avoid repeated calls inside same minute
const unloadedCacheTtlMs = 60 * 1000; // 1 minute

function formatTo12h(hour, minute) {
    const suffix = hour >= 12 ? 'PM' : 'AM';
    let h = hour % 12;
    if (h === 0) h = 12;
    const m = minute.toString().padStart(2, '0');
    return `${h}:${m} ${suffix}`;
}

// Build cards based on timings from JSON (only uses times)
async function buildScheduleCards() {
    const container = document.getElementById('cardsContainer');

    try {
        const res = await fetch(createJsonPath);
        const payload = await res.json();

        if (!payload.success || !Array.isArray(payload.data) || payload.data.length === 0) {
            container.innerHTML = '<div style="color:#fff;text-align:center;">No timings configured.</div>';
            return;
        }

        container.innerHTML = ''; // Clear existing cards

        payload.data.forEach(row => {
            if (!row.feeding_time) return;

            const timeStr = row.feeding_time;            // "18:00:00"

            const [hStr, mStr] = timeStr.split(':');
            const hour = parseInt(hStr, 10);
            const minute = parseInt(mStr, 10);

            const displayTime = formatTo12h(hour, minute);

            const card = document.createElement('div');
            card.className = 'card pending';

            // store values for later
            card.dataset.time   = timeStr;
            // card.dataset.weight intentionally NOT set (we are not using scheduled weight)

            card.innerHTML = `
                <div class="time">${displayTime}</div>
                <div class="status">Waiting for trolley…</div>
                <div class="kg-value"></div>
                <div class="progress-box">
                    <div class="progress-fill"></div>
                </div>
                <div class="sub-text">Feed not unloaded yet</div>
            `;

            container.appendChild(card);
        });

        // initial update (which will call unloaded API for past times)
        updateCards();

    } catch (e) {
        console.error('Error loading timings:', e);
        container.innerHTML = '<div style="color:#fff;text-align:center;">Error loading timings.</div>';
    }
}

// get unloaded KG for a given time (HH:MM:SS)
// caches responses for a short TTL to reduce calls
async function getUnloadedKgForTime(timeStr) {
    const key = `unloaded:${timeStr}`;
    const cachedStr = sessionStorage.getItem(key);
    if (cachedStr) {
        try {
            const cached = JSON.parse(cachedStr);
            const age = Date.now() - (cached.fetchedAt || 0);
            if (age < unloadedCacheTtlMs) {
                return cached.unloaded_kg || 0;
            }
        } catch (e) { /* continue to fetch */ }
    }

    try {
        // append time param - adjust param name if your API expects a different key
        const url = `${UNLOADED_API}?time=${encodeURIComponent(timeStr)}`;
        const res = await fetch(url, { cache: 'no-store' });
        const data = await res.json();

        let unloaded = 0;
        if (data && data.success) {
            // tolerant parsing: accept unloaded_kg or unloaded or weight
            unloaded = parseInt(data.unloaded_kg ?? data.unloaded ?? data.weight ?? 0, 10) || 0;
        }

        sessionStorage.setItem(key, JSON.stringify({ fetchedAt: Date.now(), unloaded_kg: unloaded }));
        return unloaded;

    } catch (e) {
        console.warn('Failed to fetch unloaded weight for', timeStr, e);
        // don't treat as completed if API fails
        return 0;
    }
}

async function updateCards() {
    const now = new Date();
    const nowMinutes = now.getHours() * 60 + now.getMinutes();

    // for each card, decide whether completed (time passed && unloaded>0) or pending
    const cards = Array.from(document.querySelectorAll('.card'));

    // run fetches in parallel but limited to avoid hammering
    await Promise.all(cards.map(async (card) => {
        const timeStr = card.dataset.time;   // "HH:MM:SS"
        if (!timeStr) return;

        const [hStr, mStr] = timeStr.split(':');
        const totalMinutes = parseInt(hStr, 10) * 60 + parseInt(mStr, 10);

        const statusEl   = card.querySelector('.status');
        const kgEl       = card.querySelector('.kg-value');
        const progressEl = card.querySelector('.progress-fill');
        const subText    = card.querySelector('.sub-text');

        const timePassed = nowMinutes >= totalMinutes;

        if (timePassed) {
            // check unloaded weight from the UNLOADED_API
            const unloaded = await getUnloadedKgForTime(timeStr);

            if (unloaded > 0) {
                // Completed
                card.classList.add('done');
                card.classList.remove('pending');

                statusEl.textContent = 'Completed ✔';
                kgEl.textContent = `${unloaded} KG Unloaded`;
                progressEl.style.width = '100%';
                subText.textContent = 'Feed unloaded';
            } else {
                // Time passed but nothing unloaded yet -> still pending (do NOT show scheduled weight)
                card.classList.add('pending');
                card.classList.remove('done');

                statusEl.textContent = 'Waiting for trolley…';
                kgEl.textContent = ''; // do not show scheduled weight when not completed
                progressEl.style.width = '0%';
                subText.textContent = 'Feed not unloaded yet';
            }
        } else {
            // future time -> pending, hide any kg
            card.classList.add('pending');
            card.classList.remove('done');

            statusEl.textContent = 'Waiting for trolley…';
            kgEl.textContent = ''; // hide scheduled weight (per request)
            progressEl.style.width = '0%';
            subText.textContent = 'Feed not unloaded yet';
        }
    }));
}

function setActive(element) {
    let items = document.querySelectorAll(".menu-item");
    items.forEach(i => i.classList.remove("active"));
    element.classList.add("active");
}

// Build cards once at load
buildScheduleCards();

// Re-run status every minute (to update completed as time passes)
setInterval(updateCards, 60 * 1000);

// Also refresh timing list from server every 2 minutes (in case admin changes schedule)
setInterval(buildScheduleCards, 2 * 60 * 1000);
</script>

</body>
</html>
