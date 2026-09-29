<?php
// PAGE 2b: Login.
require __DIR__ . '/includes/auth.php';
$pdo = require __DIR__ . '/config/db.php';

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf'] ?? null);

    $email    = trim(input_str($_POST, 'email'));
    $password = input_str($_POST, 'password');

    $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // New session id after login, so a session id planted before login is useless.
        session_regenerate_id(true);
        $_SESSION['user_id']   = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: tasks.php');
        exit;
    }

    // One generic message: never reveal whether the email or the password was wrong.
    $errors[] = 'Invalid email or password.';
}

$page_title = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<section class="auth">
    <aside class="auth-aside">
        <span class="chip chip-white"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg></span>
        <p class="aside-title">Welcome back.</p>
        <p class="aside-text">Log in to pick your list up right where you left it.</p>
        <ul class="auth-points">
            <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> Your tasks stay private</li>
            <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> Priorities and due dates</li>
            <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> Search and filter</li>
        </ul>
    </aside>

    <div class="auth-main">
        <h1>Log in</h1>

        <?php if (isset($_GET['registered']) && !$errors): ?>
            <div class="alert alert-success" role="status">
                <svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>
                <span>Account created. You can log in now.</span>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-error" role="alert">
                <svg class="icon" aria-hidden="true"><use href="#i-alert"/></svg>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= e($email) ?>" required autocomplete="email">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Log in <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button>
            </div>
        </form>
        <p class="auth-switch">New here? <a href="register.php">Create an account</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
