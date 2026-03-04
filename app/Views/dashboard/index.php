<?php
$viewerId = (int) ($_SESSION['user']['id'] ?? 0);
$flash    = $_SESSION['flash'] ?? null;
$errors   = $_SESSION['errors'] ?? [];
unset($_SESSION['flash'], $_SESSION['errors']);

$posts = $posts ?? [];
$q     = $q ?? '';
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>
  <script src="<?= BASE_URL ?>/assets/js/dashboard.js" defer></script>
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
    <button class="topbar__hamburger" id="hamburger" type="button" aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <nav class="topbar__nav">
      <a class="topbar__btn is-active" href="<?= BASE_URL ?>/dashboard">dashboard</a>
      <a class="topbar__btn" href="<?= BASE_URL ?>/feed">feed</a>
      <button class="topbar__btn" id="usersModalBtn" type="button">users</button>
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

  <!-- SEARCH BAR -->
  <section class="searchbar">
    <form class="searchbar__form" method="GET" action="<?= BASE_URL ?>/dashboard">
      <div class="searchbar__pill">
        <input
          class="searchbar__input"
          type="text"
          name="q"
          value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
          placeholder=""
          aria-label="Search" />
        <button class="searchbar__btn" type="submit" aria-label="Search"></button>
      </div>
      <?php if ($q): ?>
        <a href="<?= BASE_URL ?>/dashboard" class="searchbar__clear">✕ Temizle</a>
      <?php endif; ?>
    </form>
  </section>

  <!-- POSTS -->
  <main class="content">
    <p class="content__desc">Tüm kullanıcıların paylaşımlarını gör</p>
    <hr>

    <?php if (empty($posts)): ?>
      <p class="empty-state">Henüz hiç post yok.</p>
    <?php else: ?>
      <div class="posts-grid">
        <?php $viewerId = (int) ($_SESSION['user']['id'] ?? 0); ?>
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

  <?php if ((int) $p['user_id'] === (int) $_SESSION['user']['id']): ?>
    <div class="post-card__actions">
      <form method="POST" action="<?= BASE_URL ?>/posts/delete"
        onsubmit="return confirm('Silinsin mi?')">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
        <button class="icon-btn" type="submit" aria-label="Delete"></button>
      </form>
      <a class="icon-btn" href="<?= BASE_URL ?>/posts/edit?id=<?= (int) $p['id'] ?>" aria-label="Edit"></a>
    </div>
  <?php endif; ?>

  <div class="post-card__info">
    <div class="post-card__title">
      <?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>
    </div>

    <div class="post-card__meta">
      <?php
      $profileUrl = ((int) $p['user_id'] === $viewerId)
      ? BASE_URL . '/userpage'
      : BASE_URL . '/users/show?id=' . (int) $p['user_id'];
      ?>
      <a class="post-card__author-date"
      href="<?= BASE_URL ?>/users/show?id=<?= (int) $p['user_id'] ?>">
      @<?= htmlspecialchars($p['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
      · <?= date('d.m.Y', strtotime($p['created_at'])) ?>
      </a>

      <form method="POST" action="<?= BASE_URL ?>/likes/toggle">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="post_id" value="<?= (int) $p['id'] ?>">
        <?php
        $isLiked   = !empty($p['is_liked']);
        $likeCount = (int) ($p['like_count'] ?? 0);
        ?>
        <button class="post-card__like <?= $isLiked ? 'is-liked' : '' ?>"
          type="submit" aria-label="Like">
          <?= $isLiked ? '♥' : '♡' ?> <?= $likeCount ?>
        </button>
      </form>
    </div>
  </div>

</article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <a class="fab" href="<?= BASE_URL ?>/posts/create" aria-label="Add post">+</a>

  <!-- USERS MODAL -->
<div class="users-modal" id="usersModal" aria-modal="true" role="dialog" aria-label="Users" hidden>
  <div class="users-modal__panel">
    <div class="users-modal__header">
      <h2 class="users-modal__title">users</h2>
      <button class="users-modal__close" id="usersModalClose" type="button" aria-label="Close">✕</button>
    </div>

    <div class="users-modal__list">
      <?php if (empty($otherUsers)): ?>
        <p class="users-modal__empty">Başka kullanıcı yok.</p>
      <?php else: ?>
        <?php foreach ($otherUsers as $u): ?>
          <div class="users-modal__item">
            <a class="users-modal__user" href="<?= BASE_URL ?>/users/show?id=<?= (int) $u['id'] ?>">
              <div class="users-modal__avatar">
                <?php if (!empty($u['avatar_path'])): ?>
                  <img src="<?= htmlspecialchars($u['avatar_path'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                  <span><?= htmlspecialchars(mb_strtoupper(mb_substr($u['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <span class="users-modal__username">
                <?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
              </span>
            </a>

            <form method="POST" action="<?= BASE_URL ?>/follows/toggle">
              <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
              <button class="users-modal__follow-btn <?= !empty($u['is_following']) ? 'is-following' : '' ?>"
                      type="submit">
                <?= !empty($u['is_following']) ? 'unfollow' : 'follow' ?>
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="users-modal__backdrop" id="usersModalBackdrop"></div>

</body>
</html>