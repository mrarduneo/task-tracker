<?php
// PAGE 2a: Register.
require __DIR__ . '/includes/auth.php';
$pdo = require __DIR__ . '/config/db.php';

$errors = [];
$name   = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf'] ?? null);

    $name     = trim(input_str($_POST, 'name'));
    $email    = trim(input_str($_POST, 'email'));
    $password = input_str($_POST, 'password');   // passwords are never trimmed
    $confirm  = input_str($_POST, 'confirm_password');

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    }
    if (mb_strlen($name, 'UTF-8') > 100) {
        $errors[] = 'Name must be 100 characters or fewer.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (mb_strlen($email, 'UTF-8') > 150) {
        $errors[] = 'Email must be 150 characters or fewer.';
    }
    if ($password !== '' && mb_strlen($password, 'UTF-8') < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Only look for a duplicate once the email itself is valid.
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $ex) {
            // 23000 = unique-key violation: someone registered the same email between
            // our SELECT and this INSERT. The UNIQUE index on users.email is the final guard.
            if ($ex->getCode() !== '23000') {
                throw $ex;
            }
            $errors[] = 'Email already registered.';
        }
    }
}

$page_title = 'Register';
require __DIR__ . '/includes/header.php';
?>
<section class="auth">
    <aside class="auth-aside">
        <span class="chip chip-white"><svg class="icon" aria-hidden="true"><use href="#i-list"/></svg></span>
        <p class="aside-title">Start your list.</p>
        <p class="aside-text">Make an account in under a minute and get your tasks in order.</p>
        <ul class="auth-points">
            <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> Your tasks stay private</li>
            <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> Priorities and due dates</li>
            <li><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> Search and filter</li>
        </ul>
    </aside>

    <div class="auth-main">
        <h1>Create an account</h1>

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

        <form method="post" action="register.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="100" value="<?= e($name) ?>" required autocomplete="name">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= e($email) ?>" required autocomplete="email">

            <label for="password">Password (at least 8 characters)</label>
            <input type="password" id="password" name="password" minlength="8" required autocomplete="new-password">

            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required autocomplete="new-password">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Register <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button>
            </div>
        </form>
        <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
