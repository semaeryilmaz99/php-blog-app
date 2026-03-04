<?php
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <title>Sign Up</title>
</head>
<body class="auth-page">
<div class="auth-card">
  <h1 class="sr-only">Sign Up</h1>

  <?php if ($errors): ?>
    <ul class="auth-errors">
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form class="auth-form" method="POST" action="<?= BASE_URL ?>/signup">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

    <div class="auth-field">
      <label class="auth-pill" for="username">Username</label>
      <input
        id="username"
        class="auth-input"
        type="text"
        name="username"
        value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        autocomplete="username"
        required>
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="email">Email Address</label>
      <input
        id="email"
        class="auth-input"
        type="email"
        name="email"
        value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        autocomplete="email"
        required>
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="password">Password <span>(min. 8 karakter)</span></label>
      <input
        id="password"
        class="auth-input"
        type="password"
        name="password"
        autocomplete="new-password"
        required>
    </div>

    <div class="auth-field">
      <label class="auth-pill" for="password_again">Enter Password Again</label>
      <input
        id="password_again"
        class="auth-input"
        type="password"
        name="password_again"
        autocomplete="new-password"
        required>
    </div>

    <button class="auth-button" type="submit">Sign Up</button>
  </form>

  <p class="auth-alt">
    Already have an account? <a href="<?= BASE_URL ?>/login">Login</a>
  </p>
</div>
</body>
</html>