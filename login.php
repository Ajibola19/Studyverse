<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, is_admin, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid password.";
        }
    } else {
        $_SESSION['error'] = "No user found with that email.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studyverse | Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            /* DIMMING EFFECT APPLIED HERE */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('logo.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
        }

.container {
    background-color: rgba(255, 255, 255, 0.95);
    padding: 30px;
    border-radius: 12px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
    
    /* ADD THIS LINE */
    margin-top: 30px; /* Adjust this number to move it further up or down */
    margin-right:420px;
}

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .form-group input[type="submit"] {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }

        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }

        a {
            color: #007bff;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .terms {
            font-size: 13px;
            color: #555;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .terms a {
            color: red;
            font-weight: bold;
        }

        @media (max-width: 500px) {
            .container {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p class="error">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }


        // Inside login.php, near the top or above the form
if (isset($_SESSION['success'])) {
    echo '<p style="color: green; text-align: center;">' . $_SESSION['success'] . '</p>';
    unset($_SESSION['success']);
}
        ?>

        

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <p class="terms">
                By clicking login, you agree to the <a href="#">terms and conditions</a>
            </p>

            <div class="form-group">
                <input type="submit" value="Login">
            </div>

            <a href="register.php">Don't have an account? Sign up</a>
        </form>
    </div>
</body>
</html>