<?php

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
                <td><input type="date" id="added_date" name="added_date" required></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="Add Item"></td>
            </tr>
        </form>
    </table>
    </div>

</body>
</html>