<?php
session_start();
header('Content-Type: application/json');

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Snippet.php';

$data = json_decode(file_get_contents('php://input'), true);
$snippetId = isset($data['id']) ? (int)$data['id'] : 0;

if ($snippetId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid snippet ID']);
    exit;
}

$snippetModel = new Snippet($pdo);
$userId = $_SESSION['user_id'];

$deleted = $snippetModel->delete($snippetId, $userId);

if ($deleted) {
    echo json_encode(['success' => true, 'message' => 'Snippet deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete snippet']);
}
