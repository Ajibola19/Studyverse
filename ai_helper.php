<?php
/**
 * Study Verse AI Helper
 * This file bridges PHP with a Python script using g4f to avoid API Quota limits.
 */

function generateDistractors($question, $correct_answer) {
    // 1. Prepare the arguments for the command line.
    // escapeshellarg makes sure spaces or special characters in the question don't break the command.
    $escaped_q = escapeshellarg($question);
    $escaped_a = escapeshellarg($correct_answer);

    // 2. Define the command to run the Python script.
    // We add '2>&1' at the end to capture any Python errors in the output.
    $command = "python ai_bridge.py $escaped_q $escaped_a 2>&1";
    
    // 3. Execute the command and get the result
    $output = shell_exec($command);

    // 4. Basic Error Handling
    if (!$output) {
        return ["error" => "The AI system failed to respond. Please ensure Python is installed and 'pip install g4f' was successful."];
    }

    // 5. Attempt to decode the JSON coming back from Python
    $decoded = json_decode(trim($output), true);

    // If Python sent back a regular string instead of JSON, it's likely a Python error message
    if (!$decoded) {
        return ["error" => "AI Format Error: " . $output];
    }

    // Return the cleaned question and distractors to add_question.php
    return $decoded;
}
?>


