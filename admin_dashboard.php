<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require 'connection.php';

// --- Handle Announcement Update ---
if (isset($_POST['update_announcement'])) {
    $new_msg = $conn->real_escape_string($_POST['announcement_text']);
    
    $check_announcement = $conn->query("SELECT id FROM announcements WHERE id = 1");
    if ($check_announcement->num_rows == 0) {
        $conn->query("INSERT INTO announcements (id, message) VALUES (1, '$new_msg')");
    } else {
        $conn->query("UPDATE announcements SET message = '$new_msg' WHERE id = 1");
    }
    $message = "Announcement updated successfully!";
}

$ann_res = $conn->query("SELECT message FROM announcements WHERE id = 1");
$current_announcement = ($ann_res && $ann_res->num_rows > 0) ? $ann_res->fetch_assoc()['message'] : "";

// --- Fetch total number of registered users ---
$user_count_sql = "SELECT COUNT(id) AS total_users FROM users";
$user_count_result = $conn->query($user_count_sql);
$total_users = 0;
if ($user_count_result) {
    $user_data = $user_count_result->fetch_assoc();
    $total_users = $user_data['total_users'];
}

// --- NEW: Handle TESTIMONY Actions (Public Reviews) ---
if (isset($_GET['approve_testimony'])) {
    $t_id = $conn->real_escape_string($_GET['approve_testimony']);
    $conn->query("UPDATE testimony SET status = 'approved' WHERE id = $t_id");
    $message = "Testimony approved for public display.";
}

if (isset($_GET['reject_testimony'])) {
    $t_id = $conn->real_escape_string($_GET['reject_testimony']);
    $conn->query("UPDATE testimony SET status = 'rejected' WHERE id = $t_id");
    $message = "Testimony rejected.";
}

if (isset($_GET['delete_testimony'])) {
    $t_id = $conn->real_escape_string($_GET['delete_testimony']);
    $conn->query("DELETE FROM testimony WHERE id = $t_id");
    $message = "Testimony deleted.";
}

// --- NEW: Handle FEEDBACK Actions (Private Suggestions) ---
if (isset($_GET['delete_feedback'])) {
    $f_id = $conn->real_escape_string($_GET['delete_feedback']);
    $conn->query("DELETE FROM feedback WHERE id = $f_id");
    $message = "Feedback message deleted.";
}

// --- Fetch Data from separate tables ---
$testimonies_result = $conn->query("SELECT testimony.*, users.name AS user_name FROM testimony JOIN users ON testimony.user_id = users.id ORDER BY testimony.created_at DESC");
$feedback_result = $conn->query("SELECT feedback.*, users.name AS user_name FROM feedback JOIN users ON feedback.user_id = users.id ORDER BY feedback.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudyVerse</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .stat-card {
            background: #f8f9fa;
            border-left: 5px solid #00ffcc;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: inline-block;
            min-width: 250px;
            vertical-align: top;
        }
        .stat-card h3 { margin: 0; font-size: 1rem; color: #666; }
        .stat-card p { margin: 0; font-size: 2rem; font-weight: bold; color: #333; }

        .announcement-card {
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-rejected { background-color: #dc3545; color: #fff; }
        .section-title { border-bottom: 2px solid #00ffcc; display: inline-block; padding-bottom: 5px; margin-bottom: 20px; margin-top: 30px; }
    </style>
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

    <div class="container mt-4">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?>!</p>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <h3>Total Registered Users</h3>
                    <p><?php echo number_format($total_users); ?></p>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="announcement-card">
                    <h3>Update Announcement</h3>
                    <form method="POST">
                        <div class="form-group">
                            <textarea name="announcement_text" class="form-control" rows="2" placeholder="Enter scrolling text for users..." required><?php echo htmlspecialchars($current_announcement); ?></textarea>
                        </div>
                        <button type="submit" name="update_announcement" class="btn btn-primary btn-block">Update Scrolling Announcement</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2 class="section-title">Public Testimonials</h2>
        <div class="table-responsive mb-5">
            <table class="table table-bordered bg-white">
                <thead class="thead-dark">
                    <tr>
                        <th>User</th>
                        <th>Message</th>
                        <th>Privacy</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($testimonies_result && $testimonies_result->num_rows > 0): ?>
                        <?php while ($row = $testimonies_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['user_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo date("M j, Y", strtotime($row['created_at'])); ?></small>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                <td><?php echo ($row['show_name'] == 1) ? '<span class="badge badge-info">Show Name</span>' : '<span class="badge badge-secondary">Anonymous</span>'; ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <div class="btn-group">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a class="btn btn-sm btn-success" href="?approve_testimony=<?php echo $row['id']; ?>">Approve</a>
                                            <a class="btn btn-sm btn-warning" href="?reject_testimony=<?php echo $row['id']; ?>">Reject</a>
                                        <?php endif; ?>
                                        <a class="btn btn-sm btn-danger" href="?delete_testimony=<?php echo $row['id']; ?>" onclick="return confirm('Delete testimony?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No testimonials found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h2 class="section-title">Private Feedback & Suggestions</h2>
        <div class="table-responsive">
            <table class="table table-bordered bg-white">
                <thead class="thead-dark">
                    <tr>
                        <th>User Name</th>
                        <th>Suggestion Message</th>
                        <th>Submitted On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($feedback_result && $feedback_result->num_rows > 0): ?>
                        <?php while ($row = $feedback_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['user_name']); ?></strong></td>
                                <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                <td><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a class="btn btn-sm btn-danger" href="?delete_feedback=<?php echo $row['id']; ?>" onclick="return confirm('Delete feedback?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No private feedback found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>