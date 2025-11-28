<?php
require_once 'db.php';

header('Content-Type: application/json');

class RequestManager {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function logAction($action, $description) {
        $query = "INSERT INTO logs (action, description, timestamp) VALUES (:action, :description, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':action' => $action, ':description' => $description]);
    }

    public function searchItems($term) {
        $query = "SELECT id, item_name, stock_qty, unit FROM items WHERE item_name LIKE :term LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':term' => "%$term%"]);
        return $stmt->fetchAll();
    }

    public function getItemStock($id) {
        $query = "SELECT stock_qty, unit FROM items WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function createRequest($name, $itemId, $qty) {
        $stockData = $this->getItemStock($itemId);
        if (!$stockData || $stockData['stock_qty'] < $qty) {
            return ['status' => 'error', 'message' => 'Insufficient stock available.'];
        }

        $query = "INSERT INTO requests (requester_name, item_id, quantity, status, request_date) 
                  VALUES (:name, :itemId, :qty, 'Pending', NOW())";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([':name' => $name, ':itemId' => $itemId, ':qty' => $qty])) {
            $this->logAction("New Request", "$name requested $qty of Item ID $itemId");
            return ['status' => 'success', 'message' => 'Request submitted successfully.'];
        }
        return ['status' => 'error', 'message' => 'Database error.'];
    }

    public function getRequests($filter) {
        $sql = "SELECT r.*, i.item_name, i.unit 
                FROM requests r 
                JOIN items i ON r.item_id = i.id";
        
        if ($filter !== 'All') {
            $sql .= " WHERE r.status = :status";
        }
        
        $sql .= " ORDER BY r.request_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($filter !== 'All') {
            $stmt->execute([':status' => $filter]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    public function processRequest($requestId, $action) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT item_id, quantity, status FROM requests WHERE id = :id");
            $stmt->execute([':id' => $requestId]);
            $request = $stmt->fetch();

            if (!$request) throw new Exception("Request not found.");
            if ($request['status'] !== 'Pending') throw new Exception("Request already processed.");

            $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';

            if ($action === 'approve') {
                $stockData = $this->getItemStock($request['item_id']);
                if ($stockData['stock_qty'] < $request['quantity']) {
                    throw new Exception("Cannot approve: Insufficient stock.");
                }

                $updateStock = $this->conn->prepare("UPDATE items SET stock_qty = stock_qty - :qty WHERE id = :id");
                $updateStock->execute([':qty' => $request['quantity'], ':id' => $request['item_id']]);
                
                $this->logAction("Stock Update", "Deducted " . $request['quantity'] . " from Item ID " . $request['item_id']);
            }

            $updateReq = $this->conn->prepare("UPDATE requests SET status = :status WHERE id = :id");
            $updateReq->execute([':status' => $newStatus, ':id' => $requestId]);

            $this->logAction("Request Processed", "Request #$requestId was $newStatus");

            $this->conn->commit();
            return ['status' => 'success', 'message' => "Request $newStatus successfully."];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}


$manager = new RequestManager();
$data = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'search':
        echo json_encode($manager->searchItems($_GET['term']));
        break;

    case 'check_stock':
        echo json_encode($manager->getItemStock($_GET['id']));
        break;

    case 'submit_request':
        echo json_encode($manager->createRequest($data['name'], $data['item_id'], $data['qty']));
        break;

    case 'fetch_requests':
        echo json_encode($manager->getRequests($_GET['filter'] ?? 'All'));
        break;

    case 'process_request':
        echo json_encode($manager->processRequest($data['id'], $data['process_action']));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>