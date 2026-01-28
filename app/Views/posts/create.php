<?php
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

// If coming from edit, $mode and $post will be set.
$mode = $mode ?? 'create';
$post = $post ?? null;

// Field values: prefer old input, then post, else empty.
$titleValue = $old['title'] ?? ($post['title'] ?? '');
$contentValue = $old['content'] ?? ($post['content'] ?? '');

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="tr">

<head>
  <meta charset="utf-8" />
  <title>Create Post</title>
  <link rel="stylesheet" href="/blog-app/public/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?>">
</head>

<body class="post-create-page">

  <main class="post-create-card">
    <?php if ($flash): ?>
      <p class="post-create-message"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
      <ul class="post-create-errors">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <!-- enctype required: otherwise image won't arrive -->
    <form class="post-create-form" method="POST" action="<?= $mode === 'edit'
                                                          ? '/blog-app/public/posts/update?id=' . (int)$post['id']
                                                          : '/blog-app/public/posts/store'
                                                        ?>" enctype="multipart/form-data">

      <!-- Select an image -->
      <div class="post-create-field">
        <label class="post-create-file-label" for="post-image">
          <span>Select an image</span>
          <span class="post-create-file-icon" aria-hidden="true"></span>
        </label>
        <input class="post-create-file-input" id="post-image" type="file" name="image" accept="image/png,image/jpeg,image/webp">
      </div>

      <!-- header of the post -->
      <div class="post-create-field">
        <input class="post-create-input" name="title" placeholder="header of the post"
          value="<?= htmlspecialchars($titleValue, ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <!-- text of the post -->
      <div class="post-create-field">
        <textarea class="post-create-textarea" name="content" rows="8" placeholder="text of the post"><?= htmlspecialchars($contentValue, ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <!-- add post -->
      <button class="post-create-submit" type="submit">
        <?= $mode === 'edit' ? 'update post' : 'add post' ?>
      </button>

    </form>
  </main>

</body>

</html>
