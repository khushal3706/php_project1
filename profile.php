<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = (int) $_POST['delete_review'];
    $stmt = $conn->prepare('SELECT tool_id FROM reviews WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $review_id, $user_id);
    $stmt->execute();
    $del_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($del_row) {
        $stmt = $conn->prepare('DELETE FROM reviews WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $review_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $tool_id = (int) $del_row['tool_id'];
        $stmt = $conn->prepare(
            'UPDATE ai_tools SET rating = IFNULL((SELECT ROUND(AVG(rating),1) FROM reviews WHERE tool_id = ?), 4.5) WHERE id = ?'
        );
        $stmt->bind_param('ii', $tool_id, $tool_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: profile.php');
    exit;
}

$stmt = $conn->prepare('SELECT username, email, role, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM reviews WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$review_count = (int) $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

$tool_count = (int) $conn->query('SELECT COUNT(*) AS cnt FROM ai_tools')->fetch_assoc()['cnt'];
$member_since = date('F j, Y', strtotime($user['created_at']));

$stmt = $conn->prepare('
    SELECT r.id, r.rating, r.review_text, r.created_at, t.tool_name
    FROM reviews r
    JOIN ai_tools t ON r.tool_id = t.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$reviews = $stmt->get_result();
$stmt->close();

$has_reviews = $reviews && $reviews->num_rows > 0;
$initial = strtoupper($user['username'][0]);
?>
<?php include 'header.php'; ?>

<main class="w" style="padding-top:32px;padding-bottom:64px;">

  <div class="c sf s1">
    <div class="f g4" style="align-items:flex-start;">
      <div class="avatar avatar-lg" style="width:72px;height:72px;font-size:28px;"><?= htmlspecialchars($initial) ?></div>
      <div style="flex:1;">
        <div class="f g3" style="flex-wrap:wrap;margin-bottom:4px;">
          <h1 style="font-size:24px;font-weight:800;"><?= htmlspecialchars($user['username']) ?></h1>
          <span class="tag <?= $user['role'] === 'admin' ? 'tag-accent' : 'tag-gray' ?>"><?= htmlspecialchars($user['role']) ?></span>
        </div>
        <p class="t-s" style="font-size:14px;"><?= htmlspecialchars($user['email']) ?></p>
        <p class="t-m" style="font-size:13px;margin-top:4px;">Member since <?= htmlspecialchars($member_since) ?></p>
      </div>
      <a href="tools.php" class="btn btn-secondary" style="flex-shrink:0;">Browse Tools</a>
    </div>
  </div>

  <div class="g gc3 g4" style="margin-top:32px;margin-bottom:32px;">
    <div class="c-sm sf s2" style="text-align:center;">
      <div style="font-size:36px;font-weight:900;color:var(--accent);line-height:1.2;"><?= $review_count ?></div>
      <div class="t-m" style="font-size:12px;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-top:2px;">Reviews Written</div>
    </div>
    <div class="c-sm sf s3" style="text-align:center;">
      <div style="font-size:36px;font-weight:900;color:var(--accent);line-height:1.2;"><?= $tool_count ?></div>
      <div class="t-m" style="font-size:12px;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-top:2px;">Tools in Database</div>
    </div>
    <div class="c-sm sf s4" style="text-align:center;">
      <div style="font-size:16px;font-weight:700;line-height:1.3;"><?= htmlspecialchars($member_since) ?></div>
      <div class="t-m" style="font-size:12px;text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-top:2px;">Member Since</div>
    </div>
  </div>

  <h2 class="f g3" style="font-size:18px;font-weight:700;margin-bottom:20px;">My Reviews<span class="t-s" style="font-size:14px;font-weight:400;">(<?= $review_count ?>)</span></h2>

  <?php if ($has_reviews): ?>
    <div class="g gc2 g4">
      <?php $i = 0; while ($review = $reviews->fetch_assoc()): $i++; $s = 's' . min(6, (($i - 1) % 6) + 1); ?>
        <div class="c-sm sf <?= $s ?>">
          <div class="f g3" style="justify-content:space-between;margin-bottom:8px;">
            <div style="min-width:0;">
              <h3 style="font-weight:700;font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($review['tool_name']) ?></h3>
              <div style="margin-top:4px;">
                <?php $full = min(5, max(0, (int) round($review['rating']))); ?>
                <span class="st"><?= str_repeat('&#9733;', $full) ?></span>
                <?php if ($full < 5): ?>
                <span class="st-em"><?= str_repeat('&#9733;', 5 - $full) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <form method="POST" action="profile.php" onsubmit="return confirm('Delete this review?');" style="flex-shrink:0;">
              <input type="hidden" name="delete_review" value="<?= (int) $review['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
          <?php if (!empty($review['review_text'])): ?>
            <p class="t-s" style="font-size:14px;line-height:1.6;"><?= htmlspecialchars($review['review_text']) ?></p>
          <?php endif; ?>
          <p class="t-m" style="font-size:13px;margin-top:12px;"><?= date('M j, Y', strtotime($review['created_at'])) ?></p>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="c sf s5" style="text-align:center;padding:48px 24px;">
      <h3 style="font-weight:700;font-size:16px;margin-bottom:4px;">No reviews yet</h3>
      <p class="t-m" style="font-size:14px;margin-bottom:16px;">You haven't written any reviews yet.</p>
      <a href="tools.php" class="btn btn-primary">Explore Tools</a>
    </div>
  <?php endif; ?>

</main>

<?php include 'footer.php'; ?>
