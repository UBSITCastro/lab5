<?php
require_once "itemrepo.php";

if (isset($_POST['ajax'])) {
    $category = $_POST['category'] ?? null;
    $stockStatus = $_POST['stock_status'] ?? null;

    $repo = new ItemRepository();
    $items = $repo->filter($category, $stockStatus);
    $summary = $repo->summary($category);

    echo json_encode([
        "items" => $items,
        "summary" => [
            "totalItems" => $summary['total'],
            "lowStockItems" => $summary['low'],
            "outOfStockItems" => $summary['out']
        ]
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <title>Inventory Filter</title>
</head>
<body>
  <h1>Inventory Filter</h1>

  <label>Category:
    <select id="filter-category">
      <option value="">All</option>
      <option>Pens</option>
      <option>Papers</option>
      <option>Equipment</option>
      <option>Others</option>
    </select>
  </label>

  <label style="margin-left:1rem;">Stock Status:
    <select id="filter-stock">
      <option value="">All</option>
      <option>Available</option>
      <option>Low Stock</option>
      <option>Out of Stock</option>
    </select>
  </label>

  <div class="summary">
    <div>Total Items: <span id="sum-total">0</span></div>
    <div>Low Stock Items: <span id="sum-low">0</span></div>
    <div>Out of Stock Items: <span id="sum-out">0</span></div>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th><th>Name</th><th>Category</th><th>Stock</th><th>Status</th><th>Unit</th><th>Added</th>
      </tr>
    </thead>
    <tbody id="items-body"></tbody>
  </table>

<script>
const categoryEl = document.getElementById('filter-category');
const stockEl = document.getElementById('filter-stock');
const tbody = document.getElementById('items-body');

async function loadItems() {
  const formData = new FormData();
  formData.append('ajax', '1');
  if (categoryEl.value) formData.append('category', categoryEl.value);
  if (stockEl.value) formData.append('stock_status', stockEl.value);

  const res = await fetch('items.php', {
    method: 'POST',
    body: formData
  });
  const data = await res.json();

  document.getElementById('sum-total').textContent = data.summary.totalItems;
  document.getElementById('sum-low').textContent = data.summary.lowStockItems;
  document.getElementById('sum-out').textContent = data.summary.outOfStockItems;

  tbody.innerHTML = '';
  data.items.forEach((item, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx+1}</td>
      <td>${item.item_name}</td>
      <td>${item.category}</td>
      <td>${item.stock_qty}</td>
      <td class="${item.stock_status.replace(/\s/g,'.')}">${item.stock_status}</td>
      <td>${item.unit}</td>
      <td>${item.added_date}</td>
    `;
    tbody.appendChild(tr);
  });
}

categoryEl.addEventListener('change', loadItems);
stockEl.addEventListener('change', loadItems);
document.addEventListener('DOMContentLoaded', loadItems);
</script>
</body>
</html>
