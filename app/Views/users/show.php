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
  <script src="<?= BASE_URL ?>/assets/js/sidebar.js" defer></script>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?>">
</head>
<body class="dashboard">

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

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar" aria-hidden="true">
    <nav class="sidebar__menu" aria-label="Sidebar menu">
      <a class="sidebar__item" href="<?= BASE_URL ?>/dashboard">dashboard</a>
      <a class="sidebar__item" href="<?= BASE_URL ?>/posts/create">create post</a>
      <a class="sidebar__item" href="<?= BASE_URL ?>/userpage">user page</a>
    </nav>
    <form class="sidebar__logout" method="POST" action="<?= BASE_URL ?>/logout">
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
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
    $avatar   = $_SESSION['user']['avatar_path'] ?? null;
    $username = $_SESSION['user']['username'] ?? 'User';
    ?>
    <a class="topbar__user" href="<?= BASE_URL ?>/userpage" aria-label="User page">
      <?php if (!empty($avatar)): ?>
        <img class="topbar__avatar"
          src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>"
          alt="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
      <?php else: ?>
        <div class="topbar__avatar-fallback">
          <?= htmlspecialchars(mb_strtoupper(mb_substr($username, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>
    </a>
  </header>

  <!-- CONTENT -->
  <main class="content">

    <!-- Profil başlık -->
    <div class="profile-header">
      <div class="profile-header__avatar">
        <?php if (!empty($profile['avatar_path'])): ?>
          <img src="<?= htmlspecialchars($profile['avatar_path'], ENT_QUOTES, 'UTF-8') ?>"
               alt="<?= htmlspecialchars($profile['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php else: ?>
          <div class="profile-header__avatar-fallback">
            <?= htmlspecialchars(mb_strtoupper(mb_substr($profile['username'] ?? '?', 0, 1)), ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="profile-header__info">
        <h1 class="profile-header__username">
          <?= htmlspecialchars($profile['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <?php if (!empty($profile['bio'])): ?>
          <p class="profile-header__bio">
            <?= htmlspecialchars($profile['bio'], ENT_QUOTES, 'UTF-8') ?>
          </p>
        <?php endif; ?>

        <div class="profile-header__stats">
          <span><strong><?= (int) $followersCount ?></strong> takipçi</span>
          <span><strong><?= (int) $followingCount ?></strong> takip</span>
          <span><strong><?= count($posts) ?></strong> post</span>
        </div>

        <!-- Follow / Unfollow -->
        <?php if ((int) ($profile['id'] ?? 0) !== (int) $_SESSION['user']['id']): ?>
          <form method="POST" action="<?= BASE_URL ?>/follows/toggle">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="user_id" value="<?= (int) ($profile['id'] ?? 0) ?>">
            <button class="profile-header__follow-btn <?= $isFollowing ? 'is-following' : '' ?>"
                    type="submit">
              <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <hr>

    <!-- Postlar -->
    <h2 class="content__desc">Posts</h2>

    <?php if (empty($posts)): ?>
      <p class="empty-state">Henüz post yok.</p>
    <?php else: ?>
      <div class="posts-grid">
        <?php foreach ($posts as $p): ?>
          <article class="post-card">
            <div class="post-card__header">
              <div class="post-card__title">
                <?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>
              </div>
            </div>

            <div class="post-card__media">
              <?php if (!empty($p['image_path'])): ?>
                <img class="post-card__img"
                  src="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                  alt="<?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>">
              <?php else: ?>
                <div class="post-card__placeholder"></div>
              <?php endif; ?>
            </div>

            <div class="post-card__date">
              <?= htmlspecialchars(date('d.m.Y', strtotime($p['created_at'])), ENT_QUOTES, 'UTF-8') ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>

</body>
</html>