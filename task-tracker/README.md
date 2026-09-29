# Task Tracker

A small PHP + MySQL web application where registered users manage their own private to-do list: create tasks, set a priority and due date, search and filter them, and see an overview of what is pending or overdue.

Built for **CSE 472 Web and Internet Programming**, Southeast University (individual project, full-stack mini web application).

## Live demo

**URL:** https://hamim-tasktracker.infinityfree.me

## Features

- Register, log in and log out. Passwords are stored as bcrypt hashes (`password_hash`).
- Create, read, update and delete tasks (title, description, priority, status, due date).
- Search tasks by title and filter by status and by priority.
- Overview page with the number of tasks per status and a list of overdue tasks.
- Every user sees and changes only their own tasks.
- Security: PDO prepared statements, CSRF tokens on every POST form, output escaping, session id regeneration on login, POST-only delete, generic login errors.

## Tech stack

| Layer    | Technology                                 |
|----------|--------------------------------------------|
| Frontend | HTML5, CSS3, vanilla JavaScript            |
| Backend  | PHP 8 (no framework, no Composer)          |
| Database | MySQL / MariaDB, accessed through PDO      |
| Hosting  | InfinityFree (free PHP + MySQL hosting)    |

## Screenshots

<!-- TODO: add 2-3 screenshots (e.g. docs/screenshots/tasks.png) and link them here -->
_To be added._

## Local setup

**Requirements:** PHP 8 or newer with the `pdo_mysql` and `mbstring` extensions, and MySQL or MariaDB. XAMPP includes all of this.

1. **Get the code**
   ```bash
   git clone https://github.com/mrarduneo/task-tracker.git
   cd task-tracker
   ```
2. **Create the database and tables.** Either import `schema.sql` in phpMyAdmin (Import tab), or from a terminal:
   ```bash
   mysql -u root < schema.sql
   ```
   This creates a database called `task_tracker` with the tables `users` and `tasks`.
3. **Check the credentials** at the top of `config/db.php`. The defaults match a fresh XAMPP install:

   | Setting  | Value          |
   |----------|----------------|
   | Host     | `127.0.0.1`    |
   | Database | `task_tracker` |
   | User     | `root`         |
   | Password | _(empty)_      |

4. **Run the app**, using either option:
   - **XAMPP:** copy the `task-tracker` folder into `xampp/htdocs`, start Apache and MySQL, then open `http://localhost/task-tracker/`.
   - **PHP built-in server:** from the project folder run
     ```bash
     php -S localhost:8000
     ```
     and open `http://localhost:8000`.
5. Click **Create an account**, register, and start adding tasks.

`config/db.php` also turns `display_errors` off (production behaviour). While debugging locally, change that line to `'1'`.

## Deploying to InfinityFree

1. Create a hosting account and a free subdomain, then create a MySQL database in the control panel. Note the host (a `sqlXXX.infinityfree.com` address, not `localhost`), database name, user and password.
2. Open the database in phpMyAdmin and import `schema.sql` **after deleting its first two statements** (`CREATE DATABASE` and `USE`), because shared hosting does not allow creating databases with SQL.
3. Upload the project files into `htdocs/` (File Manager or FTP).
4. Edit `config/db.php` **on the server only** and enter the live host, database name, user and password. Do not commit live credentials to GitHub.
5. Open the live URL and run through the test cases again.

## Folder structure

```
task-tracker/
├── config/
│   └── db.php            PDO connection
├── includes/
│   ├── auth.php          session, login guard, CSRF helpers, e() escaping
│   ├── header.php        <head> and navigation
│   └── footer.php
├── assets/
│   ├── css/style.css
│   └── js/main.js        client-side validation, delete confirm, filter auto-submit
├── index.php             Home (public)
├── register.php          Register
├── login.php             Login
├── logout.php            Ends the session
├── tasks.php             Task list, search and filters, delete button
├── task_form.php         Create and edit a task
├── task_delete.php       POST-only delete handler
├── overview.php          Counts per status and overdue tasks
├── schema.sql            Database schema
└── README.md
```

## Credits

No CSS framework, icon set or image library is used: the HTML, CSS and JavaScript are plain code, and the icons and illustrations are custom inline SVG. The only third-party assets are two fonts loaded from [Google Fonts](https://fonts.google.com/) under the SIL Open Font License: [Bricolage Grotesque](https://fonts.google.com/specimen/Bricolage+Grotesque) (headings) and [Instrument Sans](https://fonts.google.com/specimen/Instrument+Sans) (body text). The code follows the official documentation of PHP (PDO, `password_hash`, sessions) and MySQL.
