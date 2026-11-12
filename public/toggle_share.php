<?php
session_start();

// Must be logged in to share or unshare snippets
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

$userId = $_SESSION['user_id'];
$snippetId = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($snippetId <= 0) {
    header("Location: dashboard.php");
    exit;
}

// 1. Check current public status of snippet (ensure it belongs to current user)
$stmt = $pdo->prepare("SELECT id, is_public, share_token FROM snippets WHERE id = ? AND user_id = ?");
$stmt->execute([$snippetId, $userId]);
$snippet = $stmt->fetch();

if (!$snippet) {
    header("Location: dashboard.php");
    exit;
}

// 2. Toggle status
if ($snippet['is_public']) {
    // If currently public -> turn OFF, remove token
    $updateStmt = $pdo->prepare("UPDATE snippets SET is_public = 0, share_token = NULL WHERE id = ? AND user_id = ?");
    $updateStmt->execute([$snippetId, $userId]);
} else {
    // If currently private -> turn ON, generate random 32-character token
    $newToken = bin2hex(random_bytes(16));
    $updateStmt = $pdo->prepare("UPDATE snippets SET is_public = 1, share_token = ? WHERE id = ? AND user_id = ?");
    $updateStmt->execute([$newToken, $snippetId, $userId]);
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
