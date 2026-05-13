<?php
// connection.php
$host = "sql101.infinityfree.com"; // Your MySQL Hostname from the panel
$user = "if0_41894971";             // Your MySQL Username from the panel
$pass = "Ajifoden19";     // Your account password
$db   = "if0_41894971_study_verse";  // The full DB name created in the panel

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>