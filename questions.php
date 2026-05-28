<?php
session_start();

if (!isset($_GET['topic_id'], $_GET['mode'], $_GET['timing'])) {
    exit("Invalid access. Required parameters missing.");
}

$topic_id = (int)$_GET['topic_id'];
$mode = $_GET['mode']; 
$timing = $_GET['timing']; 

require 'connection.php';

// Logic Update: Questions are now fetched in a random order
$stmt = $conn->prepare("SELECT * FROM questions WHERE topic_id = ? ORDER BY RAND()");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);

if (empty($questions)) {
    exit("No questions found for this topic.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz | StudyVerse</title>
    <style>
        :root {
            --primary: #00ffcc;
            --wrong: #ff4d4d;
            --correct: #2ecc71;
            --glass: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top, #1a1a1a 0%, #0a0a0a 100%);
            color: white;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Progress Header */
        .quiz-header {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            box-sizing: border-box;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .progress-container {
            background: var(--glass);
            height: 12px;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #00ffcc, #008cba);
            width: 0%;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 15px var(--primary);
        }

        /* Main Container */
        .question-container {
            width: 90%;
            max-width: 750px;
            margin: 20px auto;
            background: var(--glass);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            position: relative;
        }

        .timer-badge {
            position: absolute;
            top: -15px;
            right: 40px;
            background: #222;
            padding: 8px 20px;
            border-radius: 50px;
            border: 2px solid var(--primary);
            font-weight: bold;
            color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
        }

        .question-text {
            font-size: 1.5rem;
            line-height: 1.4;
            margin-bottom: 35px;
            font-weight: 500;
        }

        /* Options */
        #options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .option {
            background: rgba(255, 255, 255, 0.03);
            padding: 18px 25px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }

        .option:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(8px);
            border-color: var(--primary);
        }

        .option.selected {
            background: rgba(0, 255, 204, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.2);
        }

        .option.correct {
            background: rgba(46, 204, 113, 0.2) !important;
            border-color: var(--correct) !important;
            color: #afffbd;
        }

        .option.wrong {
            background: rgba(231, 76, 60, 0.2) !important;
            border-color: var(--wrong) !important;
            color: #ffbaba;
        }

        /* Buttons */
        .controls {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .next-btn {
            background: var(--primary);
            color: #000;
            padding: 14px 35px;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            display: none;
            box-shadow: 0 5px 15px rgba(0, 255, 204, 0.3);
        }

        .next-btn:hover {
            transform: scale(1.05);
            background: #00e6b8;
        }

        .submit-btn {
            background: transparent;
            color: #888;
            border: 1px solid #444;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .submit-btn:hover {
            color: #fff;
            border-color: #ff4d4d;
        }
    </style>
</head>
<body>

    <div class="quiz-header">
        <div style="display:flex; justify-content: space-between; margin-bottom: 8px; color: #888; font-size: 0.9rem;">
            <span id="questionNum">Question 1 of <?= count($questions) ?></span>
            <span id="progressText">0% Complete</span>
        </div>
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>
    </div>

    <div class="question-container">
        <div class="timer-badge" id="timer">Time: 00s</div>
        <div class="question-text" id="question">Loading your next challenge...</div>
        
        <div id="options"></div>

        <div class="controls">
            <form id="quizForm" method="POST" action="result.php">
                <input type="hidden" name="answers" id="answersInput">
                <input type="hidden" name="score" id="scoreInput">
                <input type="hidden" name="total" id="totalInput">
                <input type="hidden" name="details" id="detailsInput">
                <button type="button" class="submit-btn" id="submitBtn">Quit Quiz</button>
            </form>
            <button class="next-btn" id="nextBtn">Continue →</button>
        </div>
    </div>

<script>
    const questions = <?php echo json_encode($questions); ?>;
    const mode = '<?php echo $mode; ?>';
    const timing = '<?php echo $timing; ?>';

    let currentIndex = parseInt(sessionStorage.getItem('currentIndex') || '0');
    let answers = JSON.parse(sessionStorage.getItem('answers') || '[]');
    let timers = JSON.parse(sessionStorage.getItem('timers') || '{}');
    let timer, timeLeft, totalTime;

    function loadQuestion() {
        if (currentIndex >= questions.length) {
            finishQuiz();
            return;
        }

        const q = questions[currentIndex];
        totalTime = q.time_limit || 60;
        timeLeft = timing === 'timed' ? (timers[currentIndex] !== undefined ? timers[currentIndex] : totalTime) : 0;

        document.getElementById("question").innerText = q.question_text;
        document.getElementById("questionNum").innerText = `Question ${currentIndex + 1} of ${questions.length}`;
        document.getElementById("options").innerHTML = '';
        
        // Update global progress
        const overallProgress = (currentIndex / questions.length) * 100;
        document.getElementById("progressBar").style.width = `${overallProgress}%`;
        document.getElementById("progressText").innerText = `${Math.round(overallProgress)}% Complete`;

        ['A','B','C','D','E'].forEach(letter => {
            const optionText = q['option_' + letter.toLowerCase()];
            if (optionText && optionText.trim() !== "") {
                const div = document.createElement("div");
                div.className = "option";
                div.setAttribute("data-letter", letter);
                div.innerHTML = `<b style="color:var(--primary); margin-right:15px;">${letter}</b> ${optionText}`;
                
                if (mode === 'end' && answers[currentIndex] === letter) {
                    div.classList.add("selected");
                }
                
                div.onclick = () => handleAnswer(letter, div);
                document.getElementById("options").appendChild(div);
            }
        });

        if (timing === 'timed') {
            document.getElementById("timer").style.display = 'block';
            updateTimerDisplay();
            clearInterval(timer);
            timer = setInterval(() => {
                timeLeft--;
                timers[currentIndex] = timeLeft;
                sessionStorage.setItem('timers', JSON.stringify(timers));
                updateTimerDisplay();
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    if (mode === 'instant') {
                        autoReveal(q.correct_option);
                        document.getElementById("nextBtn").style.display = 'block';
                    } else {
                        saveAnswer(null);
                        nextQuestion();
                    }
                }
            }, 1000);
        } else {
            document.getElementById("timer").style.display = 'none';
        }

        document.getElementById("nextBtn").style.display = 'none';
    }

    function handleAnswer(selected, div) {
        clearInterval(timer);
        disableOptions();

        const correct = questions[currentIndex].correct_option;
        if (mode === 'instant') {
            if (selected === correct) {
                div.classList.add("correct");
            } else {
                div.classList.add("wrong");
                document.querySelectorAll(".option").forEach(opt => {
                    if(opt.getAttribute("data-letter") === correct) opt.classList.add("correct");
                });
            }
        } else {
            document.querySelectorAll(".option").forEach(opt => opt.classList.remove("selected"));
            div.classList.add("selected");
        }

        saveAnswer(selected);
        document.getElementById("nextBtn").style.display = 'block';
    }

    function saveAnswer(selected) {
        answers[currentIndex] = selected;
        sessionStorage.setItem('answers', JSON.stringify(answers));
    }

    function autoReveal(correct) {
        disableOptions();
        document.querySelectorAll(".option").forEach(opt => {
            if(opt.getAttribute("data-letter") === correct) opt.classList.add("correct");
        });
    }

    function disableOptions() {
        document.querySelectorAll(".option").forEach(opt => opt.onclick = null);
    }

    function updateTimerDisplay() {
        document.getElementById("timer").innerText = `Time: ${timeLeft}s`;
        if(timeLeft <= 10) {
            document.getElementById("timer").style.color = 'var(--wrong)';
            document.getElementById("timer").style.borderColor = 'var(--wrong)';
        } else {
            document.getElementById("timer").style.color = 'var(--primary)';
            document.getElementById("timer").style.borderColor = 'var(--primary)';
        }
    }

    function nextQuestion() {
        currentIndex++;
        sessionStorage.setItem('currentIndex', currentIndex);
        loadQuestion();
    }

    function finishQuiz() {
        const resultDetails = [];
        let score = 0;

        questions.forEach((q, i) => {
            const userLetter = answers[i];
            const correctLetter = q.correct_option;
            const isCorrect = userLetter === correctLetter;
            if (isCorrect) score++;

            const userText = userLetter ? (userLetter + ". " + q['option_' + userLetter.toLowerCase()]) : "No Answer";
            const correctText = correctLetter + ". " + q['option_' + correctLetter.toLowerCase()];

            resultDetails.push({
                question: q.question_text,
                your_answer: userText,
                correct_answer: correctText,
                correct: isCorrect
            });
        });

        document.getElementById("answersInput").value = JSON.stringify(answers);
        document.getElementById("scoreInput").value = score;
        document.getElementById("totalInput").value = questions.length;
        document.getElementById("detailsInput").value = JSON.stringify(resultDetails);

        sessionStorage.clear();
        document.getElementById("quizForm").submit();
    }

    document.getElementById("nextBtn").onclick = nextQuestion;

    // --- Updated Quit Quiz Logic with Streak Warning ---
    document.getElementById("submitBtn").onclick = () => {
        const warningMessage = "Wait! If you quit now, your progress will be lost. \n\n⚠️ IMPORTANT: If you don't finish a quiz today, your Study Streak will break! Are you sure you want to exit?";
        if(confirm(warningMessage)) {
            sessionStorage.clear();
            window.location.href = 'dashboard.php';
        }
    };

    loadQuestion();

    // 1. Push a fake state so there is "somewhere" to go back to that is still this page
    window.history.pushState(null, null, window.location.href);

    window.onpopstate = function () {
        // 2. When the back button is pressed, push the state again to stay here
        window.history.pushState(null, null, window.location.href);
        
        // Optional: Alert the user why they can't go back
        alert("Back navigation is disabled during the quiz/result to protect your progress or you can use the quit button to go back.");
    };
</script>
</body>
</html>
