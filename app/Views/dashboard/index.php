<?php
$viewerId = (int)($_SESSION['user']['id'] ?? 0);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

// Controller’dan gelmese bile hata vermesin diye:
$posts = $posts ?? [];

// Search için
$q = trim($_GET['q'] ?? '');
?>
<!doctype html>
<html lang="tr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>
  <script src="/blog-app/public/assets/js/sidebar.js" defer></script>
  <link rel="stylesheet" href="/blog-app/public/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?>">
</head>

<body class="dashboard">

  <?php if ($flash): ?>
    <div class="flash">
      <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- SIDEBAR (iskelet) -->
  <aside class="sidebar" id="sidebar" aria-hidden="true">
    <nav class="sidebar__menu" aria-label="Sidebar menu">
      <a class="sidebar__item" href="/blog-app/public/dashboard">dashboard</a>
      <a class="sidebar__item" href="/blog-app/public/posts/create">create post</a>
      <a class="sidebar__item" href="/blog-app/public/userpage">user page</a>
    </nav>

    <form class="sidebar__logout" method="POST" action="/blog-app/public/logout">
      <button type="submit">logout</button>
    </form>
  </aside>

  <!-- (opsiyonel) Arka plan overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay" hidden></div>

  <!-- 1) TOP BAR / NAVBAR -->
  <header class="topbar">
    <button class="topbar__hamburger" id="hamburger" type="button" aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>


    <nav class="topbar__nav">
      <a class="topbar__btn is-active" href="/blog-app/public/dashboard">dashboard</a>
      <a class="topbar__btn" href="/blog-app/public/posts/create">create post</a>
      <a class="topbar__btn" href="#" aria-disabled="true" onclick="return false;">&nbsp;</a>
    </nav>

    <?php
    $avatar = $_SESSION['user']['avatar_path'] ?? null;
    $username = $_SESSION['user']['username'] ?? 'User';
    ?>

    <a class="topbar__user" href="/blog-app/public/userpage" aria-label="User page">
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

  <!-- 2) SEARCH BAR -->
  <section class="searchbar">
    <form class="searchbar__form" method="GET" action="/blog-app/public/dashboard">
      <div class="searchbar__pill">
        <input
          class="searchbar__input"
          type="text"
          name="q"
          value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>"
          placeholder=""
          aria-label="Search" />

        <button class="searchbar__btn" type="submit" aria-label="Search">
          <!-- basit icon: istersen svg koyarız -->
          🔍
        </button>
      </div>
    </form>
  </section>

  <!-- 3) POSTS GRID -->
  <main class="content">
    <div class="posts-grid">
      <?php foreach ($posts as $p): ?>
        <article class="post-card">

          <!-- Kart header: pill başlık -->
          <div class="post-card__header">
            <div class="post-card__title">
              <?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>
            </div>

            <!-- Sağdaki action ikonları -->
            <div class="post-card__actions">

              <?php if ((int)$p['user_id'] === $viewerId): ?>
                <!-- ✅ Delete: sadece post sahibi -->
                <form method="POST"
                  action="/blog-app/public/posts/delete"
                  onsubmit="return confirm('Silinsin mi?')">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="icon-btn" type="submit" aria-label="Delete">
                    🗑
                  </button>
                </form>

                <!-- ✅ Edit: sadece post sahibi -->
                <a class="icon-btn"
                  href="/blog-app/public/posts/edit?id=<?= (int)$p['id'] ?>"
                  aria-label="Edit">
                  ✏️
                </a>
              <?php endif; ?>

              <!-- ❤️ Like: herkes görebilir -->
              <form method="POST"
                action="/blog-app/public/likes/toggle">
                <input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>">

                <?php
                $isLiked   = !empty($p['is_liked']);
                $likeCount = (int) ($p['like_count'] ?? 0);
                ?>

                <button class="icon-btn" type="submit" aria-label="Like">
                  <?= $isLiked ? '❤️' : '🤍' ?>
                  <span>(<?= $likeCount ?>)</span>
                </button>
              </form>

            </div>

          </div>

          <!-- Kart görsel alanı -->
          <div class="post-card__media">
            <?php if (!empty($p['image_path'])): ?>
              <img
                class="post-card__img"
                src="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                alt="">
            <?php else: ?>
              <div class="post-card__placeholder"></div>
            <?php endif; ?>
          </div>

        </article>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- 4) FAB: + butonu -->
  <a class="fab" href="/blog-app/public/posts/create" aria-label="Add post">+</a>

</body>

</html>