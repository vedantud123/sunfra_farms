<?php
date_default_timezone_set('Asia/Kolkata');

$mysqli = new mysqli("localhost", "sunfra_farms", "sunfra_farms", "sunfra_farms");
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$macAddressList = [
    "5D-7E-24-33-4F-C4",
    "04-BA-5F-0F-F0-A4",
    "50-5C-F9-54-94-34",
	"88-F6-64-A5-05-28",
	"98-B8-60-0F-F0-A4",
	"68-14-5F-0F-F0-A4",
	"5C-26-14-C4-0A-24"
];

$today = date("Y-m-d");
$fromDate = $_GET['from_date'] ?? $today;
$toDate   = $_GET['to_date'] ?? $today;
$selectedMac = $_GET['mac_address'] ?? "ALL";
$pulsesPerLiter = isset($_GET['ppl']) ? (float)$_GET['ppl'] : 450.0;

if ($selectedMac !== "ALL" && !in_array($selectedMac, $macAddressList, true)) {
    $selectedMac = "ALL";
}

$inPlaceholders = implode(',', array_fill(0, count($macAddressList), '?'));

$sql = "
SELECT
    DATE(timestamp) AS flow_date,
    mac_address,
    SUM(COALESCE(pulsecount,0)) AS total_pulses,
    ROUND(SUM(COALESCE(pulsecount,0)) / ?, 3) AS total_liters,
    COUNT(*) AS total_rows,
    SUM(CASE WHEN pulsecount > 0 THEN 1 ELSE 0 END) AS non_zero_rows
FROM water_flow_meter_expo
WHERE timestamp >= ?
  AND timestamp < DATE_ADD(?, INTERVAL 1 DAY)
  AND mac_address IN ($inPlaceholders)
";

$params = [$pulsesPerLiter, $fromDate, $toDate];
$types  = "dss";

foreach ($macAddressList as $m) {
    $params[] = $m;
    $types .= "s";
}

if ($selectedMac !== "ALL") {
    $sql .= " AND mac_address = ? ";
    $params[] = $selectedMac;
    $types .= "s";
}

$sql .= "
GROUP BY DATE(timestamp), mac_address
ORDER BY flow_date ASC, mac_address ASC
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
$grandPulses = 0;
$grandLiters = 0.0;
$grandRows = 0;
$grandNonZero = 0;

while ($r = $res->fetch_assoc()) {
    $grandPulses += (int)$r['total_pulses'];
    $grandLiters += (float)$r['total_liters'];
    $grandRows += (int)$r['total_rows'];
    $grandNonZero += (int)$r['non_zero_rows'];
    $rows[] = $r;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Water Flow Dashboard</title>
<style>
:root{
    --bg:#f2f6fb; --card:#ffffff; --text:#0f172a; --muted:#64748b;
    --line:#e2e8f0; --brand:#0ea5e9; --brand2:#0284c7; --ok:#10b981;
}
*{box-sizing:border-box}
body{
    margin:0;
    font-family:"Trebuchet MS","Segoe UI",Tahoma,sans-serif;
    color:var(--text);
    background:
        radial-gradient(circle at 10% 10%, #dff4ff 0%, transparent 35%),
        radial-gradient(circle at 90% 15%, #e3fff4 0%, transparent 30%),
        var(--bg);
}
.wrapper{max-width:1120px;margin:28px auto;padding:0 16px}
.topbar{
    background:linear-gradient(120deg,var(--brand),var(--brand2));
    color:#fff;border-radius:16px;padding:20px;box-shadow:0 10px 30px rgba(2,132,199,.25);
}
.topbar h1{margin:0 0 6px;font-size:26px}
.topbar p{margin:0;opacity:.95}
.panel{
    background:var(--card);margin-top:16px;border:1px solid var(--line);border-radius:14px;padding:16px;
}
.form-grid{display:grid;grid-template-columns:repeat(5,minmax(130px,1fr));gap:12px}
label{
    display:block;font-size:12px;font-weight:700;letter-spacing:.3px;color:var(--muted);margin-bottom:6px;text-transform:uppercase;
}
input,select,button{
    width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;font-size:14px;background:#fff;
}
button{
    margin-top:22px;border:none;color:#fff;font-weight:700;background:linear-gradient(120deg,var(--brand),var(--brand2));cursor:pointer;
}
.stats{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:12px;margin-top:14px}
.stat{
    background:#fff;border:1px solid var(--line);border-left:5px solid var(--brand);border-radius:12px;padding:12px;
}
.stat .k{font-size:12px;color:var(--muted);text-transform:uppercase;font-weight:700}
.stat .v{margin-top:4px;font-size:22px;font-weight:800}
.stat.green{border-left-color:var(--ok)}
.table-card{margin-top:16px;background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden}
.table-head{padding:12px 14px;border-bottom:1px solid var(--line);font-weight:700;color:#0c4a6e;background:#f8fcff}
.table-wrap{overflow:auto}
table{width:100%;border-collapse:collapse;min-width:780px}
th,td{padding:12px;border-bottom:1px solid #eef2f7;text-align:left}
th{background:#fcfdff;color:#334155;font-size:13px;text-transform:uppercase;letter-spacing:.3px}
tr:hover td{background:#f8fbff}
.badge{
    display:inline-block;padding:4px 8px;border-radius:999px;font-size:12px;font-weight:700;background:#e0f2fe;color:#075985;
}
.empty{padding:18px;color:var(--muted)}
@media (max-width:920px){
    .form-grid{grid-template-columns:1fr 1fr}
    .stats{grid-template-columns:1fr 1fr}
    button{margin-top:0}
}
@media (max-width:560px){
    .form-grid,.stats{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="wrapper">
    <div class="topbar">
        <h1>Water Flow Dashboard</h1>
        <p>Timezone: Asia/Kolkata | Pulse-based daily usage</p>
    </div>

    <div class="panel">
        <form method="get">
            <div class="form-grid">
                <div>
                    <label>From Date</label>
                    <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>" required>
                </div>
                <div>
                    <label>To Date</label>
                    <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>" required>
                </div>
                <div>
                    <label>Mac Address</label>
                    <select name="mac_address">
                        <option value="ALL" <?= $selectedMac === "ALL" ? "selected" : "" ?>>ALL</option>
                        <?php foreach ($macAddressList as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= $m === $selectedMac ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Pulses / Liter</label>
                    <input type="number" step="0.001" name="ppl" value="<?= htmlspecialchars((string)$pulsesPerLiter) ?>" required>
                </div>
                <div>
                    <button type="submit">Generate Report</button>
                </div>
            </div>
        </form>

        <div class="stats">
            <div class="stat">
                <div class="k">Selected MAC</div>
                <div class="v" style="font-size:16px"><?= htmlspecialchars($selectedMac) ?></div>
            </div>
            <div class="stat">
                <div class="k">Total Pulses</div>
                <div class="v"><?= number_format($grandPulses) ?></div>
            </div>
            <div class="stat green">
                <div class="k">Total Liters</div>
                <div class="v"><?= number_format($grandLiters, 3) ?></div>
            </div>
            <div class="stat">
                <div class="k">Active Rows (pulse > 0)</div>
                <div class="v"><?= number_format($grandNonZero) ?></div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-head">Daily Breakdown</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>MAC Address</th>
                        <th>Total Pulses</th>
                        <th>Total Liters</th>
                        <th>Total Rows</th>
                        <th>Non-zero Rows</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="empty">No data found for selected range.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><span class="badge"><?= htmlspecialchars($r['flow_date']) ?></span></td>
                        <td><?= htmlspecialchars($r['mac_address']) ?></td>
                        <td><?= number_format((int)$r['total_pulses']) ?></td>
                        <td><?= number_format((float)$r['total_liters'], 3) ?></td>
                        <td><?= number_format((int)$r['total_rows']) ?></td>
                        <td><?= number_format((int)$r['non_zero_rows']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
<?php
$stmt->close();
$mysqli->close();
?>