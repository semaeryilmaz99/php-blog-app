<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$posts = $posts ?? [];
$q = trim($_GET['q'] ?? '');
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Feed</title>
    <link rel="stylesheet" href="/blog-app/public/assets/css/app.css">
</head>

<body class="dashboard feed">

    <?php if ($flash): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
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
            <a class="topbar__btn" href="/blog-app/public/feed">feed</a>

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

    <main class="content">

        <p style="text-align: center;">Takip ettiğin kullanıcıların paylaşımlarını gör</p>
        <hr>

        <?php if (empty($posts)): ?>
            <p class="empty-state">
                Henüz takip ettiğin kullanıcılardan post yok.
            </p>
        <?php endif; ?>

        <div class="posts-grid">
            <?php foreach ($posts as $p): ?>
                <article class="post-card">

                    <!-- Header -->
                    <div class="post-card__header">
                        <div class="post-card__title">
                            <?= htmlspecialchars($p['title']) ?>
                        </div>

                        <div class="post-card__actions">

                            <!-- Edit / Delete sadece post sahibiyse -->
                            <?php if ((int)$p['user_id'] === (int)$_SESSION['user']['id']): ?>
                                <a href="/blog-app/public/posts/edit?id=<?= (int)$p['id'] ?>">✏️</a>

                                <form method="POST" action="/blog-app/public/posts/delete"
                                    onsubmit="return confirm('Silinsin mi?')">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button>🗑</button>
                                </form>
                            <?php endif; ?>

                            <!-- Like -->
                            <form method="POST" action="/blog-app/public/likes/toggle">
                                <input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>">
                                <button>
                                    <?= !empty($p['is_liked']) ? '❤️' : '🤍' ?>
                                    (<?= (int)$p['like_count'] ?>)
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