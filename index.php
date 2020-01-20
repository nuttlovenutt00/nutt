<?php
$servername = "http://viscctv.dyndns.org:8088/phpmyadmin/";
$username = "root";
$password = "root36698";

try {
    $conn = new PDO("mysql:host=$servername;dbname=hutcomp", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
?>
