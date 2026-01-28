<?php
$flash = $_SESSION['flash'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['flash'], $_SESSION['errors'], $_SESSION['old']);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/blog-app/public/assets/css/app.css">
  <title>Login</title>
</head>
<body class="auth-page">
<div class="auth-card">
  <h1 class="sr-only">Login</h1>

  <?php if ($flash): ?>
    <div class="auth-message"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <ul class="auth-errors">
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form class="auth-form" method="POST" action="/blog-app/public/login">
    <div class="auth-field">
      <label class="auth-pill" for="identity">Username/Emaıl</label>
      <input id="identity" class="auth-input" name="identity" value="<?= htmlspecialchars($old['identity'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="password">Password</label>
      <input id="password" class="auth-input" type="password" name="password">
    </div>

    <button class="auth-button" type="submit">Logın</button>
  </form>

  <p class="auth-alt">
    Need an account? <a href="/blog-app/public/signup">Sıgn up</a>
  </p>
</div>
</body>
</html>
