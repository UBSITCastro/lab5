<?php 
class items{
private $pdo;

    public function __construct() {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'inventory_system';
        $charset = 'utf8mb4';

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("connection failed: " . $e->getMessage());
        }
    }
      public function fetchItems(){
        $fetch = $this->pdo->query("SELECT * FROM items_table");
        return $fetch->fetchAll();
    }

    public function getItem($id) {
        $stmt = $this->pdo->prepare(" SELECT * FROM items_table WHERE id = :id ");
        $stmt->execute([
            ':id' => $id
        ]);
        $row = $stmt->fetch();
        return $row;
    }

    public function createItem($item_name, $category, $stock_qty, $unit, $added_date) {
        $stmt = $this->pdo->prepare("INSERT INTO items_table (item_name, category, stock_qty, unit, added_date) VALUES (:item_name, :category, :stock_qty, :unit, :added_date)");
        return $stmt->execute([
            ':item_name' => $item_name,
            ':category' => $category,
            ':stock_qty' => $stock_qty,
            ':unit' => $unit,
            ':added_date' => $added_date,
        ]);
    }
    public function updateItem(int $id, $item_name, $category, $stock_qty, $unit, $added_date): bool {
        $stmt = $this->pdo->prepare("UPDATE items_table SET item_name = :item_name, category = :category, stock_qty = :stock_qty, unit = :unit, added_date = :added_date WHERE id = :id");
        return $stmt->execute([
            ':item_name' => $item_name,
            ':category' => $category,
            ':stock_qty' => $stock_qty,
            ':unit' => $unit,
            ':added_date' => $added_date,
            ':id' => $id,
        ]);
    }


    public function deleteItem(int $id) {
        $stmt = $this->pdo->prepare("DELETE FROM items_table WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
?>