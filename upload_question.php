<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $level = $_POST['level'];
    $semester = $_POST['semester'];
    $file = $_FILES['file'];

    // File upload handling
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($file["size"] > 500000) { // 500 KB max size
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($fileType != "pdf" && $fileType != "doc" && $fileType != "docx") {
        echo "Sorry, only PDF, DOC, and DOCX files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $sql = "INSERT INTO questions (title, description, file_path, level, semester) VALUES ('$title', '$description', '$target_file', '$level', '$semester')";
            if ($conn->query($sql) === TRUE) {
                echo "The file ". htmlspecialchars(basename($file["name"])). " has been uploaded.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Question</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        label, input, textarea, select {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="list_questions.php">Past Questions</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="about.php">About</a></li>
        </ul>
    </nav>
    <h2>Upload Question</h2>
    <form action="upload_question.php" method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <label for="level">Level:</label>
        <select id="level" name="level" required>
            <option value="">--Select Level--</option>
            <option value="100">100 Level</option>
            <option value="200">200 Level</option>
            <option value="300">300 Level</option>
            <option value="400">400 Level</option>
            <option value="500">500 Level</option>
            <option value="600">600 Level</option>
        </select>
        <label for="semester">Semester:</label>
        <select id="semester" name="semester" required>
            <option value="">--Select Semester--</option>
            <option value="1">1st Semester</option>
            <option value="2">2nd Semester</option>
        </select>
        <label for="file">Upload File:</label>
        <input type="file" id="file" name="file" required>
        <button type="submit">Upload Question</button>
    </form>
</body>
</html>
