<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'connection.php';

// Set security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");

$user_id = $_SESSION['user_id'];
$date_today = date('Y-m-d');
$date_yesterday = date('Y-m-d', strtotime('-1 day'));

// 1. Fetch user data
$stmt = $conn->prepare("SELECT current_streak, last_quiz_date, streak_restores_used, restore_reset_date, previous_streak FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$current_streak = $user['current_streak'];
$last_quiz_date = $user['last_quiz_date'];
$restores_used = $user['streak_restores_used'];
$reset_date = $user['restore_reset_date'];
$previous_streak = $user['previous_streak'];

// 2. Fetch Latest Announcement
$announcement_res = $conn->query("SELECT message FROM announcements WHERE id = 1");
$announcement = $announcement_res->fetch_assoc()['message'] ?? "Welcome to StudyVerse!";

// 3. Update Last Login
$conn->query("UPDATE users SET last_login_date = NOW() WHERE id = $user_id");

// 4. Weekly Reset
if (strtotime($reset_date) <= strtotime('-7 days')) {
    $conn->query("UPDATE users SET streak_restores_used = 0, restore_reset_date = '$date_today' WHERE id = $user_id");
    $restores_used = 0;
}

// 5. Snapchat Logic
$can_restore = false;
if ($last_quiz_date !== $date_today && $last_quiz_date !== $date_yesterday) {
    if ($current_streak > 0) {
        $conn->query("UPDATE users SET previous_streak = $current_streak, current_streak = 0 WHERE id = $user_id");
        $previous_streak = $current_streak;
        $current_streak = 0;
    }
    if ($previous_streak > 0 && $restores_used < 3) {
        $can_restore = true;
    }
}

// 6. Handle Restore Action
if (isset($_POST['restore_action']) && $can_restore) {
    $conn->query("UPDATE users SET current_streak = previous_streak, previous_streak = 0, streak_restores_used = streak_restores_used + 1, last_quiz_date = '$date_yesterday' WHERE id = $user_id");
    header("Location: dashboard.php"); 
    exit;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyVerse</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            margin: 0;
        }
        nav {
            position: fixed; top: 0; right: 0; background: rgba(0, 0, 0, 0.9);
            width: 200px; height: 100%; display: none; flex-direction: column;
            justify-content: center; align-items: center; z-index: 1000;
        }
        nav.open { display: flex; }
        nav ul { list-style-type: none; padding: 0; margin: 0; }
        nav ul li { margin: 20px 0; }
        nav ul li a { color: white; text-decoration: none; font-size: 18px; padding: 10px 20px; display: block; text-align: center; }
        
        .menu-btn {
            position: fixed; top: 20px; right: 20px; background: none; border: none;
            font-size: 30px; color: white; cursor: pointer; z-index: 1001;
        }
        
        .container {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-height: 85vh; width: 100%; max-width: 1200px; margin: auto; padding: 20px;
            text-align: center; box-sizing: border-box;
        }

        .streak-badge {
            display: inline-flex; align-items: center; background: rgba(34, 34, 34, 0.9);
            padding: 10px 25px; border-radius: 12px; border: 1px solid #444;
            margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .streak-badge .icon {
            font-size: 2.2rem; margin-right: 15px;
            filter: <?= ($current_streak > 0) ? 'drop-shadow(0 0 8px #ff9900)' : 'grayscale(1)'; ?>;
        }
        .streak-badge .count {
            display: block; font-size: 1.6rem; font-weight: bold; color: #00ffcc; line-height: 1;
        }

        /* --- Announcement Slider Styling --- */
        .announcement-container {
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            background: rgba(0, 255, 204, 0.1);
            border: 1px solid rgba(0, 255, 204, 0.3);
            border-radius: 50px;
            padding: 8px 20px;
            margin-bottom: 30px;
            white-space: nowrap;
            position: relative;
        }
        .announcement-text {
            display: inline-block;
            font-weight: bold;
            color: #00ffcc;
            animation: slideInOut 15s linear infinite;
        }
        @keyframes slideInOut {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        /* ---------------------------------- */

        .hourglass-timer {
            margin-left: 15px; color: #ffcc00; font-weight: bold; display: none;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

        .restore-alert {
            background: rgba(255, 68, 68, 0.2); border: 1px solid #ff4444;
            padding: 15px; border-radius: 10px; margin-bottom: 25px;
        }
        .restore-btn {
            background: #ff4444; color: white; border: none; padding: 10px 20px;
            border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 10px;
        }

        .clearfix { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; width: 100%; margin-top: 20px; }
        .box {
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(5px);
            padding: 25px; border-radius: 12px; width: 280px; text-align: center;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }
        .box:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.15); }
        .box h2 { color: #00ffcc; margin-top: 0; }

        .link {
            display: block; padding: 10px; background-color: #007bff;
            color: white; border-radius: 8px; margin-top: 10px;
            text-decoration: none; font-weight: bold;
        }

        footer { background-color: rgba(0,0,0,0.8); padding: 15px 0; margin-top: 40px; }
    </style>
</head>
<body>
    <button class="menu-btn" onclick="toggleMenu()" aria-label="Toggle Menu">☰</button>
    <nav id="menu">
        <ul>
            <li><img src="logo.jpg" alt="Logo" style="height: 80px; width: auto; border-radius: 50%; margin-bottom: 10px;"></li>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="#">Profile</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="suggest.php" style="color: var(--primary);">Suggest & Testify</a></li>
            <li><a href="admin_login.php">Admin</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Welcome to StudyVerse</h1>
        
        <?php if ($can_restore): ?>
        <div class="restore-alert">
            <p>Oh no! Your <b><?= $previous_streak ?></b> day streak has ended.</p>
            <form method="POST">
                <button type="submit" name="restore_action" class="restore-btn">
                    Restore Streak (<?= 3 - $restores_used ?> chances left this week)
                </button>
            </form>
        </div>
        <?php endif; ?>

        <div class="streak-badge">
            <span class="icon"><?= ($current_streak > 0) ? '🔥' : '💀'; ?></span>
            <div class="text-group">
                <span class="count"><?= $current_streak; ?></span>
                <span class="label" style="font-size: 0.75rem; color: #aaa;">Study Streak</span>
            </div>
            <div id="hourglass" class="hourglass-timer" title="Your streak ends at midnight!">
                ⌛ <span id="countdown-text"></span>
            </div>
        </div>

        <div class="announcement-container">
            <div class="announcement-text">
                📢 <?= htmlspecialchars($announcement); ?>
            </div>
        </div>
        
        <div class="clearfix">
            <?php
            for ($level = 100; $level <= 600; $level += 100) {
                echo "<div class='box'>";
                echo "<h2>{$level} Level</h2>";
                echo "<a href='semester.php?level={$level}&semester=1st' class='link'>1st Semester</a>";
                echo "<a href='semester.php?level={$level}&semester=2nd' class='link'>2nd Semester</a>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y'); ?> StudyVerse. All rights reserved.</p>
    </footer>

    <script>
        function toggleMenu() {
            document.getElementById('menu').classList.toggle('open');
        }

        function updateCountdown() {
            const now = new Date();
            const midnight = new Date();
            midnight.setHours(24, 0, 0, 0);

            const diff = midnight - now;
            const hoursLeft = diff / (1000 * 60 * 60);

            const currentStreak = <?= $current_streak ?>;
            const lastQuizDate = "<?= $last_quiz_date ?>";
            const todayStr = "<?= $date_today ?>";

            if (currentStreak > 0 && lastQuizDate !== todayStr && hoursLeft <= 12) {
                document.getElementById('hourglass').style.display = 'inline-block';
                const h = Math.floor(hoursLeft);
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                document.getElementById('countdown-text').innerText = h + "h " + m + "m";
            } else {
                document.getElementById('hourglass').style.display = 'none';
            }
        }

        setInterval(updateCountdown, 60000);
        updateCountdown();
    </script>
</body>
</html>