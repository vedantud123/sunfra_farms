<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Weekly Egg Damage Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
  .dropdown-menu {
  max-height: 300px;
  overflow-y: auto;
  z-index: 1050;
  width: 100% !important;
  left: 0 !important;
  right: auto !important;
  transform: translate3d(0px, 38px, 0px) !important;
  padding: 0.5rem;
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}

.dropdown-menu li {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.dropdown-menu .form-check {
  display: flex;
  align-items: center;
  padding: 6px 10px;
  white-space: normal;
  margin: 0;
}

.form-check-input {
  margin-right: 8px;
}

.dropdown-toggle::after {
  float: right;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .dropdown-menu {
    max-height: 220px;
    font-size: 14px;
  }

  .form-check-label {
    font-size: 14px;
  }
}.dropdown-menu .form-check-label {
  width: 100%;
  padding-left: 6px;
  font-size: 15px;
}

</style>

</head>
<body>
<div class="container mt-4">
<div class="mb-3">
  <button class="btn btn-secondary" onclick="goBack()">← Go Back</button>
</div>

  <h2 class="mb-4 text-center">Weekly Egg Damage Report</h2>

  <div class="row g-3 mb-4">
    <!-- Month multi-select -->
    <div class="col-md-6 col-sm-12">
      <label class="form-label">Select Months</label>
      <div class="dropdown w-100">
        <button id="monthDropdownBtn" class="btn btn-outline-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
          Select Months
        </button>
        <ul class="dropdown-menu w-100 start-0" id="monthDropdownMenu"></ul>
      </div>
    </div>

    <!-- Shead dropdown -->
    <div class="col-md-6 col-sm-12">
      <label class="form-label">Select Sheads</label>
      <div class="dropdown w-100">
        <button id="sheadDropdownBtn" class="btn btn-outline-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
          Select Sheads
        </button>
        <ul class="dropdown-menu w-100 start-0" id="sheadDropdownMenu"></ul>
      </div>
    </div>
  </div>

  <div id="resultArea"></div>
</div>

<script>
  const sheadList = ["Shead 1", "Shead 2", "Shead 3", "Shead 4","Shead 5", "Shead 6", "Shead 7", "Shead 8"];

function getTrayCount(eggs) {
  const wholeTrays = Math.floor(eggs / 30);
  const remainder = eggs % 30;
  return `${wholeTrays}.${remainder.toString().padStart(2, '0')}`;
}

function renderSheadCheckboxes() {
  const menu = document.getElementById("sheadDropdownMenu");
  menu.classList.add("dropdown-menu-checkboxes");

  menu.innerHTML = `
	  <div class="px-3 py-2">
		<div class="form-check mb-2">
		  <input class="form-check-input" type="checkbox" id="selectAllSheads">
		  <label class="form-check-label fw-bold" for="selectAllSheads">Select All</label>
		</div>
		<hr class="dropdown-divider">
		<div class="d-flex flex-column gap-1">
		  ${sheadList.map((shead, i) => `
			<div class="form-check">
			  <input class="form-check-input shead-checkbox" type="checkbox" id="shead${i}" value="${shead}">
			  <label class="form-check-label" for="shead${i}">${shead}</label>
			</div>
		  `).join('')}
		</div>
	  </div>
	`;

  document.getElementById("selectAllSheads").addEventListener("change", function () {
    document.querySelectorAll(".shead-checkbox").forEach(cb => cb.checked = this.checked);
    updateSheadDropdownButton();
    fetchData();
  });

  menu.querySelectorAll(".shead-checkbox").forEach(cb => {
    cb.addEventListener("change", () => {
      const all = document.querySelectorAll(".shead-checkbox");
      const checked = document.querySelectorAll(".shead-checkbox:checked");
      document.getElementById("selectAllSheads").checked = all.length === checked.length;
      updateSheadDropdownButton();
      fetchData();
    });
  });

  menu.addEventListener('click', e => e.stopPropagation());
}

function renderMonthCheckboxes() {
  const monthMenu = document.getElementById("monthDropdownMenu");
  monthMenu.classList.add("dropdown-menu-checkboxes");

  const now = new Date();
  const months = [];

  for (let i = 0; i < 12; i++) {
    const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const value = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
    const label = date.toLocaleString('default', { month: 'long', year: 'numeric' });
    months.push({ value, label });
  }

	 monthMenu.innerHTML = `
	  <div class="px-2">
		<div class="form-check mb-2">
		  <input class="form-check-input" type="checkbox" id="selectAllMonths">
		  <label class="form-check-label fw-bold" for="selectAllMonths">Select All</label>
		</div>
		<hr class="dropdown-divider">
		<div class="d-flex flex-column gap-1">
		  ${months.map((m, i) => `
			<div class="form-check">
			  <input class="form-check-input month-checkbox" type="checkbox" id="month${i}" value="${m.value}">
			  <label class="form-check-label" for="month${i}">${m.label}</label>
			</div>
		  `).join("")}
		</div>
	  </div>
	`;

  document.getElementById("selectAllMonths").addEventListener("change", function () {
    const isChecked = this.checked;
    document.querySelectorAll(".month-checkbox").forEach(cb => cb.checked = isChecked);
    updateMonthDropdownButton();
    fetchData();
  });

  monthMenu.querySelectorAll(".month-checkbox").forEach(cb => {
    cb.addEventListener("change", () => {
      const all = document.querySelectorAll(".month-checkbox");
      const checked = document.querySelectorAll(".month-checkbox:checked");
      document.getElementById("selectAllMonths").checked = all.length === checked.length;
      updateMonthDropdownButton();
      fetchData();
    });
  });

  monthMenu.addEventListener('click', e => e.stopPropagation());
}

function updateSheadDropdownButton() {
  const selected = document.querySelectorAll(".shead-checkbox:checked");
  const btn = document.getElementById("sheadDropdownBtn");
  btn.textContent = selected.length > 0 ? `${selected.length} Shead(s) Selected` : "Select Sheads";
}

function updateMonthDropdownButton() {
  const selected = document.querySelectorAll(".month-checkbox:checked");
  const btn = document.getElementById("monthDropdownBtn");
  btn.textContent = selected.length > 0 ? `${selected.length} Month(s) Selected` : "Select Months";
}

function fetchData() {
  const selectedMonths = Array.from(document.querySelectorAll(".month-checkbox:checked")).map(cb => cb.value);
  const selectedSheads = Array.from(document.querySelectorAll(".shead-checkbox:checked")).map(cb => cb.value);

  const resultArea = document.getElementById("resultArea");
  resultArea.innerHTML = "";

  if (selectedMonths.length === 0 || selectedSheads.length === 0) {
    resultArea.innerHTML = `<div class="alert alert-info">Please select at least one month and one shead.</div>`;
    return;
  }

  const monthQuery = selectedMonths.map(encodeURIComponent).join(",");
  const sheadQuery = selectedSheads.map(encodeURIComponent).join(",");

  fetch(`get_weekly_damaged_eggs.php?months=${monthQuery}&sheads=${sheadQuery}`)
    .then(res => res.json())
    .then(data => {
      if (!data || data.length === 0) {
        resultArea.innerHTML = `<div class="alert alert-warning">No records found.</div>`;
        return;
      }

      const grouped = {};
      data.forEach(item => {
        const shead = item["Shead Name"];
        if (!grouped[shead]) grouped[shead] = [];
        grouped[shead].push(item);
      });

      let html = "";

      Object.entries(grouped).forEach(([sheadName, items]) => {
        const rows = items.map(item => `
          <tr>
            <td>${item["Month"]}</td>
            <td>${item["Week"]}</td>
            <td>${getTrayCount(item["Production Damaged"])}</td>
            <td>${getTrayCount(item["Sale Damaged"])}</td>
            <td><strong>${getTrayCount(item["Total Damaged"])}</strong></td>
            <td style="color: #d9534f; font-weight: bold;">${getTrayCount(item["100_Trays_Damage"])}</td>
          </tr>
        `).join('');

        html += `
          <div class="mb-5">
            <h5 class="text-primary mb-3">${sheadName}</h5>
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead class="table-dark">
                  <tr>
                    <th>Month</th>
                    <th>Week</th>
                    <th>Production</th>
                    <th>Sale</th>
                    <th>Total</th>
                    <th>Avg. Damage</th>
                  </tr>
                </thead>
                <tbody>${rows}</tbody>
              </table>
            </div>
          </div>
        `;
      });

      resultArea.innerHTML = html;
    })
    .catch(err => {
      console.error("Error fetching data", err);
      resultArea.innerHTML = `<div class="alert alert-danger">Failed to load data.</div>`;
    });
}

document.addEventListener("DOMContentLoaded", function () {
  renderSheadCheckboxes();
  renderMonthCheckboxes();

  if (window.innerWidth <= 768) {
    document.body.style.zoom = "80%";
  }
});
function goBack() {
  window.location.href = "https://sunfra.com/farm/egg_godown/egg_godown.php"; // Replace with your actual URL
}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
