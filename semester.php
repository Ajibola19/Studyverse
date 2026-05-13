<?php
require 'connection.php';

// Get parameters
$level = isset($_GET['level']) ? intval($_GET['level']) : 0;
// Handle both "1st" (string) and 1 (int)
$semester_raw = isset($_GET['semester']) ? $_GET['semester'] : 0;
$semester_display = htmlspecialchars($semester_raw);

if ($level <= 0 || !$semester_raw) {
    die("<div style='color:white; text-align:center; margin-top:50px;'>Invalid level or semester provided.</div>");
}

// Modes
$mode = isset($_GET['mode']) && $_GET['mode'] === 'instant' ? 'instant' : 'end';
$timing = isset($_GET['timing']) && $_GET['timing'] === 'untimed' ? 'untimed' : 'timed';

$sql = "SELECT id, subject_name FROM subjects WHERE level = ? AND semester = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $level, $semester_raw);
$stmt->execute();
$subjects_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects | StudyVerse</title>
    <style>
        :root {
            --primary-color: #00ffcc;
            --bg-dark: #0f0f0f;
            --card-bg: rgba(255, 255, 255, 0.05);
            --accent-glow: rgba(0, 255, 204, 0.2);
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(circle at top, #1a1a1a 0%, #0a0a0a 100%);
            color: #ffffff;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.2rem;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 15px var(--accent-glow);
        }

        /* Settings Bar */
        .settings-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 40px;
            border: 1px solid #333;
        }

        .toggle-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toggle-group label {
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
            font-weight: bold;
        }

        .toggle-group select {
            padding: 12px;
            background: #222;
            color: #fff;
            border: 1px solid #444;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            outline: none;
        }

        .toggle-group select:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        /* Subjects & Topics */
        .subject-card {
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .subject-header {
            background: rgba(0, 255, 204, 0.1);
            padding: 15px 25px;
            border-bottom: 1px solid rgba(0, 255, 204, 0.2);
        }

        .subject-header h2 {
            margin: 0;
            font-size: 1.4rem;
            color: var(--primary-color);
        }

        .topic-list {
            list-style: none;
            padding: 15px;
            margin: 0;
        }

        .topic-item {
            margin: 10px 0;
        }

        .topic-button {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            color: #ddd;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .topic-button:hover {
            background: rgba(0, 255, 204, 0.08);
            border-color: var(--primary-color);
            color: #fff;
            transform: translateX(5px);
        }

        .q-count {
            font-size: 0.8rem;
            background: #333;
            padding: 4px 10px;
            border-radius: 20px;
            color: var(--primary-color);
            font-weight: bold;
        }

        .back-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            color: #888;
            text-decoration: none;
            border: 1px solid #444;
            border-radius: 8px;
            transition: 0.3s;
        }

        .back-button:hover {
            color: #fff;
            background: #333;
        }

        @media (max-width: 600px) {
            .settings-bar { grid-template-columns: 1fr; }
            .container { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Level <?= htmlspecialchars($level) ?> <span style="color:#666;">|</span> <?= $semester_display ?> Sem</h1>

    <div class="settings-bar">
        <div class="toggle-group">
            <label>Feedback Style</label>
            <select id="feedbackMode" onchange="changeMode()">
                <option value="end" <?= $mode === 'end' ? 'selected' : '' ?>>Show Score at End</option>
                <option value="instant" <?= $mode === 'instant' ? 'selected' : '' ?>>Instant Feedback</option>
            </select>
        </div>

        <div class="toggle-group">
            <label>Time Limit</label>
            <select id="timingMode" onchange="changeMode()">
                <option value="timed" <?= $timing === 'timed' ? 'selected' : '' ?>>Timed Challenge</option>
                <option value="untimed" <?= $timing === 'untimed' ? 'selected' : '' ?>>Practice (No Timer)</option>
            </select>
        </div>
    </div>

    <?php if ($subjects_result->num_rows > 0): ?>
        <?php while ($subject = $subjects_result->fetch_assoc()): ?>
            <div class="subject-card">
                <div class="subject-header">
                    <h2><?= htmlspecialchars($subject['subject_name']) ?></h2>
                </div>
                
                <div class="topic-list">
                    <?php
                    $topics_sql = "SELECT t.id, t.topic_name, COUNT(q.id) as question_count
                                   FROM topics t
                                   LEFT JOIN questions q ON t.id = q.topic_id
                                   WHERE t.subject_id = ?
                                   GROUP BY t.id";
                    $topics_stmt = $conn->prepare($topics_sql);
                    $topics_stmt->bind_param("i", $subject['id']);
                    $topics_stmt->execute();
                    $topics_result = $topics_stmt->get_result();

                    if ($topics_result->num_rows > 0):
                        while ($topic = $topics_result->fetch_assoc()): ?>
                            <div class="topic-item">
                                <a class="topic-button" 
                                   href="questions.php?topic_id=<?= $topic['id'] ?>&mode=<?= $mode ?>&timing=<?= $timing ?>">
                                   <span><?= htmlspecialchars($topic['topic_name']) ?></span>
                                   <span class="q-count"><?= $topic['question_count'] ?> Qs</span>
                                </a>
                            </div>
                        <?php endwhile; 
                    else: ?>
                        <p style="padding: 10px 20px; color: #666;">No topics found for this subject.</p>
                    <?php endif; 
                    $topics_stmt->close(); ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px;">
            <p style="color:#888;">No subjects found for this selection.</p>
        </div>
    <?php endif; ?>

    <center>
        <a href="dashboard.php" class="back-button">← Back to Selection</a>
    </center>
</div>

<script>
    function changeMode() {
        const selectedMode = document.getElementById('feedbackMode').value;
        const selectedTiming = document.getElementById('timingMode').value;

        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('mode', selectedMode);
        urlParams.set('timing', selectedTiming);

        window.location.href = window.location.pathname + '?' + urlParams.toString();
    }
</script>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>