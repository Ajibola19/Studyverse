<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'connection.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['answers']) || !isset($data['topic_id'])) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$userAnswers = $data['answers'];
$topic_id = $conn->real_escape_string($data['topic_id']);

// Fetch actual questions for grading
$sql = "SELECT * FROM questions WHERE topic_id='$topic_id'";
$result = $conn->query($sql);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[$row['id']] = $row; // Store by question ID
}

$score = 0;
$details = [];
foreach ($userAnswers as $question_id => $user_answer) {
    if (isset($questions[$question_id])) {
        $question = $questions[$question_id];
        $correct_option = strtoupper(trim($question['correct_option']));
        $user_answer = strtoupper(trim($user_answer));

        if ($user_answer === $correct_option) {
            $score++;
            $details[] = [
                'question' => $question['question_text'],
                'your_answer' => $user_answer,
                'correct_answer' => $correct_option,
                'correct' => true
            ];
        } else {
            $details[] = [
                'question' => $question['question_text'],
                'your_answer' => $user_answer ?: 'No answer',
                'correct_answer' => $correct_option,
                'correct' => false
            ];
        }
    }
}

$subject_id = !empty($questions) ? reset($questions)['subject_id'] : 0;
$total_questions = count($questions);
$details_json = $conn->real_escape_string(json_encode($details));

$sql = "INSERT INTO quiz_results (user_id, subject_id, topic_id, score, total_questions, details) 
        VALUES ('$user_id', '$subject_id', '$topic_id', '$score', '$total_questions', '$details_json')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['score' => $score, 'details' => $details]);
} else {
    echo json_encode(['error' => 'Error saving result: ' . $conn->error]);
}
?>
