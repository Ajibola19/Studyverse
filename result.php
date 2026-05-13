<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'connection.php';

$user_id = $_SESSION['user_id'];
$date_today = date('Y-m-d');

// 1. Get current streak and last quiz date
$stmt = $conn->prepare("SELECT current_streak, last_quiz_date FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$current_streak = $row['current_streak'] ?? 0;
$last_quiz_date = $row['last_quiz_date'];

// 2. Snapchat Streak Logic
if ($last_quiz_date === $date_today) {
    // Already updated today, just keep current value for display
} else {
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    if ($last_quiz_date === $yesterday) {
        $current_streak++; // Streak continues from yesterday
    } else {
        $current_streak = 1; // Streak was broken, restart at 1
    }

    // Update database with the new streak and today's date
    $update = $conn->prepare("UPDATE users SET current_streak = ?, last_quiz_date = ? WHERE id = ?");
    $update->bind_param("isi", $current_streak, $date_today, $user_id);
    $update->execute();
    $update->close();
}

$stmt->close();

// 3. Handle Quiz Data
$score = $_POST['score'] ?? 0;
$total = $_POST['total'] ?? 0;
$details = $_POST['details'] ?? null;

if (!$details) {
    die("No result details found.");
}

$decoded = json_decode($details, true);
if (!$decoded || !is_array($decoded)) {
    die("Invalid result details.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result | StudyVerse</title>
    <style>
        body {
            background: #121212;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        /* Streak Badge Styling (Matches Home Page) */
        .streak-celebration {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px 30px;
            border-radius: 50px;
            border: 1px solid #444;
            margin-bottom: 25px;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .streak-celebration .icon {
            font-size: 2.5rem;
            margin-right: 15px;
            filter: drop-shadow(0 0 10px #ff9900);
        }

        .streak-celebration .count {
            font-size: 1.8rem;
            font-weight: bold;
            color: #00ffcc;
        }

        .streak-celebration .label {
            display: block;
            font-size: 0.8rem;
            color: #aaa;
            text-transform: uppercase;
        }

        h2 {
            color: #00ffcc;
            font-size: 2rem;
            margin-top: 0;
        }

        .score-box {
            font-size: 1.4rem;
            background: rgba(0, 255, 204, 0.1);
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 30px;
            border: 1px solid rgba(0, 255, 204, 0.3);
        }

        .details-header {
            text-align: left;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-size: 1.2rem;
            color: #aaa;
        }

        .result-item {
            text-align: left;
            border-bottom: 1px solid #333;
            padding: 20px 0;
        }

        .result-item p { margin: 8px 0; }

        .correct { color: #4caf50; font-weight: bold; }
        .wrong { color: #f44336; font-weight: bold; }

        .label {
            color: #888;
            font-size: 0.9em;
            margin-right: 8px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 40px;
            padding: 12px 30px;
            background: #00ffcc;
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #00e6b8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.3);
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- New Streak Celebration Section -->
        <div class="streak-celebration">
            <span class="icon">🔥</span>
            <div class="text-group">
                <span class="count"><?= $current_streak ?></span>
                <span class="label">Day Streak!</span>
            </div>
        </div>

        <h2>Quiz Completed!</h2>
        <div class="score-box">
            Your Score: <strong><?= htmlspecialchars($score) ?> / <?= htmlspecialchars($total) ?></strong>
        </div>

        <div class="details-header">Review Answers</div>

        <?php foreach ($decoded as $entry): ?>
            <div class="result-item">
                <p><strong>Q:</strong> <?= htmlspecialchars($entry['question']) ?></p>
                
                <p>
                    <span class="label">Your Choice:</span>
                    <span class="<?= $entry['correct'] ? 'correct' : 'wrong' ?>">
                        <?= htmlspecialchars($entry['your_answer']) ?>
                    </span>
                </p>

                <?php if (!$entry['correct']): ?>
                <p>
                    <span class="label">Correct Answer:</span>
                    <span class="correct">
                        <?= htmlspecialchars($entry['correct_answer']) ?>
                    </span>
                </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <a class="back-btn" href="dashboard.php">Return to Dashboard</a>
    </div>

</body>
</html>