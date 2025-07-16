<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || $_SESSION['role'] !== 'worker') {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$task_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM task_assignments WHERE task_id = ? AND worker_id = ? AND status = 'accepted'");
$stmt->execute([$task_id, $_SESSION['user_id']]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE task_assignments SET status = 'completed', submitted_at = NOW() WHERE id = ?");
    $stmt->execute([$assignment['id']]);
    
    $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE id = ?");
    $stmt->execute([$task_id]);
    
    $stmt = $pdo->prepare("SELECT payment FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $payment = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("INSERT INTO earnings (worker_id, task_id, amount) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $task_id, $payment]);
    
    if ($rating) {
        $stmt = $pdo->prepare("INSERT INTO reviews (task_id, worker_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$task_id, $_SESSION['user_id'], $rating, $comment]);
    }
    
    $pdo->commit();
    echo "<script>window.location.href='dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Task - MicroTask Platform</title>
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
        .form-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        .form-box h2 {
            text-align: center;
            color: #4facfe;
        }
        .form-box select, .form-box textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-box button {
            width: 100%;
            padding: 10px;
            background: #ff6f61;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-box button:hover {
            background: #e55a50;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Complete Task</h2>
        <form method="POST">
            <select name="rating">
                <option value="">Select Rating (Optional)</option>
                <option value="1">1 Star</option>
                <option value="2">2 Stars</option>
                <option value="3">3 Stars</option>
                <option value="4">4 Stars</option>
                <option value="5">5 Stars</option>
            </select>
            <textarea name="comment" placeholder="Review (Optional)"></textarea>
            <button type="submit">Submit Task</button>
        </form>
    </div>
</body>
</html>
