<?php
// Session start, login guard, CSRF helpers and the output-escaping helper.
// Every page requires this file first.

if (session_status() === PHP_SESSION_NONE) {
    // HttpOnly: JavaScript cannot read the session cookie (limits XSS damage).
    // SameSite=Lax: the browser does not send it on cross-site POSTs (extra CSRF defence).
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Id of the logged-in user, or null when nobody is logged in.
function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

// Guard for every page that needs a logged-in user.
function require_login(): void
{
    if (current_user_id() === null) {
        header('Location: login.php');
        exit;
    }
}

// One random token per session, created on first use.
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Call at the top of every POST handler. hash_equals() compares in constant time.
function csrf_verify($token): void
{
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        die('Invalid request (CSRF token mismatch). Go back, reload the page and try again.');
    }
}

// Escape a value for safe output inside HTML. Use it on every echoed value.
function e($str): string
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

// Turn a "YYYY-MM-DD" date from the database into "19 Sep 2026" for display.
function fmt_date($ymd): string
{
    $date = DateTime::createFromFormat('Y-m-d', (string) $ymd);
    return $date ? $date->format('j M Y') : (string) $ymd;
}

// Read a text field from $_GET or $_POST. Returns '' if it is missing or is an array
// (e.g. ?q[]=x), so later string functions never receive the wrong type.
function input_str(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? $value : '';
}
