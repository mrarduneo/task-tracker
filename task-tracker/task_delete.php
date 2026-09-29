<?php
// POST-only delete handler. Opening this URL in the address bar (a GET) does nothing.
require __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

$pdo = require __DIR__ . '/config/db.php';
csrf_verify($_POST['csrf'] ?? null);

$raw_id  = input_str($_POST, 'id');
$deleted = false;

if (ctype_digit($raw_id)) {
    // AND user_id = ?: a user can only ever delete their own tasks.
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([(int) $raw_id, current_user_id()]);
    $deleted = $stmt->rowCount() > 0;
}

// Only claim success if a row was really removed (not someone else's task or a stale id).
header('Location: tasks.php' . ($deleted ? '?deleted=1' : ''));
exit;
