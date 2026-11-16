<?php
require __DIR__ . '/includes/db.php';
if (isset($_GET['logout'])) { session_destroy(); header('Location: login.php'); exit; }
$err = ''; $reg = isset($_GET['register']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) die('Invalid request');
    $email = strtolower(trim($_POST['email'] ?? '')); $pw = $_POST['password'] ?? '';
    if ($reg) {
        $name = trim($_POST['name'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) {
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
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div id="ld"><div class="sp"></div></div>
<div class="lg"><aside class="lg-hero"><div class="brand"><span class="logo">✓</span>TaskFlow</div><h2>Organise your day.<br>Own your goals.</h2><p>A simple, focused workspace for your tasks, deadlines and ideas.</p><div class="pt">📌 Pin and prioritise what matters</div><div class="pt">✅ Checklists and smart filters</div><div class="pt">⏱ Built-in focus timer</div></aside>
<main class="lg-form"><form class="lg-card" method="post" onsubmit="this.querySelector('.btn').classList.add('busy')">
<h1><?= $reg ? 'Create your account' : 'Welcome back 👋' ?></h1>
<p class="sub"><?= $reg ? 'Start organising in under a minute.' : 'Log in to continue to your tasks.' ?></p>
<?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<?php if ($reg): ?><div class="fld"><span class="i">👤</span><input name="name" placeholder="Full name" required></div><?php endif; ?>
<div class="fld"><span class="i">✉️</span><input type="email" name="email" placeholder="Email address" required></div>
<div class="fld"><span class="i">🔒</span><input type="password" name="password" placeholder="Password" required><button type="button" class="eye" onclick="var i=this.previousElementSibling;i.type=i.type==='password'?'text':'password'" aria-label="Show password">👁</button></div>
<button class="btn"><?= $reg ? 'Create account' : 'Log in' ?></button>
<p class="alt"><?= $reg ? 'Already have an account? <a href="login.php">Log in</a>' : 'New here? <a href="?register">Create an account</a>' ?></p>
<footer class="ft">© <?= date("Y") ?> TaskFlow · Built by Kaushiki Halder</footer></form></main></div>
<script>addEventListener("load",function(){document.getElementById("ld").classList.add("off")})</script></body></html>
