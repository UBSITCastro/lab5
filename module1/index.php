<?php
include 'function.php';
$itemObj = new items(); 
if (isset($_POST['add'])) {
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $stock_qty = $_POST['stock_qty'];
    $unit = $_POST['unit'];
    $added_date = $_POST['added_date'];

    $itemObj->createItem($item_name, $category, $stock_qty, $unit, $added_date);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>
    <h1>Add Items</h1>
    <table>
        <form action="" method="post">
            <tr>
                <td><label for="item_name">Item Name:</label></td>
                <td><input type="text" id="item_name" name="item_name" required></td>
            </tr>
            <tr>
                <td><label for="category">Category:</label></td>
                <td><input type="text" id="category" name="category" required></td>
            </tr>
            <tr>
                <td><label for="stock_qty">Stock Quantity:</label></td>
                <td><input type="number" id="stock_qty" name="stock_qty" required></td>
            </tr>
            <tr>
                <td><label for="unit">Unit:</label></td>
                <td><input type="text" id="unit" name="unit" required></td>
            </tr>
            <tr>
                <td><label for="added_date">Added Date:</label></td>
                <td><input type="datetime-local" id="added_date" name="added_date" required></td>
            </tr>
            <tr>
                <td colspan="2">
                    <button name="add">Add Item</button>
                </td>
            </tr>
        </form>
    </table>
    </div>
    <div>
    <h1>Items List</h1>
    <table border="1">  
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Category</th>
            <th>Stock Quantity</th>
            <th>Unit</th>
            <th>Added Date</th>
            <th>Action</th>
        </tr>
        <?php
        $items = $itemObj->fetchItems();
        foreach ($items as $item) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['id']) . "</td>";
            echo "<td>" . htmlspecialchars($item['item_name']) . "</td>";
            echo "<td>" . htmlspecialchars($item['category']) . "</td>";
            echo "<td>" . htmlspecialchars($item['stock_qty']) . "</td>";
            echo "<td>" . htmlspecialchars($item['unit']) . "</td>";
            echo "<td>" . htmlspecialchars($item['added_date']) . "</td>";
            echo "<td>
                    <a href='edit.php?id=" . htmlspecialchars($item['id']) . "'>Edit</a> | 
                    <a href='delete.php?id=" . htmlspecialchars($item['id']) . "' onclick=\"return confirm('Are you sure you want to delete this item?');\">Delete</a>
                  </td>";
            echo "</tr>";
        }
        ?>
    </div>

</body>
</html>