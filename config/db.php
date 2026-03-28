<?php
// Database connection
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'aqpg_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    error_log('MySQL connection failed: ' . $conn->connect_error);
    die('Failed to connect to database.');
}

$conn->set_charset('utf8mb4');
