<?php
require __DIR__ . '/includes/db.php';
if (isset($_GET['logout'])) { session_destroy(); header('Location: login.php'); exit; }
$err = ''; $reg = isset($_GET['register']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) die('Invalid request');
    if (!$reg && isset($_POST['demo'])) {
        $s = $db->query('SELECT * FROM users WHERE is_demo=1'); $u = $s->fetch(PDO::FETCH_ASSOC);
        if (!$u) {
            $demoEmail = 'demo@taskflow.local';
            $s = $db->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$demoEmail]);
            if ($s->fetch()) {
                $err = 'The demo account could not be created. Please use a different email for your account.';
            } else {
                $db->beginTransaction();
                $db->prepare('INSERT INTO users(name,email,pass,is_demo) VALUES(?,?,?,1)')->execute([
                    'TaskFlow Demo', $demoEmail, password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT)
                ]);
                $uid = $db->lastInsertId();
                $task = $db->prepare('INSERT INTO tasks(user_id,title,notes,priority,category,due,done,pinned,subs) VALUES(?,?,?,?,?,?,?,?,?)');
                $samples = [
                    ['Plan the week', 'Choose the three most important things to finish.', 'high', 'Planning', date('Y-m-d', strtotime('+1 day')), 0, 1, '[{"t":"Review calendar","d":0},{"t":"Pick top priorities","d":1}]'],
                    ['Finish project brief', 'Share the first draft with the team.', 'medium', 'Work', date('Y-m-d', strtotime('+2 days')), 0, 0, '[]'],
                    ['Take a proper lunch break', 'Step away from the screen for a bit.', 'low', 'Personal', date('Y-m-d'), 1, 0, '[]'],
                    ['Read for 20 minutes', 'Make a little time to unwind.', 'low', 'Personal', null, 0, 0, '[]']
                ];
                foreach ($samples as $sample) $task->execute(array_merge([$uid], $sample));
                $db->commit();
                $s = $db->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$uid]); $u = $s->fetch(PDO::FETCH_ASSOC);
            }
        }
        if ($u) {
            session_regenerate_id(true); $_SESSION['uid'] = $u['id']; $_SESSION['name'] = $u['name'];
            header('Location: index.php'); exit;
        }
    } else {
        $email = strtolower(trim($_POST['email'] ?? '')); $pw = $_POST['password'] ?? '';
        if ($reg) {
            $name = trim($_POST['name'] ?? '');
            if ($email === 'demo@taskflow.local') {
                $err = 'That email is reserved for the demo account.';
            } elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) {
                $err = 'Enter a name, a valid email and a password of 6+ characters.';
            } else try {
                $db->prepare('INSERT INTO users(name,email,pass) VALUES(?,?,?)')->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
                session_regenerate_id(true); $_SESSION['uid'] = $db->lastInsertId(); $_SESSION['name'] = $name;
                header('Location: index.php'); exit;
            } catch (PDOException $x) { $err = 'That email is already registered.'; }
        } else {
            $s = $db->prepare('SELECT * FROM users WHERE email=?'); $s->execute([$email]); $u = $s->fetch(PDO::FETCH_ASSOC);
            if ($u && password_verify($pw, $u['pass'])) {
                session_regenerate_id(true); $_SESSION['uid'] = $u['id']; $_SESSION['name'] = $u['name'];
                header('Location: index.php'); exit;
            }
            $err = 'Wrong email or password.';
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div id="ld"><div class="sp"></div></div>
<div class="lg"><aside class="lg-hero"><div class="brand"><span class="logo">✓</span>TaskFlow</div><h2>Organise your day.<br>Own your goals.</h2><p>A simple, focused workspace for your tasks, deadlines and ideas.</p><ul class="feature-list" aria-label="TaskFlow features">
<li class="pt"><span class="pt-icon" aria-hidden="true">📌</span><span class="pt-copy"><strong>Pin and prioritise</strong><small>Keep what matters in clear view</small></span></li>
<li class="pt"><span class="pt-icon" aria-hidden="true">✅</span><span class="pt-copy"><strong>Checklists and smart filters</strong><small>Break work down and find it fast</small></span></li>
<li class="pt"><span class="pt-icon" aria-hidden="true">⏱</span><span class="pt-copy"><strong>Built-in focus timer</strong><small>Make space for uninterrupted work</small></span></li>
</ul></aside>
<main class="lg-form"><form class="lg-card" method="post" onsubmit="var b=document.activeElement;if(!b||b.type!=='submit')b=this.querySelector('.btn');b.classList.add('busy')">
<h1><?= $reg ? 'Create your account' : 'Welcome back 👋' ?></h1>
<p class="sub"><?= $reg ? 'Start organising in under a minute.' : 'Log in to continue to your tasks.' ?></p>
<?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<?php if ($reg): ?><div class="fld"><span class="i">👤</span><input name="name" placeholder="Full name" required></div><?php endif; ?>
<div class="fld"><span class="i">✉️</span><input type="email" name="email" placeholder="Email address" required></div>
<div class="fld"><span class="i">🔒</span><input type="password" name="password" placeholder="Password" required><button type="button" class="eye" onclick="var i=this.previousElementSibling;i.type=i.type==='password'?'text':'password'" aria-label="Show password">👁</button></div>
<button class="btn"><?= $reg ? 'Create account' : 'Log in' ?></button>
<?php if (!$reg): ?><div class="demo-sep"><span>or</span></div><button class="demo-btn" type="submit" name="demo" value="1" formnovalidate>Explore with a demo account</button><?php endif; ?>
<p class="alt"><?= $reg ? 'Already have an account? <a href="login.php">Log in</a>' : 'New here? <a href="?register">Create an account</a>' ?></p>
<footer class="ft">© <?= date("Y") ?> TaskFlow · Built by Kaushiki Halder</footer></form></main></div>
<script>addEventListener("load",function(){document.getElementById("ld").classList.add("off")})</script></body></html>
