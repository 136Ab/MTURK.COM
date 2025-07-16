<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$task_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SESSION['role'] === 'worker' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO task_assignments (task_id, worker_id, status) VALUES (?, ?, 'applied')");
    $stmt->execute([$task_id, $_SESSION['user_id']]);
    echo "<script>window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - MicroTask Platform</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .task-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 600px;
        }
        .task-box h2 {
            color: #4facfe;
        }
        .task-box p {
            margin: 10px 0;
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
    <div class="task-box">
        <h2><?php echo htmlspecialchars($task['title']); ?></h2>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($task['description']); ?></p>
        <p><strong>Category:</strong> <?php echo ucfirst($task['category']); ?></p>
        <p><strong>Payment:</strong> $<?php echo number_format($task['payment'], 2); ?></p>
        <p><strong>Deadline:</strong> <?php echo date('M d, Y H:i', strtotime($task['deadline'])); ?></p>
        <?php if ($_SESSION['role'] === 'worker' && $task['status'] === 'open'): ?>
            <form method="POST">
                <button type="submit" class="btn">Apply for Task</button>
            </form>
        <?php endif; ?>
        <button class="btn" onclick="window.location.href='marketplace.php'">Back to Marketplace</button>
    </div>
</body>
</html>
