<?php
// Shared <head>, icon sprite and top navigation. Each page sets $page_title, then requires
// this file. Every redirect must happen BEFORE this file is included, because it starts
// sending HTML.
$logged_in = current_user_id() !== null;
$this_page = basename($_SERVER['SCRIPT_NAME']);

// [file, label, extra CSS class]
$nav_links = $logged_in
    ? [['index.php', 'Home', ''], ['tasks.php', 'My Tasks', ''], ['task_form.php', 'New Task', ''], ['overview.php', 'Overview', '']]
    : [['index.php', 'Home', ''], ['login.php', 'Login', ''], ['register.php', 'Register', 'nav-cta']];

$user_name = (string) ($_SESSION['user_name'] ?? '');
$initial   = $user_name !== '' ? mb_strtoupper(mb_substr($user_name, 0, 1, 'UTF-8'), 'UTF-8') : '?';

// The ?v= number changes whenever a file is re-uploaded, so browsers never show an old stylesheet.
$css_file = __DIR__ . '/../assets/css/style.css';
$js_file  = __DIR__ . '/../assets/js/main.js';
$css_ver  = is_file($css_file) ? filemtime($css_file) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFD84D">
    <title><?= e($page_title ?? 'Task Tracker') ?> | Task Tracker</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 42 42'%3E%3Crect x='5' y='5' width='34' height='34' rx='10' fill='%2317130F'/%3E%3Crect x='2' y='2' width='34' height='34' rx='10' fill='%23FFD84D' stroke='%2317130F' stroke-width='2.5'/%3E%3Cpath d='M11 19.5l6 6 11-13' fill='none' stroke='%2317130F' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&amp;family=Instrument+Sans:wght@400..700&amp;display=swap">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= (int) $css_ver ?>">
</head>
<body>
<!-- Icon sprite: custom 24x24 line icons, used as <svg class="icon"><use href="#i-name"/></svg> -->
<svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="M5 12.8l4.4 4.4L19.2 7.4"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></symbol>
    <symbol id="i-edit" viewBox="0 0 24 24"><path d="M4 20l1-4.2L16.3 4.5a2.1 2.1 0 013 3L8 18.8 4 20z"/><path d="M14.5 6.3l3.2 3.2"/></symbol>
    <symbol id="i-trash" viewBox="0 0 24 24"><path d="M4.5 7h15M9.5 7V4.8h5V7M6.6 7l.9 12.2h9L17.4 7M10 11v5M14 11v5"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></symbol>
    <symbol id="i-list" viewBox="0 0 24 24"><path d="M9.5 7h10M9.5 12h10M9.5 17h10M4.5 7h.01M4.5 12h.01M4.5 17h.01"/></symbol>
    <symbol id="i-bars" viewBox="0 0 24 24"><path d="M6 19v-4M12 19v-8M18 19V6"/></symbol>
    <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13.5 6.5L19 12l-5.5 5.5"/></symbol>
    <symbol id="i-back" viewBox="0 0 24 24"><path d="M19 12H5M10.5 6.5L5 12l5.5 5.5"/></symbol>
    <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 4.5l9 15.5H3l9-15.5zM12 10v4M12 17.3v.01"/></symbol>
    <symbol id="i-lock" viewBox="0 0 24 24"><rect x="5" y="10.5" width="14" height="9.5" rx="2.2"/><path d="M8.5 10.5V8a3.5 3.5 0 017 0v2.5"/></symbol>
    <!-- Illustration for empty states (own colours, so it does not use the .icon styles) -->
    <symbol id="i-empty" viewBox="0 0 150 118">
        <rect x="24" y="16" width="88" height="86" rx="12" fill="#FFFFFF" stroke="#17130F" stroke-width="3" transform="rotate(-7 68 59)"/>
        <rect x="38" y="10" width="88" height="86" rx="12" fill="#FFD84D" stroke="#17130F" stroke-width="3" transform="rotate(6 82 53)"/>
        <path d="M58 40h50M58 54h38M58 68h26" fill="none" stroke="#17130F" stroke-width="4.5" stroke-linecap="round" transform="rotate(6 82 53)"/>
        <circle cx="128" cy="24" r="7" fill="#A9EBC8" stroke="#17130F" stroke-width="3"/>
        <circle cx="20" cy="92" r="6" fill="#FF8F7D" stroke="#17130F" stroke-width="3"/>
    </symbol>
</svg>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">
            <svg class="brand-mark" viewBox="0 0 42 42" aria-hidden="true">
                <rect x="5" y="5" width="34" height="34" rx="10" fill="#17130F"/>
                <rect x="2" y="2" width="34" height="34" rx="10" fill="#FFD84D" stroke="#17130F" stroke-width="2.5"/>
                <path d="M11 19.5l6 6 11-13" fill="none" stroke="#17130F" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Task Tracker
        </a>
        <nav class="site-nav" aria-label="Main">
            <?php foreach ($nav_links as [$href, $label, $extra_class]):
                // "New Task" and "Edit task" share one file, so only highlight it in create mode.
                $active = $this_page === $href && !($href === 'task_form.php' && isset($_GET['id']));
            ?>
                <a href="<?= e($href) ?>"<?= $extra_class !== '' ? ' class="' . e($extra_class) . '"' : '' ?><?= $active ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>
            <?php if ($logged_in): ?>
                <span class="nav-user"><span class="avatar" aria-hidden="true"><?= e($initial) ?></span><span class="nav-user-name"><?= e($user_name) ?></span></span>
                <a class="nav-out" href="logout.php">Log out</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
