<?php
$host = 'gateway01.us-east-1.prod.aws.tidbcloud.com';
$port = 4000;
$user = '3yvdKWJpMp4vpEP.root'; 
$pass = 'pi8kCfVEw0bU6bsA'; // The password you created in TiDB
$db   = 'studyverse_db'; 

$conn = mysqli_init();

// This line is MANDATORY for TiDB Cloud to work on Render
mysqli_ssl_set($conn, NULL, NULL, "/etc/ssl/certs/ca-certificates.crt", NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
