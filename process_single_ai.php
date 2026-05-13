<?php
// process_single_ai.php
header('Content-Type: application/json');

if (isset($_POST['raw_question'])) {
    $text = trim($_POST['raw_question']);

    // 1. Extract Question Text
    preg_match('/^\d+\.(.*?)(?=[a-z]\))/s', $text, $q_match);
    $question = isset($q_match[1]) ? trim($q_match[1]) : "Unknown Question";

    // 2. Extract All Options (a through e)
    // This regex looks for the letter, the closing bracket, and the text following it
    preg_match_all('/([a-e])\)\s*(.*?)(?=\s*[a-e]\)|Answer:|$)/s', $text, $opt_matches);
    $raw_options = $opt_matches[2]; 
    $labels = $opt_matches[1];

    // 3. Extract the Correct Answer letter
    preg_match('/Answer:\s*([a-e])\)/i', $text, $ans_match);
    $correct_letter = isset($ans_match[1]) ? strtolower($ans_match[1]) : '';

    $final_options = [];
    $correct_index = 0; // Default to A (0) if not found

    // 4. Map all 5 options in their exact order
    // A=0, B=1, C=2, D=3, E=4
    $mapping = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4];
    
    foreach ($raw_options as $index => $opt_text) {
        $current_letter = strtolower($labels[$index]);
        $final_options[] = trim($opt_text);
        
        // If this option letter matches the Answer letter, set the index
        if ($current_letter === $correct_letter) {
            $correct_index = $mapping[$current_letter];
        }
    }

    echo json_encode([
        "question" => $question,
        "options" => $final_options,
        "correct_index" => $correct_index
    ]);
}
?>