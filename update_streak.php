<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

require 'connection.php';

$user_id = $_SESSION['user_id'];

// Fetch last test date and streak
$user_sql = "SELECT last_test_date, streak FROM users WHERE id='$user_id'";
$user_result = $conn->query($user_sql);
$user_data = $user_result->fetch_assoc();

$current_date = date('Y-m-d');
$last_test_date = $user_data['last_test_date'];
$streak = $user_data['streak'];

// Update streak based on last test date
if ($last_test_date === $current_date) {
    // Do nothing if the test is taken on the same day
} elseif (date('Y-m-d', strtotime($last_test_date . ' +1 day')) === $current_date) {
    // Increase streak if the test is taken on the next day
    $streak++;
} else {
    // Reset streak if a day is missed
    $streak = 0;
}

// Update user's streak and last test date
$update_sql = "UPDATE users SET streak='$streak', last_test_date='$current_date' WHERE id='$user_id'";
if ($conn->query($update_sql) === TRUE) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update streak']);
}
?>
