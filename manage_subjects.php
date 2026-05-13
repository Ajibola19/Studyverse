<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require 'connection.php';

// Handle delete request
$deleteMessage = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $conn->real_escape_string($_POST['delete_id']);
    $sql = "DELETE FROM subjects WHERE id='$delete_id'";
    if ($conn->query($sql)) {
        $deleteMessage = "<div class='alert alert-success'>Subject deleted successfully.</div>";
    } else {
        $deleteMessage = "<div class='alert alert-danger'>Error deleting subject: " . $conn->error . "</div>";
    }
}

// Fetch all subjects
$subjects_result = $conn->query("SELECT * FROM subjects");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - StudyVerse</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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

    <div class="container mt-4">
        <h1>Manage Subjects</h1>

        <?php echo $deleteMessage; ?> <!-- Display delete message -->

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Level</th>
                    <th>Semester</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $subjects_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['level']); ?></td>
                        <td><?php echo htmlspecialchars($row['semester']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td> <!-- FIXED LINE -->
                        <td>
                            <form method="post" action="manage_subjects.php" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
