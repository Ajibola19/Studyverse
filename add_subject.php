<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require 'connection.php'; // Ensure connection is included

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $level = $conn->real_escape_string($_POST['level']);
    $semester = $conn->real_escape_string($_POST['semester']);
    $subject_name = $conn->real_escape_string($_POST['subject_name']); // Corrected field name

    // Check if the subject already exists
    $check_sql = "SELECT id FROM subjects WHERE level = '$level' AND semester = '$semester' AND subject_name = '$subject_name'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Subject already exists!');</script>";
    } else {
        // Insert the new subject
        $sql = "INSERT INTO subjects (level, semester, subject_name) VALUES ('$level', '$semester', '$subject_name')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Subject added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - StudyVerse</title>
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
        <h1>Add Subject</h1>
        <form method="post" action="add_subject.php">
            <label for="level">Select Level:</label>
            <select id="level" name="level" required>
                <option value="">--Select Level--</option>
                <option value="100">100 Level</option>
                <option value="200">200 Level</option>
                <option value="300">300 Level</option>
                <option value="400">400 Level</option>
                <option value="500">500 Level</option>
                <option value="600">600 Level</option>
            </select>

            <label for="semester">Select Semester:</label>
            <select id="semester" name="semester" required>
                <option value="">--Select Semester--</option>
                <option value="1">1st Semester</option>
                <option value="2">2nd Semester</option>
            </select>

            <label for="subject_name">Subject Name:</label>
            <input type="text" name="subject_name" placeholder="Enter Subject Name" required>

            <button type="submit">Add Subject</button>
        </form>
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
