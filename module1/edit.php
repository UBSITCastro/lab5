<?php
include 'function.php';
$itemObj = new items(); 
$id = $_GET['id'];
$item = $itemObj->getItem($id);
if (isset($_POST['add'])) {
    $iid = $_POST['iid'];
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $stock_qty = $_POST['stock_qty'];
    $unit = $_POST['unit'];
    $added_date = $_POST['added_date'];

    
    if ($itemObj->updateItem($iid,$item_name, $category, $stock_qty, $unit, $added_date)){
    echo "
    <script>
    alert('Item updated successfully');
        window.location.href = 'index.php';
    </script>
    "; // Redirect after update
    exit();
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Item</title>
</head>
<body>
    <div>
    <h1>Update Item</h1>
    <table>
        <form action="" method="post">
            <input type="hidden" name="iid" value="<?php echo htmlspecialchars($item['id']); ?>">
            <tr>
                <td><label for="item_name">Item Name:</label></td>
                <td><input type="text" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="category">Category:</label></td>
                <td><input type="text" id="category" name="category" value="<?php echo htmlspecialchars($item['category']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="stock_qty">Stock Quantity:</label></td>
                <td><input type="number" id="stock_qty" name="stock_qty" value="<?php echo htmlspecialchars($item['stock_qty']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="unit">Unit:</label></td>
                <td><input type="text" id="unit" name="unit" value="<?php echo htmlspecialchars($item['unit']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="added_date">Added Date:</label></td>
                <td><input type="datetime-local" id="added_date" name="added_date" value="<?php echo date('Y-m-d\TH:i', strtotime($item['added_date'])); ?>" required></td>
            </tr>
            <tr>
                <td colspan="2">
                    <button name="add">Update Item</button>
                </td>
            </tr>
        </form>
    </table>
    </div>

</body>
</html>