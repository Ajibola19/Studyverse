<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require 'connection.php';

// Add topic logic
if (isset($_POST['add_topic'])) {
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $topic_name = $conn->real_escape_string($_POST['topic_name']);

    $sql = "INSERT INTO topics (subject_id, topic_name) VALUES ('$subject_id', '$topic_name')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Topic added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding topic: " . $conn->error . "');</script>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Topic - StudyVerse</title>
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
        <h1>Add Topic</h1>
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
            <select name="subject_id" id="subject_select" required>
                <option value="">--Select Subject--</option>
            </select>

            <label for="topic_name">Topic Name:</label>
            <input type="text" name="topic_name" placeholder="Enter Topic Name" required>

            <button type="submit" name="add_topic">Add Topic</button>
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
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>
