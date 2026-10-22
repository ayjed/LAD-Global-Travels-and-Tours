<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = App\Core\Database::getConnection();
$rows = $pdo->query("SELECT id, title, slug, is_featured, created_at FROM packages WHERE status='published' ORDER BY created_at DESC")->fetchAll();
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . $r['title'] . ' | is_featured: ' . $r['is_featured'] . ' | created: ' . $r['created_at'] . PHP_EOL;
}
