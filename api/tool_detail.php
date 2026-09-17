<?php
require_once 'config.php';

$tool_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$tool = null;
$stmt = $conn->prepare("
    SELECT t.*, c.category_name, c.slug AS cat_slug
    FROM ai_tools t
    LEFT JOIN categories c ON t.category_id = c.id
    WHERE t.id = ?
    LIMIT 1
");
$stmt->bind_param('i', $tool_id);
$stmt->execute();
$tool = $stmt->get_result()->fetch_assoc();
$stmt->close();

$avg_rating = null;
$review_count = 0;
if ($tool) {
    $stmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE tool_id = ?");
    $stmt->bind_param('i', $tool_id);
    $stmt->execute();
    $rating_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $avg_rating = $rating_row['avg_rating'] ? round((float)$rating_row['avg_rating'], 1) : (float)($tool['rating'] ?? 4.5);
    $review_count = (int)$rating_row['review_count'];
}

$reviews = [];
if ($tool) {
    $stmt = $conn->prepare("
        SELECT r.*, u.username
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.tool_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param('i', $tool_id);
    $stmt->execute();
    $reviews_result = $stmt->get_result();
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt->close();
}

function abbr($name) {
    $clean = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
    $words = preg_split('/\s+/', trim($clean));
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    $s = preg_replace('/[^a-zA-Z0-9]/', '', $name);
    return strtoupper(substr($s, 0, 2));
}

$avatar_colors = ['#1f6f43', '#7c5cd6', '#0e7490', '#15803d', '#4f46e5', '#b45309', '#c2410c', '#0369a1'];
$tool_icon = $tool ? get_tool_icon_url($tool) : '';
?>
<?php include 'header.php'; ?>

<style>
.star-label{font-size:28px;cursor:pointer;color:#d8e0d8;transition:color .2s,transform .2s;user-select:none}
.star-label.on{color:var(--star)}
.star-label:hover{color:var(--star);transform:scale(1.1)}
.star-input{display:none}
</style>

<main class="w" style="padding-top:32px;padding-bottom:64px;">

<?php if ($tool): ?>

  <?php if (isset($_GET['review_success'])): ?>
    <div class="alert alert-s">Review submitted successfully.</div>
  <?php elseif (isset($_GET['review_error'])): ?>
    <div class="alert alert-e">Failed to submit review. You may have already reviewed this tool.</div>
  <?php endif; ?>

  <div class="c sf s1">
    <div class="f g4" style="align-items:flex-start;">
      <div style="text-align:center;">
        <div class="tool-icon-avatar avatar-lg" style="margin:0 auto 8px;background:#fff;box-shadow:0 4px 14px rgba(24,33,27,.07);border:1px solid var(--border);">
          <?php if (!empty($tool_icon)): ?>
          <img src="<?= htmlspecialchars($tool_icon) ?>" alt="<?= htmlspecialchars($tool['tool_name']) ?>" class="tool-icon-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
          <?php endif; ?>
          <div class="avatar avatar-lg" style="background:<?= $avatar_colors[$tool_id % count($avatar_colors)] ?>;width:100%;height:100%;border-radius:12px;<?= !empty($tool_icon) ? 'display:none;' : '' ?>"><?= htmlspecialchars(abbr($tool['tool_name'])) ?></div>
        </div>
        <span class="tag tag-accent"><?= htmlspecialchars($tool['category_name'] ?? 'General') ?></span>
      </div>
      <div style="flex:1;min-width:0;">
        <h1 style="font-size:24px;font-weight:800;margin-bottom:8px;"><?= htmlspecialchars($tool['tool_name']) ?></h1>
        <div class="f g3" style="margin-bottom:16px;flex-wrap:wrap;">
          <span class="tag <?= $tool['pricing'] === 'Free' ? 'tag-green' : ($tool['pricing'] === 'Paid' ? 'tag-accent' : 'tag-amber') ?>"><?= htmlspecialchars($tool['pricing'] ?? 'Freemium') ?></span>
          <span class="st"><?php
            $full = min(5, (int)round($avg_rating));
            echo str_repeat('&#9733;', $full);
          ?></span><span class="st-em"><?php
            echo str_repeat('&#9733;', 5 - $full);
          ?></span>
          <span style="color:var(--star);font-weight:700;font-size:14px;"><?= number_format($avg_rating, 1) ?></span>
          <span class="t-s" style="font-size:13px;">(<?= $review_count ?> review<?= $review_count !== 1 ? 's' : '' ?>)</span>
        </div>
        <p class="t-s" style="font-size:14px;line-height:1.7;margin-bottom:24px;">
          <?= nl2br(htmlspecialchars($tool['description'] ?? '')) ?>
        </p>
        <div class="f g3" style="flex-wrap:wrap;">
          <?php if (!empty($tool['url'])): ?>
          <a href="<?= htmlspecialchars($tool['url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Visit Tool</a>
          <?php endif; ?>
          <?php if (!empty($tool['category_id'])): ?>
          <a href="recommend.php?category_id=<?= (int)$tool['category_id'] ?>" class="btn btn-secondary">Similar Tools</a>
          <?php endif; ?>
          <?php if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin'): ?>
          <a href="admin_dashboard.php?edit=<?= (int)$tool['id'] ?>" class="btn btn-secondary" style="border-color:var(--accent);color:var(--accent);">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:text-top;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Tool
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div style="margin-top:40px;margin-bottom:20px;">
    <h2 class="f g3" style="font-size:18px;font-weight:800;">Community Reviews<?php if ($review_count > 0): ?><span class="t-s" style="font-size:14px;font-weight:400;">(<?= $review_count ?>)</span><?php endif; ?></h2>
  </div>

  <?php if (!empty($reviews)): ?>
    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:32px;">
      <?php foreach ($reviews as $i => $rev):
        $r_full = min(5, (int)round($rev['rating']));
        $initial = strtoupper($rev['username'][0] ?? '?');
        $color = $avatar_colors[$i % count($avatar_colors)];
        $s_class = 's' . min(6, ($i % 6) + 1);
      ?>
      <div class="c sf <?= $s_class ?>">
        <div class="f g4" style="align-items:flex-start;">
          <div class="avatar" style="background:<?= $color ?>"><?= htmlspecialchars($initial) ?></div>
          <div style="flex:1;min-width:0;">
            <div class="f g3" style="flex-wrap:wrap;margin-bottom:4px;">
              <span style="font-weight:600;font-size:14px;"><?= htmlspecialchars($rev['username'] ?? 'Anonymous') ?></span>
              <span style="width:4px;height:4px;border-radius:50%;background:var(--muted);display:inline-block;"></span>
              <span class="t-m" style="font-size:13px;"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
              <span style="margin-left:auto;"><span class="st"><?= str_repeat('&#9733;', $r_full) ?></span><?php if ($r_full < 5): ?><span class="st-em"><?= str_repeat('&#9733;', 5 - $r_full) ?></span><?php endif; ?></span>
            </div>
            <?php if (!empty($rev['review_text'])): ?>
            <p class="t-s" style="font-size:14px;line-height:1.6;"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="c" style="margin-bottom:32px;text-align:center;padding:48px 24px;">
      <h3 style="font-weight:700;font-size:16px;margin-bottom:4px;">No reviews yet</h3>
      <p class="t-m" style="font-size:14px;">Be the first to share your experience with this tool.</p>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['user_id'])): ?>
    <div class="c sf s4">
      <h3 style="font-weight:600;font-size:14px;margin-bottom:20px;">Write a Review</h3>
      <form action="submit_review.php" method="POST">
        <input type="hidden" name="tool_id" value="<?= $tool_id ?>">
        <div style="margin-bottom:16px;">
          <label class="lbl">Rating</label>
          <div style="display:flex;flex-direction:row-reverse;gap:4px;justify-content:flex-end;">
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" name="rating" value="<?= $i ?>" id="sr<?= $i ?>" class="star-input">
            <label for="sr<?= $i ?>" data-val="<?= $i ?>" class="star-label">&#9733;</label>
            <?php endfor; ?>
          </div>
        </div>
        <div style="margin-bottom:16px;">
          <label for="rt" class="lbl">Review</label>
          <textarea id="rt" name="review_text" class="i" rows="4" placeholder="Share your thoughts..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Review</button>
      </form>
    </div>
  <?php else: ?>
    <div class="c" style="text-align:center;">
      <p class="t-m" style="font-size:14px;">
        <a href="login.php" style="color:var(--accent);font-weight:600;">Sign in</a>
        to write a review for this tool.
      </p>
    </div>
  <?php endif; ?>

  <div style="margin-top:40px;">
    <a href="tools.php" class="f g2" style="color:var(--muted);font-size:14px;font-weight:500;">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to Tools
    </a>
  </div>

<?php else: ?>

  <div style="min-height:55vh;display:flex;align-items:center;justify-content:center;">
    <div class="c" style="max-width:480px;width:100%;text-align:center;padding:48px 24px;">
      <h3 style="font-weight:700;font-size:18px;margin-bottom:6px;">Tool not found</h3>
      <p class="t-m" style="font-size:14px;margin-bottom:20px;">The tool you're looking for doesn't exist or has been removed.</p>
      <a href="tools.php" class="btn btn-primary">Browse All Tools</a>
    </div>
  </div>

<?php endif; ?>

</main>

<script>
(function() {
  var labels = document.querySelectorAll('.star-label');
  function paint(val) {
    labels.forEach(function(l) {
      l.classList.toggle('on', parseInt(l.dataset.val) <= val);
    });
  }
  document.querySelectorAll('input[name="rating"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      paint(parseInt(this.value));
    });
  });
  labels.forEach(function(l) {
    l.addEventListener('mouseenter', function() { paint(parseInt(l.dataset.val)); });
    l.addEventListener('mouseleave', function() {
      var r = document.querySelector('input[name="rating"]:checked');
      paint(r ? parseInt(r.value) : 0);
    });
  });
})();
</script>

<?php include 'footer.php'; ?>
