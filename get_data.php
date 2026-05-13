<?php
require 'connection.php';

// Enable error reporting to catch issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['fetch_subjects'])) {
    $level = $conn->real_escape_string($_GET['level'] ?? '');
    $semester = $conn->real_escape_string($_GET['semester'] ?? '');

    if (empty($level) || empty($semester)) {
        echo "Error: Level or Semester is missing.";
        exit;
    }

    $sql = "SELECT id, name FROM subjects WHERE level='$level' AND semester='$semester'";
    $result = $conn->query($sql);

    if (!$result) {
        echo "SQL Error: " . $conn->error;
        exit;
    }

    if ($result->num_rows === 0) {
        echo "No subjects found.";
        exit;
    }

    $options = "<option value=''>--Select Subject--</option>";
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='{$row['id']}'>{$row['name']}</option>";
    }

    echo $options;
    exit;
}

if (isset($_GET['fetch_topics'])) {
    $subject_id = $conn->real_escape_string($_GET['subject_id'] ?? '');

    if (empty($subject_id)) {
        echo "Error: Subject ID is missing.";
        exit;
    }

    $sql = "SELECT id, topic_name FROM topics WHERE subject_id='$subject_id'";
    $result = $conn->query($sql);

    if (!$result) {
        echo "SQL Error: " . $conn->error;
        exit;
    }

    if ($result->num_rows === 0) {
        echo "No topics found.";
        exit;
    }

    $options = "<option value=''>--Select Topic--</option>";
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='{$row['id']}'>{$row['topic_name']}</option>";
    }

    echo $options;
    exit;
}

echo "Invalid request.";
exit;
