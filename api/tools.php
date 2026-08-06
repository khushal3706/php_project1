<?php
require_once 'config.php';

$search_q   = trim($_GET['q'] ?? '');
$search_cat = trim($_GET['cat'] ?? '');

$where_clauses = [];
$params        = [];
$types         = '';

if ($search_q !== '') {
    $like = '%' . $search_q . '%';
    $where_clauses[] = '(t.tool_name LIKE ? OR t.description LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

if ($search_cat !== '' && $search_cat !== 'all') {
    $where_clauses[] = 'c.slug = ?';
    $params[] = $search_cat;
    $types   .= 's';
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$sql = "SELECT t.*, c.category_name, c.slug AS cat_slug
        FROM ai_tools t
        LEFT JOIN categories c ON t.category_id = c.id
        $where_sql
        ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tools_result = $stmt->get_result();

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

include 'header.php';
?>

<style>
.tool-card{display:flex;flex-direction:column}
.tool-title{font-weight:700;font-size:1rem;margin-bottom:4px;color:var(--text)}
.tool-desc{font-size:14px;flex:1;margin-bottom:12px;color:var(--secondary)}
.cat-avatar{color:#fff;font-weight:700}
.search-wrap{display:flex;gap:8px;background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:8px 14px;flex:1;max-width:380px;align-items:center}
.search-wrap .i{border:none;padding:0;box-shadow:none}
.search-wrap .i:focus{box-shadow:none}
</style>

<main class="w" style="padding-top:44px;padding-bottom:80px;">

  <div class="sf s1">
    <h1 style="font-size:1.5rem;font-weight:800;">Explore AI Tools</h1>
    <p class="t-s" style="font-size:0.875rem;margin-top:4px;">Browse our curated collection of AI tools</p>
  </div>

  <form method="GET" action="tools.php" class="f g2 sf s2" style="margin-top:24px;">
    <div class="search-wrap">
      <svg width="16" height="16" fill="none" stroke="var(--muted)" viewBox="0 0 24 24" style="flex-shrink:0;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
      </svg>
      <input type="text" name="q" placeholder="Search tools..." value="<?= htmlspecialchars($search_q) ?>" class="i i-sm">
      <?php if ($search_cat !== ''): ?>
      <input type="hidden" name="cat" value="<?= htmlspecialchars($search_cat) ?>">
      <?php endif; ?>
    </div>
  </form>

  <div class="f g2 sf s3" style="flex-wrap:wrap;margin-top:20px;">
    <a href="tools.php<?= $search_q ? '?q=' . urlencode($search_q) : '' ?>" class="btn btn-sm <?= $search_cat === '' ? 'btn-primary' : 'btn-secondary' ?>">
      All
    </a>
    <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()):
      $slug   = $cat['slug'] ?? 'general';
      $active = ($search_cat === $slug);
      $href   = '?cat=' . urlencode($slug) . ($search_q ? '&q=' . urlencode($search_q) : '');
    ?>
    <a href="<?= $href ?>" class="btn btn-sm <?= $active ? 'btn-primary' : 'btn-secondary' ?>">
      <?= htmlspecialchars($cat['category_name']) ?>
    </a>
    <?php endwhile; ?>
  </div>

  <?php if ($tools_result && $tools_result->num_rows > 0): ?>
  <div class="g gc3 g6 sf s4" style="margin-top:28px;">
    <?php while ($tool = $tools_result->fetch_assoc()):
      $cat_slug  = $tool['cat_slug'] ?? 'general';
      $cat_name  = htmlspecialchars($tool['category_name'] ?? 'General');
      $tool_name = htmlspecialchars($tool['tool_name']);
      $icon_text = strtoupper(mb_substr($tool_name, 0, 2));
      $desc      = htmlspecialchars(mb_substr($tool['description'] ?? '', 0, 120)) . (mb_strlen($tool['description'] ?? '') > 120 ? '...' : '');
      $rating    = !empty($tool['rating']) ? (float)$tool['rating'] : 4.5;
      $full      = min(5, max(0, (int)round($rating)));
      $stars     = str_repeat('&#9733;', $full) . str_repeat('&#9734;', 5 - $full);
      $pricing   = htmlspecialchars($tool['pricing'] ?? 'Freemium');
      $pclass    = match (strtolower($pricing)) {
        'free'     => 'tag-green',
        'paid'     => 'tag-accent',
        default    => 'tag-amber'
      };
      $cat_color = match ($cat_slug) {
        'code'         => '#1f6f43',
        'image'        => '#7c5cd6',
        'data'         => '#d97706',
        'writing'      => '#15803d',
        'audio'        => '#c2410c',
        'agent'        => '#4f46e5',
        'productivity' => '#0e7490',
        'education'    => '#0369a1',
        default        => '#1f6f43'
      };
    ?>
    <div class="c-sm tool-card">
      <div class="f g3" style="margin-bottom:12px;">
        <div class="avatar cat-avatar" style="background:<?= $cat_color ?>;"><?= $icon_text ?></div>
        <span class="tag tag-gray"><?= $cat_name ?></span>
      </div>
      <div class="tool-title"><?= $tool_name ?></div>
      <p class="t-s tool-desc"><?= $desc ?></p>
      <div class="f g3" style="margin-bottom:12px;">
        <span class="st"><?= $stars ?></span>
        <span class="tag <?= $pclass ?>"><?= $pricing ?></span>
      </div>
      <a href="tool_detail.php?id=<?= (int)$tool['id'] ?>" class="btn btn-primary btn-sm btn-block">View Details</a>
    </div>
    <?php endwhile; ?>
  </div>

  <?php else: ?>
  <div class="c sf s4" style="text-align:center;padding:48px 24px;margin-top:28px;">
    <div style="width:48px;height:48px;border-radius:50%;background:var(--bg-soft);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
      <svg width="20" height="20" fill="none" stroke="var(--muted)" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
      </svg>
    </div>
    <div style="font-weight:600;font-size:1.125rem;margin-bottom:4px;">No tools found</div>
    <p class="t-s" style="font-size:14px;">
      <?php if ($search_q): ?>
        No results for "<?= htmlspecialchars($search_q) ?>".
      <?php else: ?>
        No tools in this category yet.
      <?php endif; ?>
      <a href="tools.php" style="color:var(--accent);margin-left:4px;">Clear filters</a>
    </p>
  </div>
  <?php endif; ?>
</main>

<?php
$stmt->close();
include 'footer.php';
?>
