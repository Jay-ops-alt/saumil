<?php
// Database connection
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'aqpg_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    die('Failed to connect to MySQL: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
