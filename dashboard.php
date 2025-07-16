<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$tasks = [];
$earnings = 0;
if ($_SESSION['role'] === 'worker') {
    $stmt = $pdo->prepare("SELECT t.*, ta.status, ta.submitted_at FROM tasks t JOIN task_assignments ta ON t.id = ta.task_id WHERE ta.worker_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM earnings WHERE worker_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $earnings = $stmt->fetchColumn() ?: 0;
} else {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE requester_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MicroTask Platform</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .dashboard {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .dashboard h2 {
            color: #4facfe;
        }
        .tasks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .task-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .task-card h3 {
            margin: 0 0 10px;
            color: #4facfe;
        }
        .btn {
            padding: 10px 20px;
            background: #ff6f61;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #e55a50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <h2><?php echo $_SESSION['role'] === 'worker' ? 'Worker' : 'Requester'; ?> Dashboard</h2>
            <?php if ($_SESSION['role'] === 'worker'): ?>
                <p><strong>Total Earnings:</strong> $<?php echo number_format($earnings, 2); ?></p>
                <button class="btn" onclick="alert('Withdrawal feature coming soon!')">Withdraw Earnings</button>
            <?php endif; ?>
            <h3>Your Tasks</h3>
            <div class="tasks">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><strong>Status:</strong> <?php echo ucfirst($task['status']); ?></p>
                        <?php if ($_SESSION['role'] === 'worker' && $task['status'] === 'accepted'): ?>
                            <button class="btn" onclick="window.location.href='complete_task.php?id=<?php echo $task['id']; ?>'">Complete Task</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="btn" onclick="window.location.href='marketplace.php'">Go to Marketplace</button>
            <button class="btn" onclick="window.location.href='index.php'">Home</button>
        </div>
    </div>
</body>
</html>
