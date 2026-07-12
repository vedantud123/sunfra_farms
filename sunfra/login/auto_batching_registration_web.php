<?php
session_start();

$clientName = $_SESSION['client_name'] ?? 'Your Company';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/login.php");
    exit;
}

$username = $_SESSION['username'] ?? '';
if (empty($username)) {
    header("Location: ../login/login.php");
    exit;
}

$sessionClientId = isset($_SESSION['client_id']) ? (int) $_SESSION['client_id'] : 0;
if ($sessionClientId !== 1) {
    header("Location: ../login/login.php");
    exit;
}

date_default_timezone_set("Asia/Kolkata");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Batch Mac Address</title>
    <style>
        :root {
            --bg: #0f172a;
            --bg-soft: #111827;
            --card: rgba(255, 255, 255, 0.92);
            --card-border: rgba(148, 163, 184, 0.18);
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-2: #7c3aed;
            --success: #16a34a;
            --danger: #dc2626;
            --shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
            --radius: 22px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.22), transparent 34%),
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.18), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, #eef2ff 50%, #f8fafc 100%);
            min-height: 100vh;
        }

        .shell {
            max-width: 1280px;
            margin: 0 auto;
            padding: 28px 18px 42px;
        }

        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 30px;
            padding: 28px;
            color: #fff;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 45%, #7c3aed 100%);
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: auto -80px -110px auto;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            filter: blur(2px);
        }

        .hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            font-size: 13px;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 12px;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.05;
            letter-spacing: -0.04em;
        }

        .hero p {
            margin: 0;
            max-width: 760px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 15px;
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .button {
            appearance: none;
            border: 0;
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .16s ease, opacity .16s ease, background .16s ease;
        }

        .button:hover { transform: translateY(-1px); }
        .button:active { transform: translateY(0); }
        .button.primary {
            color: #fff;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            box-shadow: 0 12px 26px rgba(22, 163, 74, 0.28);
        }
        .button.secondary {
            color: #e2e8f0;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }
        .button.ghost {
            color: #0f172a;
            background: #fff;
            border: 1px solid #dbe3f1;
        }
        .button.link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .button.small {
            padding: 9px 12px;
            border-radius: 12px;
            font-size: 13px;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 16px;
            position: relative;
            z-index: 1;
        }

        .metric {
            padding: 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(12px);
        }

        .metric .label {
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(226, 232, 240, 0.78);
            margin-bottom: 8px;
        }

        .metric .value {
            font-size: 24px;
            font-weight: 800;
            line-height: 1;
        }

        .metric .note {
            margin-top: 6px;
            font-size: 13px;
            color: rgba(226, 232, 240, 0.78);
        }

        .layout {
            display: grid;
            grid-template-columns: 360px minmax(0, 1fr);
            gap: 18px;
            margin-top: 18px;
            align-items: start;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
        }

        .panel-head {
            padding: 20px 20px 0;
        }

        .panel-head h2 {
            margin: 0 0 6px;
            font-size: 20px;
            letter-spacing: -0.02em;
        }

        .panel-head p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .panel-body {
            padding: 20px;
        }

        .stack {
            display: grid;
            gap: 14px;
        }

        .field label {
            display: block;
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .input,
        .search {
            width: 100%;
            border: 1px solid #d7e0ee;
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 14px;
            outline: none;
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .input:focus,
        .search:focus {
            border-color: rgba(37, 99, 235, 0.65);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .input.uppercase {
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .helper {
            margin-top: 7px;
            font-size: 12px;
            color: var(--muted);
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .search-wrap {
            flex: 1;
            min-width: 220px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-wrap .search {
            padding-left: 16px;
        }

        .status {
            min-height: 24px;
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            display: none;
            font-size: 14px;
            line-height: 1.45;
        }

        .status.show { display: block; }
        .status.success {
            display: block;
            color: #166534;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
        }
        .status.error {
            display: block;
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .table-wrap {
            overflow: auto;
            border-radius: 18px;
            border: 1px solid #e5ecf6;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 720px;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            text-align: left;
            padding: 15px 16px;
            background: linear-gradient(180deg, #f8fbff, #eef4ff);
            color: #334155;
            font-size: 13px;
            letter-spacing: .04em;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }

        tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #eef2f7;
            font-size: 14px;
            color: #0f172a;
            vertical-align: middle;
            background: #fff;
        }

        tbody tr:hover td {
            background: #f8fbff;
        }

        .id-pill,
        .tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .id-pill {
            background: #eef2ff;
            color: #4338ca;
        }

        .tag {
            background: #ecfeff;
            color: #0e7490;
        }

        .empty {
            text-align: center;
            padding: 34px 16px;
            color: var(--muted);
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .footer-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .small-note {
            margin-top: 12px;
            font-size: 12px;
            color: var(--muted);
        }

        .editing-badge {
            display: none;
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 14px;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-size: 13px;
            font-weight: 600;
        }

        .editing-badge.show {
            display: block;
        }

        .skeleton {
            background: linear-gradient(90deg, #edf2f7 25%, #f8fbff 37%, #edf2f7 63%);
            background-size: 400% 100%;
            animation: shimmer 1.4s ease infinite;
            border-radius: 10px;
            height: 14px;
        }

        @keyframes shimmer {
            0% { background-position: 100% 0; }
            100% { background-position: 0 0; }
        }

        @media (max-width: 1080px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .shell { padding: 14px 12px 32px; }
            .hero { padding: 20px; border-radius: 22px; }
            .panel-head, .panel-body { padding-left: 16px; padding-right: 16px; }
            .metrics { grid-template-columns: 1fr; }
            .toolbar { align-items: stretch; }
            .search-wrap { width: 100%; }
        }
    </style>
</head>
<body>
<div class="shell">
    <section class="hero">
        <div class="hero-top">
            <div>
                <div class="eyebrow">Auto Batch MAC Registration â€¢ Premium Admin View</div>
                <h1>Manage MAC addresses with clarity.</h1>
                <p>
                    A modern registration dashboard to view, search, edit, and save records with a cleaner
                    workflow and better visibility for the user.
                </p>
            </div>
            <div class="hero-actions">
                <a class="button primary link" href="sunfra_client_registration_to_web.php">Registration of New Client</a>
                <button type="button" class="button secondary" id="refreshBtn">Refresh Table</button>
                <button type="button" class="button ghost" id="clearBtn">Clear Form</button>
            </div>
        </div>

        <div class="metrics">
            <div class="metric">
                <div class="label">Total Records</div>
                <div class="value" id="totalCount">0</div>
                <div class="note">All rows from the database</div>
            </div>
            <div class="metric">
                <div class="label">Visible Records</div>
                <div class="value" id="visibleCount">0</div>
                <div class="note">Matches the search filter</div>
            </div>
            <div class="metric">
                <div class="label">Last Action</div>
                <div class="value" id="lastAction">Ready</div>
                <div class="note">Shows your latest edit/save state</div>
            </div>
            <div class="metric">
                <div class="label">UI Mode</div>
                <div class="value">Fast</div>
                <div class="note">Clean layout and responsive table</div>
            </div>
        </div>
    </section>

    <div class="layout">
        <section class="panel">
            <div class="panel-head">
                <h2>Edit Record</h2>
                <p>Update an existing row or add a new one from this form.</p>
            </div>
            <div class="panel-body">
                <div id="editingBadge" class="editing-badge">Editing existing record</div>
                <form id="editForm" autocomplete="off">
                    <input type="hidden" id="id" name="id">
                    <div class="stack">
                        <div class="field">
                            <label for="mac_address">MAC Address</label>
                            <input class="input uppercase" type="text" id="mac_address" name="mac_address" placeholder="AA-BB-CC-DD-EE-FF">
                            <div class="helper">Use hyphen format like AA-BB-CC-DD-EE-FF.</div>
                        </div>
                        <div class="field">
                            <label for="client_id">Client ID</label>
                            <input class="input" type="number" id="client_id" name="client_id" placeholder="123">
                            <div class="helper">Optional if your record does not use a client reference.</div>
                        </div>
                    </div>

                    <div class="footer-actions">
                        <button type="submit" class="button primary" id="saveBtn">Save Record</button>
                        <button type="button" class="button ghost" id="resetBtn">Reset Form</button>
                    </div>

                    <div id="message" class="status"></div>
                    <div class="small-note">
                        Tip: use search to quickly find a row, then click edit.
                    </div>
                </form>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2>Saved Records</h2>
                <p>Search, browse, and edit existing data from the table below.</p>
            </div>
            <div class="panel-body">
                <div class="toolbar">
                    <div class="search-wrap">
                        <input class="search" type="search" id="searchBox" placeholder="Search by MAC address, client ID, or ID">
                    </div>
                    <button type="button" class="button ghost small" id="showAllBtn">Show All</button>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th style="width:90px;">ID</th>
                            <th>MAC Address</th>
                            <th style="width:160px;">Client ID</th>
                            <th style="width:160px;">Action</th>
                        </tr>
                        </thead>
                        <tbody id="tableBody">
                        <tr>
                            <td colspan="4" class="empty">
                                <div class="skeleton" style="width: 45%; margin: 0 auto 10px;"></div>
                                Loading records...
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div id="tableMeta" class="small-note">Loading data from server...</div>
                <div class="footer-actions" style="justify-content: space-between; align-items: center;">
                    <div class="small-note" id="pageInfo">Page 1 of 1</div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" class="button ghost small" id="prevPageBtn">
                            <i class="fas fa-chevron-left"></i> Prev
                        </button>
                        <button type="button" class="button ghost small" id="nextPageBtn">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const dataUrl = 'auto_batching_registration_json.php';
const saveUrl = 'auto_batching_save.php';

const form = document.getElementById('editForm');
const message = document.getElementById('message');
const tableBody = document.getElementById('tableBody');
const searchBox = document.getElementById('searchBox');
const resetBtn = document.getElementById('resetBtn');
const clearBtn = document.getElementById('clearBtn');
const refreshBtn = document.getElementById('refreshBtn');
const showAllBtn = document.getElementById('showAllBtn');
const saveBtn = document.getElementById('saveBtn');
const totalCount = document.getElementById('totalCount');
const visibleCount = document.getElementById('visibleCount');
const lastAction = document.getElementById('lastAction');
const tableMeta = document.getElementById('tableMeta');
const pageInfo = document.getElementById('pageInfo');
const editingBadge = document.getElementById('editingBadge');
const prevPageBtn = document.getElementById('prevPageBtn');
const nextPageBtn = document.getElementById('nextPageBtn');

let allRows = [];
let filteredRows = [];
let currentPage = 1;
const pageSize = 10;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function normalizeMacAddress(value) {
    return String(value ?? '')
        .trim()
        .toUpperCase()
        .replace(/:/g, '-')
        .replace(/\s+/g, '');
}

function setStatus(text, type) {
    message.textContent = text || '';
    message.className = 'status' + (type ? ' ' + type : '');
    if (!text) {
        message.className = 'status';
    }
}

function setLastAction(text) {
    lastAction.textContent = text;
}

function resetForm() {
    form.reset();
    document.getElementById('id').value = '';
    editingBadge.classList.remove('show');
    setStatus('', '');
    setLastAction('Ready');
}

function renderRows(rows) {
    if (!rows.length) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="empty">
                    <div style="font-size: 42px; margin-bottom: 8px;">???</div>
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">No records found</div>
                    <div>Try a different search or add a new entry.</div>
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = rows.map(row => {
        const rowJson = encodeURIComponent(JSON.stringify(row));
        return `
            <tr>
                <td><span class="id-pill">${escapeHtml(row.id)}</span></td>
                <td><span class="tag">${escapeHtml(normalizeMacAddress(row.mac_address || '—'))}</span></td>
                <td>${escapeHtml(row.client_id ?? '—')}</td>
                <td>
                    <div class="actions">
                        <button type="button" class="button ghost small edit-btn" data-row="${rowJson}">Edit</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function applyFilter() {
    const term = searchBox.value.trim().toLowerCase();
    filteredRows = !term ? allRows : allRows.filter(row => {
        return [
            row.id,
            row.mac_address,
            row.client_id
        ].some(value => String(value ?? '').toLowerCase().includes(term));
    });
    currentPage = 1;
    renderCurrentPage();
}

function renderCurrentPage() {
    const term = searchBox.value.trim();
    const totalVisible = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(totalVisible / pageSize));
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    const startIndex = (currentPage - 1) * pageSize;
    const pageRows = filteredRows.slice(startIndex, startIndex + pageSize);

    visibleCount.textContent = totalVisible;
    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    prevPageBtn.disabled = currentPage <= 1;
    nextPageBtn.disabled = currentPage >= totalPages;

    if (!pageRows.length) {
        renderRows([]);
        tableMeta.textContent = term ? `No results for "${term}".` : 'No records available.';
        return;
    }

    renderRows(pageRows);
    const startNumber = totalVisible === 0 ? 0 : startIndex + 1;
    const endNumber = Math.min(startIndex + pageSize, totalVisible);
    tableMeta.textContent = term
        ? `Showing ${startNumber}-${endNumber} of ${totalVisible} result(s) for "${term}".`
        : `Showing ${startNumber}-${endNumber} of ${totalVisible} record(s).`;
}
async function loadData() {
    saveBtn.disabled = true;
    refreshBtn.disabled = true;
    tableMeta.textContent = 'Loading data from server...';

    try {
        const response = await fetch(dataUrl, { cache: 'no-store' });
        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Failed to load data');
        }

        allRows = Array.isArray(data.data) ? data.data : [];
        totalCount.textContent = allRows.length;
        filteredRows = allRows.slice();
        currentPage = 1;
        renderCurrentPage();
    } catch (error) {
        allRows = [];
        totalCount.textContent = '0';
        visibleCount.textContent = '0';
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" class="empty">
                    <div style="font-size: 42px; margin-bottom: 8px;">âš ï¸</div>
                    <div style="font-weight: 700; color: #b91c1c; margin-bottom: 4px;">Unable to load records</div>
                    <div>${escapeHtml(error.message)}</div>
                </td>
            </tr>
        `;
        setStatus(error.message, 'error');
        tableMeta.textContent = 'Load failed.';
    } finally {
        saveBtn.disabled = false;
        refreshBtn.disabled = false;
    }
}

function editRow(row) {
    document.getElementById('id').value = row.id || '';
    document.getElementById('mac_address').value = normalizeMacAddress(row.mac_address || '');
    document.getElementById('client_id').value = row.client_id || '';
    editingBadge.classList.add('show');
    editingBadge.textContent = `Editing record #${row.id}`;
    setStatus(`You are editing record #${row.id}. Make your changes and click Save Record.`, 'success');
    setLastAction(`Editing #${row.id}`);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

tableBody.addEventListener('click', (event) => {
    const button = event.target.closest('.edit-btn');
    if (!button) return;
    const row = JSON.parse(decodeURIComponent(button.dataset.row));
    editRow(row);
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
        id: document.getElementById('id').value.trim(),
        mac_address: normalizeMacAddress(document.getElementById('mac_address').value),
        client_id: document.getElementById('client_id').value.trim()
    };

    if (!payload.mac_address) {
        setStatus('MAC Address is required.', 'error');
        return;
    }

    document.getElementById('mac_address').value = payload.mac_address;

    setStatus('Saving record...', '');
    saveBtn.disabled = true;

    try {
        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (data.status !== 'success') {
            throw new Error(data.message || 'Save failed');
        }

        setStatus(data.message || 'Saved successfully.', 'success');
        setLastAction(payload.id ? `Updated #${payload.id}` : `Created #${data.id || 'new'}`);
        resetForm();
        await loadData();
    } catch (error) {
        setStatus(error.message, 'error');
    } finally {
        saveBtn.disabled = false;
    }
});

searchBox.addEventListener('input', applyFilter);
resetBtn.addEventListener('click', resetForm);
clearBtn.addEventListener('click', resetForm);
refreshBtn.addEventListener('click', loadData);
showAllBtn.addEventListener('click', () => {
    searchBox.value = '';
    applyFilter();
});
prevPageBtn.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage -= 1;
        renderCurrentPage();
    }
});
nextPageBtn.addEventListener('click', () => {
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
    if (currentPage < totalPages) {
        currentPage += 1;
        renderCurrentPage();
    }
});

loadData();
</script>
</body>
</html>
