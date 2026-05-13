<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'connection.php';

$feedback_message = '';
$status = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];

    // 1. Logic for Private Suggestions (Feedback Table)
    if (isset($_POST['submit_suggestion'])) {
        $message = trim($_POST['message']);
        if(!empty($message)) {
            $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $message);
            if ($stmt->execute()) {
                $feedback_message = "Thank you! Your private suggestion has been sent.";
                $status = "success";
            } else {
                $feedback_message = "Error sending suggestion.";
                $status = "error";
            }
            $stmt->close();
        }
    }

    // 2. Logic for Public Testimonies (Testimony Table)
    if (isset($_POST['submit_testimony'])) {
        $message = trim($_POST['testimony']);
        $show_name = isset($_POST['show_name']) ? 1 : 0;
        
        if(!empty($message)) {
            $stmt = $conn->prepare("INSERT INTO testimony (user_id, message, show_name, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("isi", $user_id, $message, $show_name);
            if ($stmt->execute()) {
                $feedback_message = "Testimony submitted! It will appear on the home page once approved.";
                $status = "success";
            } else {
                $feedback_message = "Error submitting testimony.";
                $status = "error";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggest & Testify | StudyVerse</title>
    <style>
        :root {
            --primary: #00ffcc;
            --bg-dark: #0a0a0a;
            --glass: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url('bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            margin: 0;
            padding-top: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        nav {
            position: fixed; top: 0; right: 0; background: rgba(0, 0, 0, 0.95);
            width: 240px; height: 100%; display: flex; flex-direction: column;
            justify-content: center; align-items: center; z-index: 1000;
            transform: translateX(100%); transition: transform 0.4s ease;
            backdrop-filter: blur(10px); border-left: 1px solid var(--border);
        }
        nav.open { transform: translateX(0); }
        nav ul { list-style: none; padding: 0; width: 100%; }
        nav ul li { margin: 15px 0; text-align: center; }
        nav ul li a { color: #bbb; text-decoration: none; font-size: 1.1rem; padding: 12px; display: block; transition: 0.3s; }
        nav ul li a:hover { color: var(--primary); background: rgba(255,255,255,0.05); }

        .menu-btn {
            position: fixed; top: 25px; right: 25px; background: rgba(0,0,0,0.5);
            border: 1px solid var(--border); border-radius: 8px; font-size: 24px;
            color: white; cursor: pointer; z-index: 1001; padding: 5px 12px;
        }

        .container {
            width: 90%; max-width: 600px; background: var(--glass);
            backdrop-filter: blur(20px); padding: 30px; border-radius: 24px;
            border: 1px solid var(--border); box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            text-align: center; margin-bottom: 40px;
        }

        h1 { color: var(--primary); margin-bottom: 10px; font-size: 1.8rem; }
        .subtitle { color: #aaa; margin-bottom: 20px; font-size: 0.9rem; }

        textarea {
            width: 100%; height: 100px; padding: 15px; background: rgba(0, 0, 0, 0.3);
            border: 1px solid #444; border-radius: 12px; color: white;
            font-size: 0.95rem; resize: none; box-sizing: border-box; outline: none;
        }
        textarea:focus { border-color: var(--primary); }

        .btn-submit {
            margin-top: 15px; padding: 12px; background: var(--primary);
            color: #000; border: none; border-radius: 10px; font-weight: bold;
            cursor: pointer; width: 100%; transition: 0.3s;
        }
        .btn-submit:hover { background: #00e6b8; transform: translateY(-2px); }

        .divider { height: 1px; background: var(--border); margin: 30px 0; position: relative; }
        .divider span { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #111; padding: 0 15px; color: #555; font-size: 0.8rem; }

        .privacy-box { display: flex; align-items: center; gap: 10px; margin-top: 10px; font-size: 0.85rem; color: #ccc; justify-content: center; }

        .feedback-message { margin-top: 20px; padding: 12px; border-radius: 10px; font-size: 0.9rem; }
        .success { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71; }
        .error { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid #e74c3c; }
    </style>
</head>
<body>

    <button class="menu-btn" onclick="toggleMenu()">☰</button>
    <nav id="menu">
        <img src="logo.jpg" alt="Logo" style="height: 60px; width: 60px; border-radius: 50%; margin-bottom: 20px;">
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="suggest.php" style="color: var(--primary);">Suggest & Testify</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Private Suggestion</h1>
        <p class="subtitle">Sent directly to developers. Not shown publicly.</p>
        <form method="post">
            <textarea name="message" placeholder="Type your suggestion here..." required></textarea>
            <button type="submit" name="submit_suggestion" class="btn-submit">Send Suggestion</button>
        </form>

        <div class="divider"><span>OR</span></div>

        <h1>Public Testimony</h1>
        <p class="subtitle">Share your success story on our home page!</p>
        <form method="post">
            <textarea name="testimony" placeholder="How has StudyVerse helped you?" required></textarea>
            <div class="privacy-box">
                <input type="checkbox" name="show_name" id="show_name" value="1" checked>
                <label for="show_name">Display my name publicly</label>
            </div>
            <button type="submit" name="submit_testimony" class="btn-submit" style="background: #008cba; color: white;">Submit Testimony</button>
        </form>

        <?php if ($feedback_message): ?>
            <div class="feedback-message <?php echo $status; ?>">
                <?php echo $feedback_message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>function toggleMenu() { document.getElementById('menu').classList.toggle('open'); }</script>
</body>
</html>