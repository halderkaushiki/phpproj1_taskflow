# TaskFlow - PHP To-Do Manager
Features: register/login (hashed passwords), per-user tasks, priorities, categories, due dates,
notes, search, filters, sorting, progress ring, overdue alerts, edit dialog, delete with undo,
dark mode, keyboard shortcuts (N = new task, / = search), CSV export,
loading screen, footer, pinned tasks, checklists/subtasks with progress, Today and Overdue smart filters, Pomodoro focus timer.

Stack: PHP 7.4+ (PDO SQLite), vanilla JS (fetch API), CSS variables.

Run: `php -S localhost:8000` in this folder, open http://localhost:8000
Deploy: upload to any PHP host; make the folder writable so data.db can be created.

Files: index.php (app shell) | login.php (auth) | api.php (JSON endpoints) |
includes/db.php (DB + session) | assets/app.js | assets/style.css
