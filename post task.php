<?php
session_start();
require_once 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is a requester
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requester') {
    echo "<script>alert('You must be logged in as a requester to post tasks.'); window.location.href='login.php';</script>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $payment = floatval($_POST['payment'] ?? 0);
    $deadline = $_POST['deadline'] ?? '';

    // Validate inputs
    if (empty($title) || empty($description) || empty($category) || $payment <= 0 || empty($deadline)) {
        $error = "All fields are required, and payment must be greater than 0.";
    } elseif (!in_array($category, ['data_entry', 'survey', 'transcription', 'other'])) {
        $error = "Invalid category selected.";
    } else {
        try {
            // Convert deadline to MySQL DATETIME format
            $deadline_formatted = date('Y-m-d H:i:s', strtotime($deadline));
            if (!$deadline_formatted) {
                $error = "Invalid deadline format.";
            } else {
                // Prepare and execute the insert query
                $stmt = $pdo->prepare("INSERT INTO tasks (requester_id, title, description, category, payment, deadline) VALUES (?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$_SESSION['user_id'], $title, $description, $category, $payment, $deadline_formatted]);

                if ($result) {
                    $success = "Task posted successfully!";
                    // Log query success for debugging
                    error_log("Task posted: Title=$title, UserID={$_SESSION['user_id']}, Time=" . date('Y-m-d H:i:s'));
                    echo "<script>window.location.href='marketplace.php';</script>";
                    exit;
                } else {
                    $error = "Failed to post task. Please try again.";
                    error_log("Task post failed: Title=$title, UserID={$_SESSION['user_id']}");
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Database error in post_task.php: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Task - MicroTask Platform</title>
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
            margin-bottom: 20px;
        }
        .form-box input, .form-box select, .form-box textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1em;
        }
        .form-box textarea {
            height: 100px;
            resize: vertical;
        }
        .form-box button {
            width: 100%;
            padding: 12px;
            background: #ff6f61;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s;
        }
        .form-box button:hover {
            background: #e55a50;
        }
        .error, .success {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            background: #ffe6e6;
            color: #d32f2f;
        }
        .success {
            background: #e6ffe6;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Post a New Task</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="post_task.php">
            <input type="text" name="title" placeholder="Task Title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            <textarea name="description" placeholder="Task Description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            <select name="category" required>
                <option value="" disabled <?php echo !isset($_POST['category']) ? 'selected' : ''; ?>>Select Category</option>
                <option value="data_entry" <?php echo ($_POST['category'] ?? '') === 'data_entry' ? 'selected' : ''; ?>>Data Entry</option>
                <option value="survey" <?php echo ($_POST['category'] ?? '') === 'survey' ? 'selected' : ''; ?>>Survey</option>
                <option value="transcription" <?php echo ($_POST['category'] ?? '') === 'transcription' ? 'selected' : ''; ?>>Transcription</option>
                <option value="other" <?php echo ($_POST['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
            <input type="number" name="payment" step="0.01" min="0.01" placeholder="Payment ($)" value="<?php echo htmlspecialchars($_POST['payment'] ?? ''); ?>" required>
            <input type="datetime-local" name="deadline" value="<?php echo htmlspecialchars($_POST['deadline'] ?? ''); ?>" required>
            <button type="submit">Post Task</button>
        </form>
        <button type="button" onclick="window.location.href='marketplace.php'">Back to Marketplace</button>
    </div>
</body>
</html>
