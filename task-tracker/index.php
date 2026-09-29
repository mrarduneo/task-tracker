<?php
// PAGE 1: Home (public). No database query; the session is only read so the buttons
// and the nav can change when someone is logged in.
require __DIR__ . '/includes/auth.php';

$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> Your personal to-do list</p>
        <h1>Plan it. <span class="hl">Track it.</span> Finish it.</h1>
        <p class="lead">
            A small web app for keeping your own to-do list in one place. Add tasks, give them
            a priority and a due date, and mark them done as you go.
        </p>
        <div class="hero-actions">
            <?php if (current_user_id() !== null): ?>
                <a class="btn btn-primary btn-lg" href="tasks.php">Go to my tasks <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
            <?php else: ?>
                <a class="btn btn-primary btn-lg" href="register.php">Create an account <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
                <a class="btn btn-secondary btn-lg" href="login.php">Log in</a>
            <?php endif; ?>
        </div>
        <p class="privacy-note"><svg class="icon" aria-hidden="true"><use href="#i-lock"/></svg> Your tasks are private: only you can see, change or delete them.</p>
    </div>

    <!-- Decorative illustration of the app: purely visual, hidden from screen readers. -->
    <div class="hero-art" aria-hidden="true">
        <span class="art-sun"></span>
        <div class="mock mock-1">
            <p class="mock-title">Prepare the presentation</p>
            <div class="mock-meta"><span class="badge badge-high">High</span><span class="badge badge-pending">Pending</span></div>
            <p class="mock-due"><svg class="icon"><use href="#i-clock"/></svg> Due tomorrow</p>
        </div>
        <div class="mock mock-2">
            <p class="mock-title">Review the PHP notes</p>
            <div class="mock-meta"><span class="badge badge-medium">Medium</span><span class="badge badge-in_progress">In progress</span></div>
        </div>
        <div class="mock mock-3 is-done">
            <p class="mock-title">Set up the database</p>
            <div class="mock-meta"><span class="badge badge-low">Low</span><span class="badge badge-done">Done</span></div>
        </div>
        <span class="art-stamp">All done!</span>
    </div>
</section>

<section aria-labelledby="features-title">
    <h2 class="section-title" id="features-title">What you can do</h2>
    <div class="features">
        <article class="feature">
            <span class="chip chip-yellow"><svg class="icon" aria-hidden="true"><use href="#i-list"/></svg></span>
            <h3>Your own list</h3>
            <p>Create, edit and delete your tasks whenever you need to.</p>
        </article>
        <article class="feature">
            <span class="chip chip-lilac"><svg class="icon" aria-hidden="true"><use href="#i-bars"/></svg></span>
            <h3>Priority and status</h3>
            <p>Low, medium or high priority. Pending, in progress or done.</p>
        </article>
        <article class="feature">
            <span class="chip chip-sky"><svg class="icon" aria-hidden="true"><use href="#i-search"/></svg></span>
            <h3>Search and filter</h3>
            <p>Find a task by its title, or filter by status or priority.</p>
        </article>
        <article class="feature">
            <span class="chip chip-mint"><svg class="icon" aria-hidden="true"><use href="#i-clock"/></svg></span>
            <h3>Overview</h3>
            <p>See how many tasks are in each status, and which are overdue.</p>
        </article>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
