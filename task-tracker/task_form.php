<?php
// PAGE 4: Create and Update in one file.
//   task_form.php        -> create mode
//   task_form.php?id=12  -> edit mode for task 12 (only if it belongs to the logged-in user)
require __DIR__ . '/includes/auth.php';
require_login();
$pdo = require __DIR__ . '/config/db.php';

$statuses   = ['pending' => 'Pending', 'in_progress' => 'In progress', 'done' => 'Done'];
$priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];

$editing = isset($_GET['id']);
$task_id = 0;
if ($editing) {
    $raw_id  = input_str($_GET, 'id');
    $task_id = ctype_digit($raw_id) ? (int) $raw_id : 0;   // junk becomes 0, which matches no row
}

$errors = [];
$values = [
    'title'       => '',
    'description' => '',
    'priority'    => 'medium',
    'status'      => 'pending',
    'due_date'    => '',
];

if ($editing) {
    // Ownership check: the task must belong to the logged-in user. If it belongs to someone
    // else (or does not exist) we answer 404 and show nothing about it.
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$task_id, current_user_id()]);
    $task = $stmt->fetch();

    if (!$task) {
        http_response_code(404);
        $page_title = 'Task not found';
        require __DIR__ . '/includes/header.php';
        ?>
        <section class="card not-found">
            <span class="big" aria-hidden="true">404</span>
            <h1>Task not found</h1>
            <p class="page-sub" style="margin-bottom: 1.5rem;">This task does not exist, or it is not yours.</p>
            <a class="btn btn-primary" href="tasks.php"><svg class="icon" aria-hidden="true"><use href="#i-back"/></svg> Back to my tasks</a>
        </section>
        <?php
        require __DIR__ . '/includes/footer.php';
        exit;
    }

    $values = [
        'title'       => $task['title'],
        'description' => (string) $task['description'],
        'priority'    => $task['priority'],
        'status'      => $task['status'],
        'due_date'    => (string) $task['due_date'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf'] ?? null);

    $values['title']       = trim(input_str($_POST, 'title'));
    $values['description'] = trim(input_str($_POST, 'description'));
    $values['priority']    = input_str($_POST, 'priority');
    $values['status']      = input_str($_POST, 'status');
    $values['due_date']    = trim(input_str($_POST, 'due_date'));

    if ($values['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($values['title'], 'UTF-8') > 150) {
        $errors[] = 'Title must be 150 characters or fewer.';
    }
    if (mb_strlen($values['description'], 'UTF-8') > 5000) {
        $errors[] = 'Description must be 5000 characters or fewer.';
    }
    if (!isset($priorities[$values['priority']])) {
        $errors[] = 'Choose a valid priority.';
    }
    if (!isset($statuses[$values['status']])) {
        $errors[] = 'Choose a valid status.';
    }
    if ($values['due_date'] !== '') {
        // Formatting the parsed date back and comparing rejects impossible dates such as
        // 2026-02-31, which createFromFormat() would otherwise silently roll over to March.
        $date = DateTime::createFromFormat('Y-m-d', $values['due_date']);
        if (!$date || $date->format('Y-m-d') !== $values['due_date']) {
            $errors[] = 'Due date must be a real date in the format YYYY-MM-DD.';
        }
    }

    if (!$errors) {
        // Empty optional fields are stored as NULL.
        $description = $values['description'] === '' ? null : $values['description'];
        $due_date    = $values['due_date'] === '' ? null : $values['due_date'];

        if ($editing) {
            // AND user_id = ? again: ownership is enforced in the write, not only in the load above.
            $stmt = $pdo->prepare(
                'UPDATE tasks SET title = ?, description = ?, priority = ?, status = ?, due_date = ?
                 WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([
                $values['title'], $description, $values['priority'], $values['status'], $due_date,
                $task_id, current_user_id(),
            ]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO tasks (user_id, title, description, priority, status, due_date)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                current_user_id(), $values['title'], $description,
                $values['priority'], $values['status'], $due_date,
            ]);
        }

        header('Location: tasks.php?saved=1');
        exit;
    }
}

$page_title  = $editing ? 'Edit task' : 'New task';
$form_action = 'task_form.php' . ($editing ? '?id=' . $task_id : '');
require __DIR__ . '/includes/header.php';
?>
<div class="form-page">
<div class="page-head">
    <div>
        <h1><?= $editing ? 'Edit task' : 'New task' ?></h1>
        <p class="page-sub"><?= $editing ? 'Update the details, then save your changes.' : 'Give it a title, then add a priority and a due date if you like.' ?></p>
    </div>
    <a class="btn btn-secondary" href="tasks.php"><svg class="icon" aria-hidden="true"><use href="#i-back"/></svg> Back to tasks</a>
</div>

<section class="card form-card">
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

    <!-- novalidate: main.js shows the friendly "title required" message. With JavaScript off the
         form still submits and the server-side checks above catch the problem. -->
    <form method="post" action="<?= e($form_action) ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label for="title" style="margin-top: 0;">Title <span class="req">*</span></label>
        <input type="text" id="title" name="title" maxlength="150" value="<?= e($values['title']) ?>" required aria-describedby="title-help">
        <div class="field-help" id="title-help">
            <span id="title-error" class="field-error" role="alert" hidden></span>
            <span class="counter"><span id="title-count">0</span>/150</span>
        </div>

        <label for="description">Description (optional)</label>
        <textarea id="description" name="description" maxlength="5000"><?= e($values['description']) ?></textarea>

        <div class="form-row">
            <div>
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <?php foreach ($priorities as $value => $label): ?>
                        <option value="<?= e($value) ?>"<?= $values['priority'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= e($value) ?>"<?= $values['status'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="due_date">Due date (optional)</label>
                <input type="date" id="due_date" name="due_date" value="<?= e($values['due_date']) ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> <?= $editing ? 'Save changes' : 'Create task' ?></button>
            <a class="btn btn-secondary btn-lg" href="tasks.php">Cancel</a>
        </div>
    </form>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
