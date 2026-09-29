<?php
// PAGE 5: Overview. Task counts per status, plus a list of overdue tasks.
require __DIR__ . '/includes/auth.php';
require_login();
$pdo = require __DIR__ . '/config/db.php';

$statuses = ['pending' => 'Pending', 'in_progress' => 'In progress', 'done' => 'Done'];
$icons    = ['pending' => 'i-clock', 'in_progress' => 'i-bars', 'done' => 'i-check'];

// GROUP BY only returns statuses that have rows, so start every count at 0.
$counts = array_fill_keys(array_keys($statuses), 0);

$stmt = $pdo->prepare('SELECT status, COUNT(*) AS c FROM tasks WHERE user_id = ? GROUP BY status');
$stmt->execute([current_user_id()]);
foreach ($stmt->fetchAll() as $row) {
    $counts[$row['status']] = (int) $row['c'];
}

// A NULL due date makes "due_date < CURDATE()" NULL, so tasks with no due date never appear.
$stmt = $pdo->prepare(
    "SELECT id, title, due_date FROM tasks
     WHERE user_id = ? AND status <> 'done' AND due_date < CURDATE()
     ORDER BY due_date ASC"
);
$stmt->execute([current_user_id()]);
$overdue = $stmt->fetchAll();

$page_title = 'Overview';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Overview</h1>
        <p class="page-sub">Where every task stands right now.</p>
    </div>
    <a class="btn btn-secondary" href="tasks.php"><svg class="icon" aria-hidden="true"><use href="#i-list"/></svg> View all tasks</a>
</div>

<div class="stat-grid">
    <?php foreach ($statuses as $key => $label): ?>
        <div class="stat-card stat-<?= e($key) ?>">
            <span class="stat-icon" aria-hidden="true"><svg class="icon"><use href="#<?= e($icons[$key]) ?>"/></svg></span>
            <span class="stat-number"><?= $counts[$key] ?></span>
            <span class="stat-label"><?= e($label) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="section-head">
    <h2>Overdue tasks</h2>
    <?php if ($overdue): ?><span class="count-chip"><?= count($overdue) ?></span><?php endif; ?>
</div>

<?php if (!$overdue): ?>
    <div class="empty">
        <svg class="empty-art" aria-hidden="true"><use href="#i-empty"/></svg>
        <p class="empty-title">No overdue tasks.</p>
        <p class="empty-sub">You are all caught up. Nothing is past its due date.</p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="task-table">
            <thead>
                <tr>
                    <th scope="col">Task</th>
                    <th scope="col">Due date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($overdue as $task): ?>
                    <tr class="overdue">
                        <td class="cell-title" data-label="Task"><a href="task_form.php?id=<?= (int) $task['id'] ?>"><?= e($task['title']) ?></a></td>
                        <td data-label="Due date"><span><time datetime="<?= e($task['due_date']) ?>"><?= e(fmt_date($task['due_date'])) ?></time><span class="overdue-tag">Overdue</span></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
