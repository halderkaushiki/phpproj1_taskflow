<?php
session_start();
$db = new PDO('sqlite:' . __DIR__ . '/../data.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT UNIQUE NOT NULL,pass TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS tasks(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,title TEXT NOT NULL,notes TEXT DEFAULT '',priority TEXT DEFAULT 'medium',category TEXT DEFAULT '',due TEXT,done INTEGER DEFAULT 0,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
foreach (["pinned INTEGER DEFAULT 0", "subs TEXT DEFAULT '[]'"] as $c) { try { $db->exec("ALTER TABLE tasks ADD COLUMN $c"); } catch (PDOException $e) {} }
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
