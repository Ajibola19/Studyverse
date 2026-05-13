<?php
// save_batch.php
session_start();
header('Content-Type: application/json');

// 1. Security Check
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized admin session']);
    exit;
}

require 'connection.php';

// 2. Decode JSON Payload
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['questions'])) {
    echo json_encode(['success' => false, 'error' => 'No question payload found']);
    exit;
}

$subject_id = intval($input['subject_id']);
$topic_id   = intval($input['topic_id']);

// 3. Start Transaction
$conn->begin_transaction();

try {
    foreach ($input['questions'] as $q) {
        // Skip if question text is somehow empty
        if (empty(trim($q['question_text']))) continue;

        $question_text  = $conn->real_escape_string(trim($q['question_text']));
        $option_a       = $conn->real_escape_string(trim($q['option_a']));
        $option_b       = $conn->real_escape_string(trim($q['option_b']));
        $option_c       = $conn->real_escape_string(trim($q['option_c']));
        $option_d       = $conn->real_escape_string(trim($q['option_d']));
        $option_e       = $conn->real_escape_string(trim($q['option_e'] ?? '')); // Added Option E support
        $correct_option = strtoupper(trim($q['correct_option']));
        
        // Use the individual time limit sent for this specific question
        // Fallback to 60 if for some reason it is missing
        $q_time_limit   = isset($q['individual_time']) ? intval($q['individual_time']) : 60;

        $sql = "INSERT INTO questions (subject_id, topic_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option, time_limit) 
                VALUES ($subject_id, $topic_id, '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$option_e', '$correct_option', $q_time_limit)";
        
        if (!$conn->query($sql)) {
            throw new Exception("Database Error: " . $conn->error);
        }
    }
    
    // 4. Finalize
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // If anything goes wrong, undo everything in this batch
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>