<?php
$flash  = $_SESSION['flash'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['flash'], $_SESSION['errors'], $_SESSION['old']);

// Edit modu mu, create modu mu?
$isEdit  = isset($mode) && $mode === 'edit';
$post    = $post ?? [];

$title   = $old['title']   ?? ($post['title']   ?? '');
$content = $old['content'] ?? ($post['content'] ?? '');
$postId  = (int) ($post['id'] ?? 0);

$formAction = $isEdit
    ? BASE_URL . '/posts/update?id=' . $postId
    : BASE_URL . '/posts/store';

$pageTitle  = $isEdit ? 'Edit Post' : 'Create Post';
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/app.css') ?>">
</head>
<body class="post-create-page">

  <div class="post-create-card">

    <h1 class="sr-only"><?= $pageTitle ?></h1>

    <?php if ($flash): ?>
      <div class="post-create-message">
        <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <ul class="post-create-errors">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <form class="post-create-form"
          method="POST"
          action="<?= $formAction ?>"
          enctype="multipart/form-data">

      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

      <!-- Başlık -->
      <div class="post-create-field">
        <input
          class="post-create-input"
          type="text"
          name="title"
          value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
          placeholder="title"
          required>
      </div>

      <!-- İçerik -->
      <div class="post-create-field">
        <textarea
          class="post-create-textarea"
          name="content"
          placeholder="text"
          required><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

      <!-- Görsel -->
      <div class="post-create-field">
        <input
  type="file"
  name="media"
  id="postMedia"
  class="post-create-file-input"
  accept="image/png,image/jpeg,image/webp,video/mp4">
<label class="post-create-file-label" for="postMedia">
  <span class="post-create-file-icon"></span>
  <span class="post-create-file-text" id="postMediaLabel">
    <?= $isEdit ? 'Change image / video (optional)' : 'Add image / video (optional)' ?>
  </span>
</label>
      </div>

      <?php if ($isEdit && !empty($post['image_path'])): ?>
        <div class="post-create-current-image">
          <img
            src="<?= htmlspecialchars($post['image_path'], ENT_QUOTES, 'UTF-8') ?>"
            alt="Current image"
            style="max-width: 200px; border-radius: 8px;">
          <p>Mevcut görsel — yeni seçersen değişir</p>
        </div>
      <?php endif; ?>

      <button class="post-create-submit" type="submit">
        <?= $isEdit ? 'Update' : 'Publish' ?>
      </button>

    </form>

    <a class="post-create-back" href="<?= BASE_URL ?>/dashboard">← back</a>

  </div>

  <script>
document.getElementById('postMedia').addEventListener('change', function() {
  const label = document.getElementById('postMediaLabel');
  label.textContent = this.files[0] ? this.files[0].name : 'Add image / video (optional)';
});
</script>
</body>
</html>