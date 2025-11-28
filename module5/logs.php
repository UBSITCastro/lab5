<?php

$host = "localhost";
$dbname = "inventory_system";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$query = "SELECT * FROM logs_table";
$params = [];

if ($start_date && $end_date) {
    $query .= " WHERE DATE(timestamp) BETWEEN :start_date AND :end_date";
    $params = [
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ];
}

$query .= " ORDER BY timestamp DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Activity Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Activity Logs</h2>

  
    <form method="get" action="">
        <label>Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"></label>
        <label>End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"></label>
        <button type="submit">Filter</button>
        <button type="button" onclick="window.location.href='logs.php'">Reset</button>
    </form>

   
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Action</th>
                <th>Description</th>
                <th>Date/Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($logs) > 0): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['id']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['description']) ?></td>
                        <td><?= htmlspecialchars($log['timestamp']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="color:#f39c12;">No logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
