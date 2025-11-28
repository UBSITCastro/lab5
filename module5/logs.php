<?php
// logs.php

// Database connection
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

// Optional date filtering
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$query = "SELECT * FROM logs";
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
    <title>Activity Logs</title>
    <style>
        table { border-collapse: collapse; width: 90%; margin: 20px auto; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Activity Logs</h2>

    <form method="get" action="">
        <label>Start Date: <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"></label>
        <label>End Date: <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"></label>
        <button type="submit">Filter</button>
        <a href="logs.php">Reset</a>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Action</th>
            <th>Description</th>
            <th>Date/Time</th>
        </tr>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['id']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['description']) ?></td>
                <td><?= htmlspecialchars($log['timestamp']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
