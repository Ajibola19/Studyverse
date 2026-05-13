<?php
require 'connection.php';

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

$query = "SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option, time_limit 
          FROM questions 
          WHERE subject_id = ? AND topic_id = ?
          ORDER BY RAND()
          LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $subject_id, $topic_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];

while ($row = $result->fetch_assoc()) {
    $questions[] = [
        'id' => $row['id'],
        'question' => $row['question_text'],
        'options' => [
            'A' => $row['option_a'],
            'B' => $row['option_b'],
            'C' => $row['option_c'],
            'D' => $row['option_d'],
        ],
        'correct' => $row['correct_option'],
        'time_limit' => (int)$row['time_limit']
    ];
}

header('Content-Type: application/json');
echo json_encode($questions);
?>
