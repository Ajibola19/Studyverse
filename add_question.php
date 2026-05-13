<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require 'connection.php';

// Single Question Upload Handler
if (isset($_POST['add_question'])) {
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $topic_id = $conn->real_escape_string($_POST['topic_id']);
    $question_text = $conn->real_escape_string($_POST['question_text']);
    $option_a = $conn->real_escape_string($_POST['option_a']);
    $option_b = $conn->real_escape_string($_POST['option_b']);
    $option_c = $conn->real_escape_string($_POST['option_c']);
    $option_d = $conn->real_escape_string($_POST['option_d']);
    $option_e = $conn->real_escape_string($_POST['option_e']); 
    $correct_option = strtoupper(trim($conn->real_escape_string($_POST['correct_option'])));
    $time_limit = intval($_POST['time_limit']);

    if (!in_array($correct_option, ['A', 'B', 'C', 'D', 'E'])) {
        echo "<script>alert('Correct option must be A, B, C, D, or E');</script>";
    } else {
        $sql = "INSERT INTO questions (subject_id, topic_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option, time_limit) 
                VALUES ('$subject_id', '$topic_id', '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$option_e', '$correct_option', '$time_limit')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Question added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question - StudyVerse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
    <style>
        .batch-layout { display: flex; gap: 30px; margin-top: 20px; }
        .batch-sidebar { flex: 0 0 350px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); height: fit-content; }
        .batch-preview { flex: 1; }
        .a4-paper { 
            background: white; width: 100%; min-height: 297mm; padding: 20mm; 
            box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 4px;
            font-family: "Georgia", serif; font-size: 11pt; color: #333; line-height: 1.6;
        }
        .q-block { position: relative; border-bottom: 1px dashed #ddd; padding: 20px 0; }
        .q-block:hover { background-color: #fafbfc; border-radius: 4px; }
        .q-actions { position: absolute; right: 10px; top: 10px; opacity: 0; transition: opacity 0.2s; z-index: 10; }
        .q-block:hover .q-actions { opacity: 1; }
        [contenteditable="true"]:focus { outline: 2px solid #007bff; background: #f0f7ff; border-radius: 3px; }
        #progress_badge { 
            position: fixed; bottom: 20px; right: 20px; background: #343a40; color: white; 
            padding: 15px 25px; border-radius: 50px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
            z-index: 99999; display: none; align-items: center; font-size: 10pt;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Admin Dashboard</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link active" href="add_question.php">Add Question</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div id="progress_badge">
    <div class="spinner-border text-success mr-3" role="status"></div>
    <span>Processing: <span id="cur">0</span> / <span id="tot">0</span> Questions...</span>
</div>

<div class="container-fluid px-5 py-4">
    <ul class="nav nav-pills mb-4 justify-content-center" id="uploadTabs">
        <li class="nav-item"><a class="nav-link active font-weight-bold" data-toggle="tab" href="#single_view">Single Upload</a></li>
        <li class="nav-item"><a class="nav-link font-weight-bold" data-toggle="tab" href="#batch_view">⚡ Instant Bulk Parser</a></li>
    </ul>

    <div class="tab-content">
        <!-- SINGLE VIEW -->
        <div class="tab-pane fade show active" id="single_view">
            <div class="admin-container mx-auto" style="max-width: 600px;">
                <h1>Add Question</h1>
                <form method="post">
                    <label>Level & Semester:</label>
                    <div class="d-flex gap-2">
                        <select name="level" id="level_select" onchange="loadSubjects()" class="form-control mr-2" required>
                            <option value="">Level</option>
                            <?php for($i=100; $i<=600; $i+=100) echo "<option value='$i'>$i Level</option>"; ?>
                        </select>
                        <select name="semester" id="semester_select" onchange="loadSubjects()" class="form-control" required>
                            <option value="">Semester</option>
                            <option value="1">1st</option>
                            <option value="2">2nd</option>
                        </select>
                    </div>
                    <label class="mt-2">Subject & Topic:</label>
                    <select name="subject_id" id="subject_select" onchange="loadTopics()" class="form-control mb-2" required><option value="">--Subject--</option></select>
                    <select name="topic_id" id="topic_select" class="form-control mb-2" required><option value="">--Topic--</option></select>
                    
                    <textarea name="question_text" class="form-control mb-2" placeholder="Enter question" required></textarea>
                    <input type="text" name="option_a" class="form-control mb-1" placeholder="Option A" required>
                    <input type="text" name="option_b" class="form-control mb-1" placeholder="Option B" required>
                    <input type="text" name="option_c" class="form-control mb-1" placeholder="Option C" required>
                    <input type="text" name="option_d" class="form-control mb-1" placeholder="Option D" required>
                    <input type="text" name="option_e" class="form-control mb-1" placeholder="Option E (Optional)">
                    <input type="text" name="correct_option" class="form-control mb-2" placeholder="Correct (A-E)" required>
                    <input type="number" name="time_limit" class="form-control mb-3" value="60" title="Seconds">
                    <button type="submit" name="add_question" class="btn btn-primary btn-block">Add Question</button>
                </form>
            </div>
        </div>

        <!-- BATCH VIEW -->
        <div class="tab-pane fade" id="batch_view">
            <div class="batch-layout">
                <div class="batch-sidebar">
                    <h5 class="text-primary">Configuration</h5>
                    <select id="b_level_select" class="form-control mb-2" onchange="loadBatchSubjects()">
                        <option value="">--Level--</option>
                        <?php for($i=100; $i<=600; $i+=100) echo "<option value='$i'>$i Level</option>"; ?>
                    </select>
                    <select id="b_semester_select" class="form-control mb-2" onchange="loadBatchSubjects()">
                        <option value="">--Semester--</option>
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                    </select>
                    <select id="b_subject_select" class="form-control mb-2" onchange="loadBatchTopics()"><option value="">--Subject--</option></select>
                    <select id="b_topic_select" class="form-control mb-3"><option value="">--Topic--</option></select>
                    <input type="number" id="b_time_limit" class="form-control mb-3" value="60" placeholder="Default Time (sec)">
                    <hr>
                    <textarea id="bulk_input" class="form-control" rows="11" placeholder="Paste questions here..."></textarea>
                    <button onclick="startBatchParse()" class="btn btn-primary btn-block mt-3">🚀 Parse & Preview</button>
                </div>

                <div class="batch-preview">
                    <div class="a4-paper">
                        <h2 class="text-center font-weight-bold mb-5">EXAM PREVIEW CANVAS</h2>
                        <div id="q_container"><p class="text-muted text-center py-5">Canvas empty.</p></div>
                        <div id="save_canvas_div" style="display:none;" class="text-center mt-5">
                            <button onclick="commitAllToDB()" class="btn btn-success btn-lg px-5">Confirm & Save All</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Dropdown Loaders
function loadSubjects() {
    let lvl = document.getElementById('level_select').value;
    let sem = document.getElementById('semester_select').value;
    if(!lvl || !sem) return;
    fetch(`get_subjects.php?level=${lvl}&semester=${sem}`).then(r => r.text()).then(h => document.getElementById('subject_select').innerHTML = h);
}
function loadTopics() {
    let sid = document.getElementById('subject_select').value;
    fetch(`get_data.php?fetch_topics=1&subject_id=${sid}`).then(r => r.text()).then(h => document.getElementById('topic_select').innerHTML = h);
}
function loadBatchSubjects() {
    let lvl = document.getElementById('b_level_select').value;
    let sem = document.getElementById('b_semester_select').value;
    if(!lvl || !sem) return;
    fetch(`get_subjects.php?level=${lvl}&semester=${sem}`).then(r => r.text()).then(h => document.getElementById('b_subject_select').innerHTML = h);
}
function loadBatchTopics() {
    let sid = document.getElementById('b_subject_select').value;
    fetch(`get_data.php?fetch_topics=1&subject_id=${sid}`).then(r => r.text()).then(h => document.getElementById('b_topic_select').innerHTML = h);
}

// Batch Logic
function startBatchParse() {
    const text = document.getElementById('bulk_input').value.trim();
    if (!text) return alert("Paste text first!");
    const questions = text.split(/\n\s*(?=\d+\.)/).filter(q => q.trim().length > 10);
    
    document.getElementById('tot').innerText = questions.length;
    document.getElementById('progress_badge').style.display = 'flex';
    document.getElementById('q_container').innerHTML = "";
    document.getElementById('save_canvas_div').style.display = 'block';
    runParser(questions, 0);
}

async function runParser(list, idx) {
    if (idx >= list.length) {
        document.getElementById('progress_badge').style.display = 'none';
        return;
    }
    document.getElementById('cur').innerText = idx + 1;
    let fd = new FormData();
    fd.append('raw_question', list[idx]);
    try {
        let res = await fetch('process_single_ai.php', { method: 'POST', body: fd });
        let data = await res.json();
        renderToA4(data, idx);
    } catch (e) { console.error(e); }
    runParser(list, idx + 1);
}

function renderToA4(data, id) {
    if (id === 0) document.getElementById('q_container').innerHTML = "";
    const defaultTime = document.getElementById('b_time_limit').value || 60;

    const html = `
        <div class="q-block" id="q_${id}">
            <div class="q-actions">
                <button class="btn btn-sm btn-outline-danger" onclick="this.parentElement.parentElement.remove()">🗑️ Delete</button>
            </div>
            <p><strong>${id+1}. </strong><span contenteditable="true" class="txt q-text">${data.question}</span></p>
            <div class="pl-4">
                A. <span contenteditable="true" class="opt opt-a">${data.options[0] || ''}</span><br>
                B. <span contenteditable="true" class="opt opt-b">${data.options[1] || ''}</span><br>
                C. <span contenteditable="true" class="opt opt-c">${data.options[2] || ''}</span><br>
                D. <span contenteditable="true" class="opt opt-d">${data.options[3] || ''}</span><br>
                E. <span contenteditable="true" class="opt opt-e">${data.options[4] || ''}</span>
            </div>
            <div class="d-flex align-items-center mt-3 small text-muted">
                <div class="mr-4">
                    Correct Answer: 
                    <select class="select-correct custom-select-sm border">
                        <option value="A" ${data.correct_index === 0 ? 'selected' : ''}>A</option>
                        <option value="B" ${data.correct_index === 1 ? 'selected' : ''}>B</option>
                        <option value="C" ${data.correct_index === 2 ? 'selected' : ''}>C</option>
                        <option value="D" ${data.correct_index === 3 ? 'selected' : ''}>D</option>
                        <option value="E" ${data.correct_index === 4 ? 'selected' : ''}>E</option>
                    </select>
                </div>
                <div>
                    Time (sec): 
                    <input type="number" class="q-time border rounded px-1" style="width:60px;" value="${defaultTime}">
                </div>
            </div>
        </div>`;
    document.getElementById('q_container').insertAdjacentHTML('beforeend', html);
}

function commitAllToDB() {
    const sid = document.getElementById('b_subject_select').value;
    const tid = document.getElementById('b_topic_select').value;
    if (!sid || !tid) return alert("Select Subject/Topic!");

    let questions = [];
    document.querySelectorAll('.q-block').forEach(b => {
        const opts = b.querySelectorAll('.opt');
        questions.push({
            question_text: b.querySelector('.q-text').innerText,
            option_a: opts[0].innerText, 
            option_b: opts[1].innerText,
            option_c: opts[2].innerText, 
            option_d: opts[3].innerText,
            option_e: opts[4] ? opts[4].innerText : '',
            correct_option: b.querySelector('.select-correct').value,
            individual_time: b.querySelector('.q-time').value
        });
    });

    fetch('save_batch.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            subject_id: sid, 
            topic_id: tid,
            questions: questions
        })
    }).then(r => r.json()).then(res => {
        if(res.success) { 
            alert("Success: " + res.count + " questions saved!"); 
            window.location.reload(); 
        } else { 
            alert("Error: " + res.error); 
        }
    });
}
</script>
</body>
</html>