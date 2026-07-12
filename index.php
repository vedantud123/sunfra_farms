<?php
// index.php - simple safe replacement
// Temporary: show errors to help debugging. Remove or set to 0 after done.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// ensure session started (db.php already calls session_start())
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// get category filter (safe integer casting)
$cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

// Build SQL safely. We'll use prepared statements for the items query when filter is present.
try {
    if ($cat > 0) {
        $stmt = $conn->prepare("SELECT i.*, c.name AS category_name FROM ingredients i LEFT JOIN categories c ON i.category_id = c.id WHERE i.category_id = ? ORDER BY i.id DESC");
        $stmt->bind_param("i", $cat);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query("SELECT i.*, c.name AS category_name FROM ingredients i LEFT JOIN categories c ON i.category_id = c.id ORDER BY i.id DESC");
    }
} catch (Exception $e) {
    // show a friendly DB error
    echo "<p style='color:red'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// fetch categories for filter dropdown
$cats = $conn->query("SELECT id, name FROM categories ORDER BY name");

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Inventory Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* small fallback styles if style.css missing */
    body { font-family: Arial, sans-serif; margin: 20px; background:#fff; color:#222; }
    table { border-collapse: collapse; width: 100%; margin-top: 15px; }
    table, th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    a { color:#007bff; text-decoration:none; margin-right:10px; }
    a:hover { text-decoration:underline; }
  </style>
</head>
<body>
<h2>🍴 Restaurant Inventory</h2>

<p>Welcome, <?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User'); ?> |
 <a href="logout.php">Logout</a></p>

<div style="margin-bottom:12px;">
  <a href="add_item.php">➕ Add Item</a> |
  <a href="add_category.php">➕ Add Category</a> |
  <a href="receive.php">📦 Receive Stock</a> |
  <a href="consume.php">🍳 Consume Stock</a> |
  <a href="stock_history.php">📋 Stock History</a>
</div>

<form method="get" style="margin-bottom:10px;">
  <label>Filter category:</label>
  <select name="cat" onchange="this.form.submit()">
    <option value="0">All</option>
    <?php
    if ($cats) {
        while ($c = $cats->fetch_assoc()) {
            $selected = ($cat && $cat == $c['id']) ? 'selected' : '';
            echo "<option value=\"" . intval($c['id']) . "\" $selected>" . htmlspecialchars($c['name']) . "</option>";
        }
    }
    ?>
  </select>
  <noscript><button type="submit">Filter</button></noscript>
</form>

<table>
  <tr><th>ID</th><th>Item</th><th>Category</th><th>Quantity</th><th>Unit</th></tr>
  <?php
  if ($res && $res->num_rows > 0) {
      while ($row = $res->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . intval($row['id']) . "</td>";
          echo "<td>" . htmlspecialchars($row['name']) . "</td>";
          echo "<td>" . htmlspecialchars($row['category_name'] ?? '') . "</td>";
          echo "<td>" . htmlspecialchars($row['qty']) . "</td>";
          echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
          echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='5'>No items found.</td></tr>";
  }
  ?>
</table>

</body>
</html>
