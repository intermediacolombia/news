<?php
require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../login/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids']) && !empty($_POST['action'])) {
  $ids = array_map('intval', $_POST['ids']);
  $in  = implode(',', array_fill(0, count($ids), '?'));

  try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    switch ($_POST['action']) {
      case 'delete':
        $stmt = $pdo->prepare("UPDATE blog_posts SET deleted=1 WHERE id IN ($in)");
        break;
      case 'draft':
        $stmt = $pdo->prepare("UPDATE blog_posts SET status='draft' WHERE id IN ($in)");
        break;
      case 'publish':
        $stmt = $pdo->prepare("UPDATE blog_posts SET status='published' WHERE id IN ($in)");
        break;
      default:
        exit;
    }

    $stmt->execute($ids);
    echo 'OK';
  } catch (Throwable $e) {
    http_response_code(500);
    echo $e->getMessage();
  }
}
