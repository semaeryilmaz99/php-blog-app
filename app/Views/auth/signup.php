<?php
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/blog-app/public/assets/css/app.css">
  <title>Signup</title>
</head>
<body class="auth-page">
<div class="auth-card">
  <h1 class="sr-only">Signup</h1>

  <?php if ($errors): ?>
    <ul class="auth-errors">
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form class="auth-form" method="POST" action="/blog-app/public/signup">
    <div class="auth-field">
      <label class="auth-pill" for="username">Username</label>
      <input id="username" class="auth-input" name="username" value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="email">Emaıl Address</label>
      <input id="email" class="auth-input" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="password">Password</label>
      <input id="password" class="auth-input" type="password" name="password">
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="password_again">Enter Password Agaın</label>
      <input id="password_again" class="auth-input" type="password" name="password_again">
    </div>

    <button class="auth-button" type="submit">Sıgn Up</button>
  </form>

  <p class="auth-alt">
    Already have an account? <a href="/blog-app/public/login">Logın</a>
  </p>
</div>

</body>
</html>
