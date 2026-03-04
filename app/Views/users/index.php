<?php
$flash  = $_SESSION['flash'] ?? null;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['flash'], $_SESSION['errors']);

$otherUsers = $otherUsers ?? [];
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Users</title>
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
    <h2 class="content__desc">Users</h2>
    <hr>

    <?php if (empty($otherUsers)): ?>
      <p class="empty-state">Başka kullanıcı yok.</p>
    <?php else: ?>
      <div class="users-grid">
        <?php foreach ($otherUsers as $u): ?>
          <div class="user-card">
            <!-- Avatar -->
            <a class="user-card__avatar" href="<?= BASE_URL ?>/users/show?id=<?= (int) $u['id'] ?>">
              <?php if (!empty($u['avatar_path'])): ?>
                <img src="<?= htmlspecialchars($u['avatar_path'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>">
              <?php else: ?>
                <div class="user-card__avatar-fallback">
                  <?= htmlspecialchars(mb_strtoupper(mb_substr($u['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
              <?php endif; ?>
            </a>

            <!-- Username -->
            <a class="user-card__name"
               href="<?= BASE_URL ?>/users/show?id=<?= (int) $u['id'] ?>">
              <?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
            </a>

            <!-- Follow / Unfollow -->
            <form method="POST" action="<?= BASE_URL ?>/follows/toggle">
              <input type="hidden" name="csrf_token"
                     value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
              <button class="user-card__btn <?= !empty($u['is_following']) ? 'is-following' : '' ?>"
                      type="submit">
                <?= !empty($u['is_following']) ? 'Unfollow' : 'Follow' ?>
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <a class="fab" href="<?= BASE_URL ?>/posts/create" aria-label="Add post">+</a>

</body>
</html>