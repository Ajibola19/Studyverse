<?php
include 'connection.php';

$level = isset($_POST['level']) ? $_POST['level'] : '';
$semester = isset($_POST['semester']) ? $_POST['semester'] : '';

$sql = "SELECT * FROM questions";
if ($level && $semester) {
    $sql .= " WHERE level = '$level' AND semester = '$semester'";
}
$sql .= " ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Questions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        label, select {
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['is_admin']): ?>
                    <li><a href="upload_question.php">Upload Question</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">
        <h2>Past Questions</h2>
        <form action="list_questions.php" method="POST">
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
            <button type="submit">Show Questions</button>
        </form>
        <?php if ($level && $semester): ?>
            <h3>Showing Questions for Level <?php echo $level; ?>, Semester <?php echo $semester; ?></h3>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Uploaded At</th>
                    <th>Download</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['title'] . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>" . $row['uploaded_at'] . "</td>";
                        echo "<td><a href='" . $row['file_path'] . "' download>Download</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No questions found</td></tr>";
                }
                ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
