<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'connection.php';

if (!isset($_GET['subject_id'])) {
    echo "Subject ID not provided.";
    exit;
}

$subject_id = $conn->real_escape_string($_GET['subject_id']);

// Fetch topics for the given subject
$sql = "SELECT * FROM topics WHERE subject_id='$subject_id'";
$result = $conn->query($sql);

$subject_sql = "SELECT name FROM subjects WHERE id='$subject_id'";
$subject_result = $conn->query($subject_sql);
$subject = $subject_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topics - StudyVerse</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
        }
        h1 {
            margin-top: 0;
            font-size: 24px;
            text-align: center;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin: 10px 0;
        }
        a {
            text-decoration: none;
            color: #007bff;
            display: block;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
        }
        a:hover {
            background-color: #f8f8f8;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Topics for <?php echo htmlspecialchars($subject['name']); ?></h1>
        <ul>
            <?php
            while($row = $result->fetch_assoc()) {
                echo '<li><a href="questions.php?topic_id='.$row['id'].'">'.htmlspecialchars($row['name']).'</a></li>';
            }
            ?>
        </ul>
    </div>
</body>
</html>
