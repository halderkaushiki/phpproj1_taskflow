<?php require __DIR__ . '/includes/db.php'; if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; } ?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div id="ld"><div class="sp"></div></div><div id="app"></div>
<script>window.APP={name:<?= json_encode($_SESSION['name'], JSON_HEX_TAG) ?>,csrf:<?= json_encode($_SESSION['csrf']) ?>};</script>
<script src="assets/app.js"></script></body></html>
