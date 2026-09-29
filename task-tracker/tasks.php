<?php
// PAGE 3: Task list with search and filters (Read), plus the Delete button.
require __DIR__ . '/includes/auth.php';
require_login();
$pdo = require __DIR__ . '/config/db.php';

// Allowed values for the two filters (the same values as the ENUM columns) and their labels.
$statuses   = ['pending' => 'Pending', 'in_progress' => 'In progress', 'done' => 'Done'];
$priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'];

$q        = trim(input_str($_GET, 'q'));
$status   = input_str($_GET, 'status');
$priority = input_str($_GET, 'priority');

// Anything that is not on the whitelist is treated as "no filter".
if (!isset($statuses[$status])) {
    $status = '';
}
if (!isset($priorities[$priority])) {
    $priority = '';
}

// "WHERE user_id = ?" is never optional: it is the ownership rule. Filters are added with
// placeholders only, so user input never becomes part of the SQL text.
// is_overdue is worked out by MySQL (same CURDATE() as overview.php) so both pages agree;
// it is NULL or 0 for tasks with no due date, so those are never overdue.
$sql = "SELECT *, (status <> 'done' AND due_date < CURDATE()) AS is_overdue
        FROM tasks
        WHERE user_id = ?";
$params = [current_user_id()];

if ($q !== '') {
    $sql .= ' AND title LIKE ?';
    // Escape % _ and \ so the search text is matched literally, not as LIKE wildcards.
    $params[] = '%' . addcslashes($q, '%_\\') . '%';
}
if ($status !== '') {
    $sql .= ' AND status = ?';
    $params[] = $status;
}
if ($priority !== '') {
    $sql .= ' AND priority = ?';
    $params[] = $priority;
}

// Soonest due date first, tasks without a due date last, newest first as a tie-break.
$sql .= ' ORDER BY (due_date IS NULL), due_date ASC, id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$filtering = ($q !== '' || $status !== '' || $priority !== '');

// Fixed messages chosen by flag; nothing from the URL is ever printed.
$flash = null;
if (isset($_GET['saved'])) {
    $flash = 'Task saved.';
} elseif (isset($_GET['deleted'])) {
    $flash = 'Task deleted.';
}

$page_title = 'My Tasks';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>My Tasks</h1>
        <p class="page-sub">Soonest deadline first. Tasks without a due date wait at the bottom.</p>
    </div>
    <a class="btn btn-primary" href="task_form.php"><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg> New task</a>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success" role="status">
        <svg class="icon" aria-hidden="true"><use href="#i-check"/></svg>
        <span><?= e($flash) ?></span>
    </div>
<?php endif; ?>

<form class="filters" method="get" action="tasks.php" data-autosubmit>
    <div class="field grow">
        <label for="q">Search title</label>
        <div class="input-icon">
            <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="e.g. report">
        </div>
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">Any status</option>
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $status === $value ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="priority">Priority</label>
        <select id="priority" name="priority">
            <option value="">Any priority</option>
            <?php foreach ($priorities as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $priority === $value ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field buttons">
        <button type="submit" class="btn btn-dark">Apply</button>
        <a class="btn btn-secondary" href="tasks.php">Clear</a>
    </div>
</form>

<?php if (!$tasks): ?>
    <div class="empty">
        <svg class="empty-art" aria-hidden="true"><use href="#i-empty"/></svg>
        <p class="empty-title">No tasks found.</p>
        <?php if ($filtering): ?>
            <p class="empty-sub">Nothing matches that search. Try different words, or clear the filters.</p>
            <a class="btn btn-secondary" href="tasks.php">Clear filters</a>
        <?php else: ?>
            <p class="empty-sub">Your list is empty. Add your first task to get started.</p>
            <a class="btn btn-primary" href="task_form.php"><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg> New task</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p class="result-count"><?= count($tasks) ?> <?= count($tasks) === 1 ? 'task' : 'tasks' ?><?= $filtering ? ' match your filters' : '' ?></p>
    <div class="table-wrap">
        <table class="task-table">
            <thead>
                <tr>
                    <th scope="col">Task</th>
                    <th scope="col">Priority</th>
                    <th scope="col">Status</th>
                    <th scope="col">Due date</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr<?= $task['is_overdue'] ? ' class="overdue"' : '' ?>>
                        <td class="cell-title" data-label="Task"><?= e($task['title']) ?></td>
                        <td data-label="Priority"><span class="badge badge-<?= e($task['priority']) ?>"><?= e($priorities[$task['priority']]) ?></span></td>
                        <td data-label="Status"><span class="badge badge-<?= e($task['status']) ?>"><?= e($statuses[$task['status']]) ?></span></td>
                        <td data-label="Due date">
                            <?php if ($task['due_date'] === null): ?>
                                <span class="no-date">&mdash;</span>
                            <?php else: ?>
                                <span><time datetime="<?= e($task['due_date']) ?>"><?= e(fmt_date($task['due_date'])) ?></time><?php if ($task['is_overdue']): ?><span class="overdue-tag">Overdue</span><?php endif; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-actions">
                            <a class="btn btn-secondary btn-small" href="task_form.php?id=<?= (int) $task['id'] ?>"><svg class="icon" aria-hidden="true"><use href="#i-edit"/></svg> Edit</a>
                            <!-- Delete is a POST form (never a link) so it needs the CSRF token. -->
                            <form method="post" action="task_delete.php" data-confirm="Delete this task? This cannot be undone.">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-small"><svg class="icon" aria-hidden="true"><use href="#i-trash"/></svg> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
