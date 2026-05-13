<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About StudyVerse | Our Mission</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #00ffcc;
            --secondary: #008cba;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #0a0a0a;
            color: white;
            line-height: 1.6;
        }

        /* Reusing your background styles for consistency */
        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 204, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 204, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 80px 20px;
        }

        .about-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .badge {
            background: rgba(0, 255, 204, 0.1);
            color: var(--primary);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 0.8rem;
            border: 1px solid var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            display: inline-block;
            margin-bottom: 15px;
        }

        h1 {
            font-size: 3.5rem;
            background: linear-gradient(90deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 10px 0;
        }

        .mission-box {
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 30px;
            backdrop-filter: blur(10px);
            margin-bottom: 50px;
            text-align: center;
        }

        .mission-box p {
            font-size: 1.2rem;
            color: #ccc;
        }

        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 60px;
        }

        .stat-card {
            padding: 30px;
            background: rgba(255,255,255,0.03);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
        }

        .stat-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
        }

        .stat-card i {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            margin: 10px 0;
            color: white;
        }

        .stat-card p {
            color: #888;
            font-size: 0.95rem;
        }

        .cta-section {
            text-align: center;
            padding-top: 40px;
        }

        .btn-back {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
            border: 1px solid var(--primary);
            padding: 12px 30px;
            border-radius: 50px;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: var(--primary);
            color: black;
        }
    </style>
</head>
<body>

    <div class="bg-grid"></div>

    <div class="container">
        <header class="about-header">
            <div class="badge">The Story Behind The Verse</div>
            <h1>Empowering Future Doctors</h1>
        </header>

        <section class="mission-box">
            <h3>Our Mission</h3>
            <p>
                StudyVerse was born out of a simple observation: medical education is vast, but practice tools are often scattered. We built this platform to bridge the gap between classroom theory and board exam reality, providing a high-yield environment for students to test their clinical judgment under pressure.
            </p>
        </section>

        <div class="grid-stats">
            <div class="stat-card">
                <i class="fa-solid fa-microscope"></i>
                <h3>Evidence-Based</h3>
                <p>Every question in our database is curated from professional medical board standards and high-yield past questions.</p>
            </div>

            <div class="stat-card">
                <i class="fa-solid fa-bolt"></i>
                <h3>Rapid Retention</h3>
                <p>We leverage active recall and spaced repetition concepts to help you memorize complex anatomical and clinical facts faster.</p>
            </div>

            <div class="stat-card">
                <i class="fa-solid fa-users"></i>
                <h3>Student-Centric</h3>
                <p>Built by those who understand the 2:00 AM coffee-fueled study sessions. Designed for the 100L through 600L journey.</p>
            </div>
        </div>

        <section class="cta-section">
            <p style="color: #666; margin-bottom: 30px;">Ready to master your next professional exam?</p>
            <a href="dashboard.php" class="btn-back">Back</a>
        </section>

        <footer style="text-align: center; margin-top: 100px; color: #444; font-size: 0.8rem;">
            <p>&copy; 2024 StudyVerse Medical. Precision in Practice.</p>
        </footer>
    </div>

</body>
</html>