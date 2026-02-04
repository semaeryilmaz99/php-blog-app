<?php
$me = (int)($_SESSION['user']['id'] ?? 0);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Users</title>
</head>
<body>

<h1>Users</h1>

<?php foreach (($otherUsers ?? []) as $u): ?>
  <div style="display:flex;gap:12px;align-items:center;margin-bottom:10px;">
    <a href="/blog-app/public/users/show?id=<?= (int)$u['id'] ?>">
      <?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
    </a>

    <form method="POST" action="/blog-app/public/follows/toggle">
      <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
      <button type="submit">Follow / Unfollow</button>
    </form>
  </div>
<?php endforeach; ?>

</body>
</html>
