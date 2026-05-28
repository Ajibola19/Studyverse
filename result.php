<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. Check if email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email is already registered.";
    } else {
        // 2. Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Store the success message to show on the login page
            $_SESSION['success'] = "Account created successfully! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
    $checkEmail->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studyverse | Sign Up</title>
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
            margin-bottom: 30px;
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
            color: #d9534f;
            background: #f2dede;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            border: 1px solid #ebccd1;
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
        <h2>Sign Up</h2>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Full Name</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <p class="terms">
                By clicking sign up, you agree to the <a href="#">terms and conditions</a>
            </p>

            <div class="form-group">
                <input type="submit" value="Sign Up">
            </div>

            <a href="login.php">Already have an account? Login</a>
        </form>
    </div>
</body>
</html>
