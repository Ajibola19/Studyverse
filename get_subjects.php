<?php
require 'connection.php'; // Ensure this file contains your database connection setup

// Check if 'level' and 'semester' parameters are passed
if (!isset($_GET['level']) || empty($_GET['level']) || !isset($_GET['semester']) || empty($_GET['semester'])) {
    echo '<option value="">Select Level and Semester</option>';
    exit;
}

$level = $conn->real_escape_string($_GET['level']);
$semester = $conn->real_escape_string($_GET['semester']);

// Query the database for subjects matching the selected level and semester
$subject_sql = "SELECT id, subject_name FROM subjects WHERE level = '$level' AND semester = '$semester'";
$subject_result = $conn->query($subject_sql);

if ($subject_result->num_rows > 0) {
    echo '<option value="">Select Subject</option>';
    while ($subject_row = $subject_result->fetch_assoc()) {
        echo '<option value="' . $subject_row['id'] . '">' . htmlspecialchars($subject_row['subject_name']) . '</option>';
    }
} else {
    echo '<option value="">No Subjects Found</option>';
}
?>
