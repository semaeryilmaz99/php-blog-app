<?php
$flash  = $flash ?? null;
$errors = $errors ?? [];
$old    = $old ?? [];
$user   = $user ?? [];
$posts  = $posts ?? [];

$username = $old['username'] ?? ($user['username'] ?? '');
$bio      = $old['bio']      ?? ($user['bio']      ?? '');
$avatar   = $user['avatar_path'] ?? null;
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Page</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?>">
</head>
<body class="userpage">

  <?php if ($flash): ?>
    <div class="flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <div class="userpage-layout">

    <!-- SOL: PROFILE CARD -->
    <section class="profile-card">
      <div class="profile-card__header">
        <h2 class="profile-card__title">Profile</h2>
      </div>

      <!-- Avatar -->
      <div class="profile-card__avatar">
        <?php if (!empty($avatar)): ?>
          <img src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>"
               alt="Avatar" class="profile-card__avatar-img">
        <?php else: ?>
          <div class="profile-card__avatar-placeholder" aria-label="No avatar">
            <?= htmlspecialchars(mb_strtoupper(mb_substr($username, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Avatar güncelleme -->
      <form class="profile-card__avatar-form" method="POST"
            action="<?= BASE_URL ?>/userpage/update-avatar"
            enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <label class="profile-card__label">Change avatar</label>
        <input class="profile-card__file" type="file" name="avatar"
               accept="image/png,image/jpeg,image/webp">
        <button class="profile-card__btn" type="submit">Update avatar</button>
      </form>

      <hr class="profile-card__divider">

      <!-- Profil güncelleme -->
      <form class="profile-card__form" method="POST"
            action="<?= BASE_URL ?>/userpage/update-profile">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <label class="profile-card__label">Username</label>
        <input class="profile-card__input" type="text" name="username"
               value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
               placeholder="username" required>

        <label class="profile-card__label">Bio</label>
        <textarea class="profile-card__textarea" name="bio" rows="5"
                  placeholder="bio"><?= htmlspecialchars($bio, ENT_QUOTES, 'UTF-8') ?></textarea>

        <button class="profile-card__btn" type="submit">Save profile</button>
      </form>

      <div class="profile-card__meta">
        <div><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    </section>

    <!-- SAĞ: USER LIBRARY -->
    <section class="library-card">
      <div class="library-card__header">
        <h2 class="library-card__title">User Library</h2>
        <a class="library-card__create" href="<?= BASE_URL ?>/posts/create">+ Create Post</a>
      </div>

      <?php if (empty($posts)): ?>
        <p class="library-card__empty">Henüz post yok.</p>
      <?php else: ?>
        <div class="library-grid">
          <?php foreach ($posts as $p): ?>
            <article class="library-item">
              <div class="library-item__top">
                <div class="library-item__title">
                  <?= htmlspecialchars($p['title'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="library-item__actions">
                  <a class="library-item__edit"
                     href="<?= BASE_URL ?>/posts/edit?id=<?= (int) $p['id'] ?>"
                     aria-label="Edit">✏️</a>

                  <form method="POST" action="<?= BASE_URL ?>/userpage/delete-post"
                    onsubmit="return confirm('Post silinsin mi?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="post_id" value="<?= (int) ($p['id'] ?? 0) ?>">
                    <button class="library-item__delete" type="submit" aria-label="Delete"></button>
                  </form>
                </div>
              </div>

              <div class="library-item__media">
                <?php if (!empty($p['image_path'])): ?>
                  <img class="library-item__img"
                    src="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($p['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                  <div class="library-item__placeholder"></div>
                <?php endif; ?>
              </div>

              <div class="library-item__date">
                <?= htmlspecialchars(date('d.m.Y', strtotime($p['created_at'])), ENT_QUOTES, 'UTF-8') ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </div>
</body>
</html>