<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sunfra Farms Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background-color: #121212;
        font-family: 'Poppins', sans-serif;
        color: #fff;
        min-height: 100vh;
    }
    h1 {
        text-align: center;
        margin: 30px 0;
        font-weight: 600;
        color: #ffffff;
    }
    .card {
        border-radius: 15px;
        background-color: #1e1e1e;
        box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        transition: transform 0.3s, box-shadow 0.3s;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.6);
    }
    .card-body {
        padding: 20px;
    }
    .card-title {
        font-size: 1.3rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: 15px;
        color: #fff;
    }
    .stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-weight: 500;
        font-size: 1rem;
        color: #fff; /* Ensure label text is white */
    }
    .stat span {
        font-weight: 600;
    }
    .temp span { color: #ff6b6b; }      /* red for temperature */
    .humidity span { color: #1dd1a1; }  /* green for humidity */
    .water span { color: #54a0ff; }     /* blue for water */
    .feed span { color: #feca57; }      /* yellow for feed */

    @media (max-width: 575px) {
        .card { margin-bottom: 15px; }
        .stat { font-size: 0.95rem; }
    }
</style>
</head>

<body>
<div class="container">
    <h1>Sunfra Farms — Shed Dashboard</h1>
    <div class="row" id="shedCards"></div>
</div>

<script>
const API_URL = "https://rt.ambientweather.net/v1/devices?applicationKey=134af5db96ee4c4facde6820bc14a01bfc86a92d3d224b4b877fc94671fd1cd9&apiKey=572bf73566ca44b587fa6d64303a2fc9b5b22c4d215542c8bf80e3c5d8b1b322";

const sheds = [
    "Shead1", "Shead2", "Shead3", "Shead4",
    "Shead5", "Shead6", "Shead7", "Shead8",
    "Chick", "Grower"
];

const waterUsedPerShed = [120, 85, 90, 100, 95, 110, 80, 125, 78, 102];

const container = document.getElementById("shedCards");

sheds.forEach((name, i) => {
    const cardHTML = `
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">${name}</h5>
            <div class="stat temp">Temperature: <span>Loading...</span></div>
            <div class="stat humidity">Humidity: <span>Loading...</span></div>
            <div class="stat water">Water Used: <span>${waterUsedPerShed[i]} lit</span></div>
            <div class="stat feed">Available Feed: <span>0</span></div>
          </div>
        </div>
      </div>`;
    container.insertAdjacentHTML("beforeend", cardHTML);
});

async function updateWeather() {
    try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error("Failed to fetch Ambient Weather data");

        const data = await response.json();
        if (!data || !data.length) throw new Error("No data received");

        const lastData = data[0].lastData;
        const tempC = ((lastData.tempinf - 32) * 5/9).toFixed(1) + "°C";
        const humidity = lastData.humidityin + "%";

        document.querySelectorAll(".temp span").forEach(el => el.textContent = tempC);
        document.querySelectorAll(".humidity span").forEach(el => el.textContent = humidity);

    } catch (error) {
        console.error("Weather update failed:", error);
        document.querySelectorAll(".temp span").forEach(el => el.textContent = "N/A");
        document.querySelectorAll(".humidity span").forEach(el => el.textContent = "N/A");
    }
}

updateWeather();

setInterval(updateWeather, 10 * 60 * 1000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
