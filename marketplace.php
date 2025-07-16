<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$category = $_GET['category'] ?? '';
$where = $category ? "WHERE category = ?" : "";
$stmt = $pdo->prepare("SELECT * FROM tasks $where ORDER BY created_at DESC");
$tasks = $category ? $stmt->execute([$category]) && $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->execute() && $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Marketplace - MicroTask Platform</title>
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
        .filters {
            margin-bottom: 20px;
        }
        .filters select {
            padding: 10px;
            border-radius: 5px;
        }
        .tasks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .task-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .task-card:hover {
            transform: translateY(-5px);
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
        <h2>Task Marketplace</h2>
        <?php if ($_SESSION['role'] === 'requester'): ?>
            <button class="btn" onclick="window.location.href='post_task.php'">Post a Task</button>
        <?php endif; ?>
        <div class="filters">
            <select onchange="window.location.href='marketplace.php?category=' + this.value">
                <option value="">All Categories</option>
                <option value="data_entry" <?php if ($category === 'data_entry') echo 'selected'; ?>>Data Entry</option>
                <option value="survey" <?php if ($category === 'survey') echo 'selected'; ?>>Survey</option>
                <option value="transcription" <?php if ($category === 'transcription') echo 'selected'; ?>>Transcription</option>
                <option value="other" <?php if ($category === 'other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
        <div class="tasks">
            <?php foreach ($tasks as $task): ?>
                <div class="task-card">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?>...</p>
                    <p><strong>Payment:</strong> $<?php echo number_format($task['payment'], 2); ?></p>
                    <p><strong>Deadline:</strong> <?php echo date('M d, Y', strtotime($task['deadline'])); ?></p>
                    <button class="btn" onclick="window.location.href='task_details.php?id=<?php echo $task['id']; ?>'">View Task</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
