<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'connection.php';

// Validate and get level & semester from the URL
if (!isset($_GET['level']) || !isset($_GET['semester'])) {
    die("Invalid request. Level and semester required.");
}

$level = $_GET['level'];
$semester = $_GET['semester'];

// Use prepared statement for security
$sql = "SELECT id, subject_name FROM subjects WHERE level = ? AND semester = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $level, $semester);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - StudyVerse</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }
        .subject {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .subject h2 {
            font-size: 20px;
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .topic-list {
            list-style: none;
            padding: 0;
        }
        .topic-list li {
            margin: 5px 0;
        }
        .topic-button {
            display: block;
            width: 100%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
            background-color: #f9f9f9;
        }
        .topic-button:hover {
            background-color: #f0f0f0;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            h1 {
                font-size: 20px;
            }
            .subject h2 {
                font-size: 18px;
            }
            .topic-button {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Subjects for <?php echo htmlspecialchars($level); ?> Level - Semester <?php echo htmlspecialchars($semester); ?></h1>
        <?php
        if ($result->num_rows > 0) {
            while ($subject = $result->fetch_assoc()) {
                $subject_id = $subject['id'];
                echo '<div class="subject">';
                echo '<h2>' . htmlspecialchars($subject['subject_name']) . '</h2>';

                // Fetch topics for this subject
                $topics_sql = "SELECT id, topic_name FROM topics WHERE subject_id = ?";
                $topics_stmt = $conn->prepare($topics_sql);
                $topics_stmt->bind_param("i", $subject_id);
                $topics_stmt->execute();
                $topics_result = $topics_stmt->get_result();

                if ($topics_result->num_rows > 0) {
                    echo '<ul class="topic-list">';
                    while ($topic = $topics_result->fetch_assoc()) {
                        echo '<li><a class="topic-button" href="questions.php?topic_id=' . $topic['id'] . '">' . htmlspecialchars($topic['topic_name']) . '</a></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No topics available for this subject.</p>';
                }

                $topics_stmt->close();
                echo '</div>';
            }
        } else {
            echo "<p>No subjects available for this semester.</p>";
        }
        ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
