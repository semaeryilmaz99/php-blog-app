<?php
$flash  = $_SESSION['flash'] ?? null;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['flash'], $_SESSION['errors']);

$profile        = $profile        ?? [];
$posts          = $posts          ?? [];
$isFollowing    = $isFollowing    ?? false;
$followersCount = $followersCount ?? 0;
$followingCount = $followingCount ?? 0;
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($profile['username'] ?? 'Kullanıcı', ENT_QUOTES, 'UTF-8') ?></title>
  <script src="<?= BASE_URL ?>/assets/js/dashboard.js" defer></script>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body class="dashboard">

  <?php if ($flash): ?>
    <div class="flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar" aria-hidden="true">
    <nav class="sidebar__menu" aria-label="Sidebar menu">
      <a class="sidebar__item" href="<?= BASE_URL ?>/dashboard">dashboard</a>
      <a class="sidebar__item" href="<?= BASE_URL ?>/dashboard">feed</a>
      <a class="sidebar__item" href="<?= BASE_URL ?>/posts/create">create post</a>
      <a class="sidebar__item" href="<?= BASE_URL ?>/userpage">user page</a>
    </nav>
    <form class="sidebar__logout" method="POST" action="<?= BASE_URL ?>/logout">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit">logout</button>
    </form>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay" hidden></div>

  <!-- TOPBAR -->
  <header class="topbar">
    <button class="topbar__hamburger" id="hamburger" type="button"
            aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <nav class="topbar__nav">
      <a class="topbar__btn" href="<?= BASE_URL ?>/dashboard">dashboard</a>
      <a class="topbar__btn" href="<?= BASE_URL ?>/feed">feed</a>
    </nav>

    <?php
    $myAvatar   = $_SESSION['user']['avatar_path'] ?? null;
    $myUsername = $_SESSION['user']['username'] ?? 'User';
    ?>
    <a class="topbar__user" href="<?= BASE_URL ?>/userpage" aria-label="User page">
      <?php if (!empty($myAvatar)): ?>
        <img class="topbar__avatar"
          src="<?= htmlspecialchars($myAvatar, ENT_QUOTES, 'UTF-8') ?>"
          alt="<?= htmlspecialchars($myUsername, ENT_QUOTES, 'UTF-8') ?>">
      <?php else: ?>
        <div class="topbar__avatar-fallback">
          <?= htmlspecialchars(mb_strtoupper(mb_substr($myUsername, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
    </a>
  </header>

  <!-- CONTENT -->
  <main class="content">

    <!-- Profil header -->
    <div class="user-profile">
      <div class="user-profile__avatar">
        <?php if (!empty($profile['avatar_path'])): ?>
          <img src="<?= htmlspecialchars($profile['avatar_path'], ENT_QUOTES, 'UTF-8') ?>"
               alt="<?= htmlspecialchars($profile['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php else: ?>
          <span><?= htmlspecialchars(mb_strtoupper(mb_substr($profile['username'] ?? '?', 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
      </div>

      <div class="user-profile__info">
        <h1 class="user-profile__username">
          <?= htmlspecialchars($profile['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <?php if (!empty($profile['bio'])): ?>
          <p class="user-profile__bio">
            <?= htmlspecialchars($profile['bio'], ENT_QUOTES, 'UTF-8') ?>
          </p>
        <?php endif; ?>

        <div class="user-profile__stats">
          <div class="user-profile__stat">
            <strong><?= (int) $followersCount ?></strong>
            <span>takipçi</span>
          </div>
          <div class="user-profile__stat">
            <strong><?= (int) $followingCount ?></strong>
            <span>takip</span>
          </div>
          <div class="user-profile__stat">
            <strong><?= count($posts) ?></strong>
            <span>post</span>
          </div>
        </div>

        <?php if ((int) ($profile['id'] ?? 0) !== (int) $_SESSION['user']['id']): ?>
          <form method="POST" action="<?= BASE_URL ?>/follows/toggle">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="user_id" value="<?= (int) ($profile['id'] ?? 0) ?>">
            <button class="user-profile__follow-btn <?= $isFollowing ? 'is-following' : '' ?>"
                    type="submit">
              <?= $isFollowing ? 'unfollow' : 'follow' ?>
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <hr class="user-profile__divider">

    <!-- Postlar -->
    <?php if (empty($posts)): ?>
      <p class="empty-state">Henüz post yok.</p>
    <?php else: ?>
      <?php $viewerId = (int) ($_SESSION['user']['id'] ?? 0); ?>
      <div class="posts-grid">
        <?php foreach ($posts as $p): ?>
          <article class="post-card">

            <?php if (!empty($p['image_path'])): ?>
  <?php if (($p['media_type'] ?? 'image') === 'video'): ?>
    <video
      class="post-card__video"
      src="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>"
      muted
      loop
      playsinline
      preload="none">
    </video>
  <?php else: ?>
    <img class="post-card__img"
      src="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>"
      alt="<?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
<?php else: ?>
  <div class="post-card__placeholder"></div>
<?php endif; ?>

            <div class="post-card__overlay"></div>

            <div class="post-card__info">
              <div class="post-card__title">
                <?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div class="post-card__meta">
                <span class="post-card__author-date">
                  <?= date('d.m.Y', strtotime($p['created_at'])) ?>
                </span>
              </div>
            </div>

          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>

</body>
</html>