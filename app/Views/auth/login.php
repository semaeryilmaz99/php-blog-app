<?php
$flash  = $_SESSION['flash'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['flash'], $_SESSION['errors'], $_SESSION['old']);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
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

  <form class="auth-form" method="POST" action="<?= BASE_URL ?>/login">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

    <div class="auth-field">
      <label class="auth-pill" for="identity">Username / Email</label>
      <input
        id="identity"
        class="auth-input"
        type="text"
        name="identity"
        value="<?= htmlspecialchars($old['identity'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        autocomplete="username"
        required>
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="password">Password</label>
      <input
        id="password"
        class="auth-input"
        type="password"
        name="password"
        autocomplete="current-password"
        required>
    </div>

    <button class="auth-button" type="submit">Login</button>
  </form>

  <p class="auth-alt">
    Need an account? <a href="<?= BASE_URL ?>/signup">Sign up</a>
  </p>
</div>
</body>
</html>