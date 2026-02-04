<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>

<h1><?= htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') ?></h1>

<p>
  Followers: <?= (int)$followersCount ?> |
  Following: <?= (int)$followingCount ?>
</p>

<?php if ((int)$profile['id'] !== (int)$_SESSION['user']['id']): ?>
  <form method="POST" action="/blog-app/public/follows/toggle">
    <input type="hidden" name="user_id" value="<?= (int)$profile['id'] ?>">
    <button type="submit">
      <?= $isFollowing ? 'Following' : 'Follow' ?>
    </button>
  </form>
<?php endif; ?>

<h2>Posts</h2>

<?php foreach (($posts ?? []) as $p): ?>
  <article style="margin-bottom:16px;">
    <h3><?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?></h3>
    <?php if (!empty($p['image_path'])): ?>
      <img src="<?= htmlspecialchars($p['image_path'], ENT_QUOTES, 'UTF-8') ?>" style="max-width:300px;">
    <?php endif; ?>
  </article>
<?php endforeach; ?>

</body>
</html>
