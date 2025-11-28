<?php
// ItemRepository.php
require_once "pdo.php";

class ItemRepository {
    private $pdo;
    private const LOW_STOCK_THRESHOLD = 10;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function filter(?string $category, ?string $stockStatus): array {
        $conditions = [];
        $params = [];

        if ($category) {
            $conditions[] = "category = :category";
            $params[':category'] = $category;
        }

        if ($stockStatus) {
            switch ($stockStatus) {
                case 'Available':
                    $conditions[] = "stock_qty >= :avail";
                    $params[':avail'] = self::LOW_STOCK_THRESHOLD;
                    break;
                case 'Low Stock':
                    $conditions[] = "stock_qty > 0 AND stock_qty < :low";
                    $params[':low'] = self::LOW_STOCK_THRESHOLD;
                    break;
                case 'Out of Stock':
                    $conditions[] = "stock_qty = 0";
                    break;
            }
        }

        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT * FROM items $where ORDER BY item_name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['stock_status'] = $this->computeStockStatus((int)$item['stock_qty']);
        }
        return $items;
    }

    public function summary(?string $category): array {
        $params = [];
        $catWhere = "";
        if ($category) {
            $catWhere = "WHERE category = :category";
            $params[':category'] = $category;
        }

        $total = $this->countQuery("SELECT COUNT(*) FROM items $catWhere", $params);
        $low   = $this->countQuery("SELECT COUNT(*) FROM items $catWhere" . ($catWhere ? " AND" : " WHERE") . " stock_qty > 0 AND stock_qty < :low", array_merge($params, [':low'=>self::LOW_STOCK_THRESHOLD]));
        $out   = $this->countQuery("SELECT COUNT(*) FROM items $catWhere" . ($catWhere ? " AND" : " WHERE") . " stock_qty = 0", $params);

        return ['total'=>$total, 'low'=>$low, 'out'=>$out];
    }

    private function countQuery(string $sql, array $params): int {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function computeStockStatus(int $stock): string {
        if ($stock === 0) return "Out of Stock";
        if ($stock < self::LOW_STOCK_THRESHOLD) return "Low Stock";
        return "Available";
    }
}
