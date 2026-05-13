<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require 'connection.php';

if (isset($_POST['add_question'])) {
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $topic_id = $conn->real_escape_string($_POST['topic_id']);
    $question_text = $conn->real_escape_string($_POST['question_text']);
    $option_a = $conn->real_escape_string($_POST['option_a']);
    $option_b = $conn->real_escape_string($_POST['option_b']);
    $option_c = $conn->real_escape_string($_POST['option_c']);
    $option_d = $conn->real_escape_string($_POST['option_d']);
    $correct_option = strtoupper(trim($conn->real_escape_string($_POST['correct_option'])));
    $time_limit = intval($_POST['time_limit']);

    // Sanity check: correct_option must be A/B/C/D
    if (!in_array($correct_option, ['A', 'B', 'C', 'D'])) {
        echo "<script>alert('Correct option must be A, B, C, or D');</script>";
    } else {
        $sql = "INSERT INTO questions (subject_id, topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, time_limit) 
                VALUES ('$subject_id', '$topic_id', '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option', '$time_limit')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Question added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

// Fetch distinct levels
$level_sql = "SELECT DISTINCT level FROM subjects";
$level_result = $conn->query($level_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question - StudyVerse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="add_subject.php">Add Subject</a></li>
            <li class="nav-item"><a class="nav-link" href="add_topics.php">Add Topic</a></li>
            <li class="nav-item"><a class="nav-link" href="add_question.php">Add Question</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_admins.php">Manage Admins</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_subjects.php">Manage Subjects</a></li>
            <li class="nav-item"><a class="nav-link" href="manage_questions.php">Manage Questions</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="admin-container">
    <h1>Add Question</h1>
    <form method="post" action="">
        <label for="level">Select Level:</label>
        <select name="level" id="level_select" onchange="loadSemesters(this.value)" required>
            <option value="">--Select Level--</option>
            <?php while ($level_row = $level_result->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($level_row['level']); ?>">
                    <?php echo htmlspecialchars($level_row['level']); ?>
                </option>
            <?php } ?>
        </select>

        <label for="semester">Select Semester:</label>
        <select name="semester" id="semester_select" onchange="loadSubjects()" required>
            <option value="">--Select Semester--</option>
            <option value="1">1st Semester</option>
            <option value="2">2nd Semester</option>
        </select>

        <label for="subject_id">Select Subject:</label>
        <select name="subject_id" id="subject_select" onchange="loadTopics()" required>
            <option value="">--Select Subject--</option>
        </select>

        <label for="topic_id">Select Topic:</label>
        <select name="topic_id" id="topic_select" required>
            <option value="">--Select Topic--</option>
        </select>

        <label for="question_text">Question Text:</label>
        <textarea name="question_text" placeholder="Enter question" required></textarea>

        <input type="text" name="option_a" placeholder="Option A" required>
        <input type="text" name="option_b" placeholder="Option B" required>
        <input type="text" name="option_c" placeholder="Option C" required>
        <input type="text" name="option_d" placeholder="Option D" required>
        <input type="text" name="correct_option" placeholder="Correct Option (A/B/C/D)" required>
        <input type="number" name="time_limit" placeholder="Time Limit (seconds)" required>

        <button type="submit" name="add_question">Add Question</button>
    </form>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</div>

<script>
    function loadSubjects() {
        let level = document.getElementById('level_select').value;
        let semester = document.getElementById('semester_select').value;

        if (!level || !semester) {
            document.getElementById('subject_select').innerHTML = '<option value="">--Select Subject--</option>';
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_subjects.php?level=' + encodeURIComponent(level) + '&semester=' + encodeURIComponent(semester), true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('subject_select').innerHTML = xhr.responseText;
                document.getElementById('topic_select').innerHTML = '<option value="">--Select Topic--</option>';
            }
        };
        xhr.send();
    }

    function loadTopics() {
        let subjectId = document.getElementById('subject_select').value;

        if (!subjectId) {
            document.getElementById('topic_select').innerHTML = '<option value="">--Select Topic--</option>';
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_data.php?fetch_topics=1&subject_id=' + encodeURIComponent(subjectId), true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('topic_select').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
</script>
</body>
</html>
