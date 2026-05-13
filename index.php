<?php
require 'connection.php';

// Fetch only APPROVED testimonies
$testimony_query = "SELECT testimony.message, testimony.show_name, users.name 
                   FROM testimony 
                   JOIN users ON testimony.user_id = users.id 
                   WHERE testimony.status = 'approved' 
                   ORDER BY testimony.created_at DESC 
                   LIMIT 6";
$testimony_result = $conn->query($testimony_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to StudyVerse | Medical Board Preparation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #00ffcc;
            --secondary: #008cba;
            --glass: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #0a0a0a;
            color: white;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animate {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #0a0a0a, #1a1a1a);
            z-index: -1;
        }

        /* Medical Grid Overlay */
        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 204, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 204, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: var(--primary);
            filter: blur(80px);
            opacity: 0.15;
            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from { transform: translate(0, 0); }
            to { transform: translate(100px, 100px); }
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .badge {
            background: rgba(0, 255, 204, 0.1);
            color: var(--primary);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 0.8rem;
            border: 1px solid var(--primary);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
        }

        h1 {
            font-size: 4rem;
            margin: 0 0 10px 0;
            background: linear-gradient(90deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .tagline {
            font-size: 1.3rem;
            color: #ccc;
            max-width: 700px;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
        }

        .btn {
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-primary {
            background: var(--primary);
            color: #000;
            box-shadow: 0 0 20px rgba(0, 255, 204, 0.4);
        }

        .btn-secondary {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
        }

        .btn:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 255, 204, 0.3);
        }

        /* Features Section */
        .features {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            padding: 60px 20px 100px 20px;
            max-width: 1200px;
            margin: auto;
        }

        .feature-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 24px;
            width: 320px;
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            transition: 0.3s;
        }

        .feature-card:hover {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: white;
        }

        .feature-card p {
            color: #aaa;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Streak Teaser */
        .streak-teaser {
            text-align: center;
            padding: 100px 20px;
            background: linear-gradient(180deg, transparent, rgba(0, 255, 204, 0.05), transparent);
        }

        .fire-icon {
            font-size: 5rem;
            color: #ff9900;
            filter: drop-shadow(0 0 20px #ff9900);
            margin-bottom: 20px;
            animation: flicker 2s infinite alternate;
        }

        @keyframes flicker {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.1); opacity: 1; }
        }

        /* Testimonials Section */
        .testimonials {
            padding: 100px 20px;
            max-width: 1200px;
            margin: auto;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 50px;
        }

        .testimony-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 20px;
            position: relative;
        }

        .testimony-card i.quote {
            color: var(--primary);
            font-size: 1.5rem;
            opacity: 0.3;
            margin-bottom: 15px;
            display: block;
        }

        .testimony-text {
            font-style: italic;
            color: #ddd;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .testimony-user {
            color: var(--primary);
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="bg-animate">
        <div class="circle" style="width: 400px; height: 400px; top: -100px; left: -100px;"></div>
        <div class="circle" style="width: 300px; height: 300px; bottom: -50px; right: -50px; background: var(--secondary);"></div>
    </div>
    <div class="bg-grid"></div>

    <section class="hero">
        <img src="logo.jpg" alt="StudyVerse Logo" style="width: 100px; border-radius: 50%; margin-bottom: 15px; border: 3px solid var(--primary); box-shadow: 0 0 20px var(--primary);">
        <div class="badge">Board Exam Readiness Portal</div>
        <h1>StudyVerse</h1>
        <p class="tagline">The ultimate practice ecosystem for medical students. Master 100L - 600L high-yield past questions with active recall and exam-simulated timing.</p>
        
        <div class="cta-buttons">
            <a href="register.php" class="btn btn-primary">Start Practicing</a>
            <a href="login.php" class="btn btn-secondary">Student Login</a>
        </div>
    </section>

    <section class="streak-teaser">
        <i class="fa-solid fa-fire fire-icon"></i>
        <h2>Clinical Consistency Challenge</h2>
        <p style="max-width: 700px; margin: auto; font-size: 1.2rem; color: #bbb; line-height: 1.8;">
            Medicine is a marathon. To ensure long-term retention, you must engage. Answer at least one quiz every 24 hours to protect your <b>Study Streak</b>. Don't let your clinical fire burn out.
        </p>
    </section>

    <section class="features">
        <div class="feature-card">
            <i class="fa-solid fa-stethoscope"></i>
            <h3>Medical Curriculum</h3>
            <p>From Pre-clinical Anatomy to Clinical Surgery, our question bank is tailored to the rigorous medical school journey.</p>
        </div>

        <div class="feature-card">
            <i class="fa-solid fa-brain"></i>
            <h3>Active Recall Mode</h3>
            <p>Use 'Instant Mode' to verify facts immediately or 'End Mode' to simulate the pressure of professional board exams.</p>
        </div>

        <div class="feature-card">
            <i class="fa-solid fa-clock"></i>
            <h3>Timed Performance</h3>
            <p>Train your brain to retrieve information rapidly. Master the 60-second-per-question pace required for medical excellence.</p>
        </div>
    </section>

    <?php if ($testimony_result && $testimony_result->num_rows > 0): ?>
    <section class="testimonials">
        <div style="text-align: center;">
            <div class="badge">Success Stories</div>
            <h2 style="font-size: 2.5rem;">From Your Peers</h2>
        </div>
        <div class="testimonial-grid">
            <?php while ($row = $testimony_result->fetch_assoc()): ?>
                <div class="testimony-card">
                    <i class="fa-solid fa-quote-left quote"></i>
                    <p class="testimony-text">"<?php echo htmlspecialchars($row['message']); ?>"</p>
                    <div class="testimony-user">
                        — <?php echo ($row['show_name'] == 1) ? htmlspecialchars($row['name']) : "Anonymous Student"; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <footer style="text-align: center; padding: 60px; color: #444; border-top: 1px solid #222; font-size: 0.9rem;">
        <p>&copy; 2024 StudyVerse Medical. Built for future doctors and specialists.</p>
    </footer>

</body>
</html>