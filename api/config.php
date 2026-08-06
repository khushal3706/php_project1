<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host     = getenv('DB_HOST') ?: 'localhost';
$db_user  = getenv('DB_USER') ?: 'root';
$db_pass  = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'ai_tool_portal';

$conn = new mysqli($host, $db_user, $db_pass, $database);

if ($conn->connect_error) {
    error_log("DB connection failed: " . $conn->connect_error);
    die("Service unavailable. Please try again later.");
}

$conn->set_charset('utf8mb4');