<?php
include 'function.php';
$itemObj = new items();
$id = $_GET['id'];
$itemObj->deleteItem($id);
echo ".
<script>
alert('Item deleted successfully');
        window.location.href = 'index.php';
    </script
";
?>