<?php
session_start();
require_once 'db.php';
$tasks = $pdo->query("SELECT * FROM tasks WHERE status = 'open' LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MicroTask Platform - Home</title>
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
        header {
            text-align: center;
            padding: 50px 0;
        }
        header h1 {
            font-size: 3em;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .btn {
            padding: 10px 20px;
            background: #ff6f61;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
            margin: 10px;
        }
        .btn:hover {
            background: #e55a50;
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
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome to MicroTask Platform</h1>
            <p>Earn money by completing small tasks or post tasks to get work done!</p>
            <button class="btn" onclick="window.location.href='signup.php'">Sign Up as Worker</button>
            <button class="btn" onclick="window.location.href='signup.php?role=requester'">Sign Up as Requester</button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn" onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
            <?php else: ?>
                <button class="btn" onclick="window.location.href='login.php'">Login</button>
            <?php endif; ?>
        </header>
        <section>
            <h2>Featured Tasks</h2>
            <div class="tasks">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?>...</p>
                        <p><strong>Payment:</strong> $<?php echo number_format($task['payment'], 2); ?></p>
                        <button class="btn" onclick="window.location.href='task_details.php?id=<?php echo $task['id']; ?>'">View Task</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</body>
</html>
