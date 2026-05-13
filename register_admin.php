<?php
session_start();
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert new admin
            $insert = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $insert->bind_param("ss", $username, $hashed_password);
            if ($insert->execute()) {
                header("Location: admin_login.php?success=registered");
                exit;
            } else {
                $error = "Error registering admin.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
    <style>
        body {
            background-color: #111;
            color: #fff;
            font-family: Arial, sans-serif;
            padding-top: 100px;
            text-align: center;
        }
        .form-container {
            background-color: #222;
            padding: 20px;
            margin: auto;
            width: 400px;
            border-radius: 10px;
            box-shadow: 0 0 10px #000;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px auto;
            display: block;
            border: none;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            margin-top: 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: #388E3C;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Register Admin</h1>
        <?php if (isset($error)) echo "<p class='error'>{$error}</p>"; ?>
        <form method="post" action="register_admin.php">
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register Admin</button>
        </form>
        <p><a href="admin_login.php" style="color: #4CAF50;">Back to Login</a></p>
    </div>
</body>
</html>
