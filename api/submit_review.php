<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tools.php');
    exit;
}

$tool_id    = isset($_POST['tool_id'])    ? (int) $_POST['tool_id'] : 0;
$rating     = isset($_POST['rating'])     ? (int) $_POST['rating']  : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

if ($rating < 1 || $rating > 5) {
    header('Location: tool_detail.php?id=' . $tool_id . '&review_error=1');
    exit;
}

$stmt = $conn->prepare('SELECT id FROM ai_tools WHERE id = ?');
$stmt->bind_param('i', $tool_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $stmt->close();
    header('Location: tools.php');
    exit;
}
$stmt->close();

$stmt = $conn->prepare('SELECT id FROM reviews WHERE user_id = ? AND tool_id = ?');
$stmt->bind_param('ii', $_SESSION['user_id'], $tool_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    header('Location: tool_detail.php?id=' . $tool_id . '&review_error=1');
    exit;
}
$stmt->close();

$stmt = $conn->prepare('INSERT INTO reviews (user_id, tool_id, rating, review_text) VALUES (?, ?, ?, ?)');
$stmt->bind_param('iiis', $_SESSION['user_id'], $tool_id, $rating, $review_text);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare('UPDATE ai_tools SET rating = IFNULL((SELECT ROUND(AVG(rating), 1) FROM reviews WHERE tool_id = ?), 4.5) WHERE id = ?');
$stmt->bind_param('ii', $tool_id, $tool_id);
$stmt->execute();
$stmt->close();

header('Location: tool_detail.php?id=' . $tool_id . '&review_success=1');
exit;
