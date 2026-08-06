<?php
require_once 'config.php';
include 'header.php';
?>

<style>
.tc{text-align:center}
.jc{justify-content:center}
.fw{flex-wrap:wrap}
.hero-h1{font-size:clamp(2rem,5vw,3.4rem);font-weight:800;line-height:1.12;letter-spacing:-.03em;color:var(--text)}
.hero-sub{font-size:1.05rem;line-height:1.7;max-width:620px;color:var(--secondary)}
.hero-search{max-width:520px}
.stats-wrap{max-width:560px;margin:48px auto 0}
.stat-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);padding:22px 16px;box-shadow:var(--shadow-sm)}
.stat-num{font-size:1.75rem;font-weight:800;color:var(--accent)}
.stat-lbl{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);font-weight:600}
.mb-20{margin-bottom:20px}
.mt-24{margin-top:24px}
.pt-60{padding-top:60px}
.pt-64{padding-top:72px}
.p-mx{margin:16px auto 0}
.form-mx{margin:32px auto 0}
.hero-tag{color:var(--accent);font-weight:600}
.search-box{display:flex;gap:8px;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);padding:6px;box-shadow:var(--shadow-md)}
.search-box .i{border:none;padding:10px 14px;flex:1;box-shadow:none}
.search-box .i:focus{box-shadow:none}
</style>

<main class="w pt-60">

  <section class="tc pt-64 hero-wrap">

    <?php if ($li): ?>
    <div class="f jc g3 mb-20">
      <span class="t-s">Welcome back, <strong><?= htmlspecialchars($un) ?></strong></span>
      <?php if ($ia): ?>
      <a href="admin_dashboard.php" class="tag tag-accent">Admin</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <h1 class="hero-h1 sf s1">AI Tool Recommendation Portal</h1>

    <p class="hero-sub t-s sf s2 p-mx">Discover, compare, and choose the best AI tools for your workflow. Powered by community reviews.</p>

    <form action="tools.php" method="GET" class="hero-search sf s3 form-mx">
      <div class="search-box">
        <input type="text" name="q" placeholder="Search AI tools..." class="i" autocomplete="off">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </form>

    <div class="f jc fw g3 sf s4 mt-24">
      <?php
      $tags = ['ChatGPT', 'Midjourney', 'GitHub Copilot', 'Claude', 'Canva AI'];
      foreach ($tags as $tag):
      ?>
      <a href="tools.php?q=<?= urlencode($tag) ?>" class="tag tag-accent hero-tag"><?= htmlspecialchars($tag) ?></a>
      <?php endforeach; ?>
    </div>

  </section>

  <?php
  $toolCount = $conn->query("SELECT COUNT(*) as c FROM ai_tools")->fetch_assoc()['c'] ?? 0;
  $catCount  = $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'] ?? 0;
  $userCount = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0;
  ?>

  <div class="g gc3 g4 stats-wrap sf s5">
    <div class="stat-card tc">
      <div class="stat-num"><?= $toolCount ?></div>
      <div class="stat-lbl">AI Tools</div>
    </div>
    <div class="stat-card tc">
      <div class="stat-num"><?= $catCount ?></div>
      <div class="stat-lbl">Categories</div>
    </div>
    <div class="stat-card tc">
      <div class="stat-num"><?= $userCount ?></div>
      <div class="stat-lbl">Users</div>
    </div>
  </div>

</main>

<?php include 'footer.php'; ?>
