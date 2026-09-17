<?php
require_once 'config.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$modal_success = '';
$modal_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_tool') {
    $tool_name   = trim($_POST['tool_name']   ?? '');
    $description = trim($_POST['description'] ?? '');
    $url         = trim($_POST['url']         ?? '');
    $icon_url    = trim($_POST['icon_url']    ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $pricing     = $_POST['pricing'] ?? 'Freemium';
    $rating      = isset($_POST['rating']) && is_numeric($_POST['rating']) ? (float)$_POST['rating'] : 4.5;
    $added_by    = $_SESSION['user_id'];

    $allowed_pricing = ['Free','Freemium','Paid'];
    if (empty($tool_name)) {
        $modal_error = 'Tool name is required.';
    } elseif (!in_array($pricing, $allowed_pricing)) {
        $modal_error = 'Invalid pricing option.';
    } else {
        $rating = min(5.0, max(1.0, round($rating, 1)));
        $category_id = $category_id > 0 ? $category_id : null;
        $stmt = $conn->prepare(
            "INSERT INTO ai_tools (tool_name, description, url, icon_url, category_id, pricing, rating, added_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssisdi', $tool_name, $description, $url, $icon_url, $category_id, $pricing, $rating, $added_by);
        if ($stmt->execute()) {
            $modal_success = "Tool \"" . htmlspecialchars($tool_name) . "\" added successfully!";
        } else {
            $modal_error = 'Failed to add tool. Please try again.';
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_tool') {
    $tool_id     = (int)($_POST['tool_id'] ?? 0);
    $tool_name   = trim($_POST['tool_name']   ?? '');
    $description = trim($_POST['description'] ?? '');
    $url         = trim($_POST['url']         ?? '');
    $icon_url    = trim($_POST['icon_url']    ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $pricing     = $_POST['pricing'] ?? 'Freemium';
    $rating      = isset($_POST['rating']) && is_numeric($_POST['rating']) ? (float)$_POST['rating'] : 4.5;

    $allowed_pricing = ['Free','Freemium','Paid'];
    if ($tool_id <= 0) {
        $modal_error = 'Invalid tool ID for update.';
    } elseif (empty($tool_name)) {
        $modal_error = 'Tool name is required.';
    } elseif (!in_array($pricing, $allowed_pricing)) {
        $modal_error = 'Invalid pricing option.';
    } else {
        $rating = min(5.0, max(1.0, round($rating, 1)));
        $category_id = $category_id > 0 ? $category_id : null;
        $stmt = $conn->prepare(
            "UPDATE ai_tools 
             SET tool_name = ?, description = ?, url = ?, icon_url = ?, category_id = ?, pricing = ?, rating = ?
             WHERE id = ?"
        );
        $stmt->bind_param('ssssisdi', $tool_name, $description, $url, $icon_url, $category_id, $pricing, $rating, $tool_id);
        if ($stmt->execute()) {
            $modal_success = "Tool \"" . htmlspecialchars($tool_name) . "\" updated successfully!";
        } else {
            $modal_error = 'Failed to update tool. Please try again.';
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_tool') {
    $del_id = (int)($_POST['tool_id'] ?? 0);
    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM ai_tools WHERE id = ?");
        $stmt->bind_param('i', $del_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php?deleted=1");
        exit();
    }
}

$edit_id = (int)($_GET['edit'] ?? 0);
$edit_tool_data = null;
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM ai_tools WHERE id = ?");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $edit_tool_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$tools_result = $conn->query(
    "SELECT t.*, c.category_name, c.slug AS cat_slug
     FROM ai_tools t
     LEFT JOIN categories c ON t.category_id = c.id
     ORDER BY t.created_at DESC"
);

$cats_result = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");

$stat_tools = (int)$conn->query("SELECT COUNT(*) AS c FROM ai_tools")->fetch_assoc()['c'];
$stat_users = (int)$conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$stat_cats  = (int)$conn->query("SELECT COUNT(*) AS c FROM categories")->fetch_assoc()['c'];
$stat_avg   = $conn->query("SELECT ROUND(AVG(rating),1) AS a FROM ai_tools")->fetch_assoc()['a'] ?? '--';
?>
<?php include 'header.php'; ?>

<style>
.modal-overlay {
  position:fixed;inset:0;
  background:rgba(18,27,20,.55);
  z-index:100;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:16px;
  opacity:0;
  pointer-events:none;
  transition:opacity .25s ease;
}
.modal-overlay.open {
  opacity:1;
  pointer-events:auto;
}
.modal-overlay .c {
  width:100%;
  max-width:500px;
  transform:scale(.95);
  transition:transform .25s ease;
}
.modal-overlay.open .c {
  transform:scale(1);
}
.stat-num{font-size:32px;font-weight:900;color:var(--accent);line-height:1.2}
.stat-lbl2{font-size:12px;text-transform:uppercase;letter-spacing:.06em;font-weight:600;margin-top:4px;color:var(--muted)}
.table-card{overflow:hidden;padding:0}
.table-head{display:flex;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:12px}
</style>

<main class="w" style="padding-top:32px;padding-bottom:64px;">

  <div class="f g4" style="justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;">
    <div>
      <h1 style="font-size:24px;font-weight:800;margin-bottom:4px;">Admin Panel</h1>
      <p class="t-m" style="font-size:14px;">
        Logged in as <strong style="color:var(--text);"><?= htmlspecialchars($_SESSION['username']) ?></strong>
        &middot; <a href="logout.php" style="color:var(--error);font-weight:600;">Sign Out</a>
      </p>
    </div>
    <button onclick="openModal()" class="btn btn-primary">Add New Tool</button>
  </div>

  <?php if ($modal_success): ?><div class="alert alert-s"><?= $modal_success ?></div><?php endif; ?>
  <?php if ($modal_error):   ?><div class="alert alert-e"><?= htmlspecialchars($modal_error) ?></div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-s">Tool deleted successfully.</div><?php endif; ?>

  <div class="g gc4 g4" style="margin-bottom:32px;">
    <div class="c-sm sf s1" style="text-align:center;">
      <div class="stat-num"><?= $stat_tools ?></div>
      <div class="stat-lbl2">Total Tools</div>
    </div>
    <div class="c-sm sf s2" style="text-align:center;">
      <div class="stat-num"><?= $stat_users ?></div>
      <div class="stat-lbl2">Users</div>
    </div>
    <div class="c-sm sf s3" style="text-align:center;">
      <div class="stat-num"><?= $stat_cats ?></div>
      <div class="stat-lbl2">Categories</div>
    </div>
    <div class="c-sm sf s4" style="text-align:center;">
      <div class="stat-num"><?= htmlspecialchars((string)$stat_avg) ?></div>
      <div class="stat-lbl2">Avg Rating</div>
    </div>
  </div>

  <div class="c table-card">
    <div class="table-head">
      <div style="font-weight:600;font-size:14px;">All Tools <span class="t-m" style="font-weight:400;">(<?= $stat_tools ?> entries)</span></div>
      <input type="text" id="adminSearch" class="i i-sm" style="width:220px;" placeholder="Filter table..." oninput="filterTable(this.value)">
    </div>
    <div style="overflow-x:auto;">
      <table class="tbl" id="toolsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tool Name</th>
            <th>Category</th>
            <th>Pricing</th>
            <th>Rating</th>
            <th>Added</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($tools_result && $tools_result->num_rows > 0):
            while ($tool = $tools_result->fetch_assoc()): ?>
          <tr class="tool-row" data-name="<?= strtolower(htmlspecialchars($tool['tool_name'])) ?>">
            <td class="t-m" style="font-size:13px;font-family:monospace;">#<?= $tool['id'] ?></td>
            <td>
              <div class="f g2" style="align-items:center;">
                <div class="tool-icon-avatar" style="width:32px;height:32px;padding:3px;border-radius:8px;">
                  <?php $t_icon = get_tool_icon_url($tool); if (!empty($t_icon)): ?>
                  <img src="<?= htmlspecialchars($t_icon) ?>" alt="" class="tool-icon-img" onerror="this.style.display='none';">
                  <?php endif; ?>
                </div>
                <div>
                  <div style="font-weight:600;"><?= htmlspecialchars($tool['tool_name']) ?></div>
                  <?php if (!empty($tool['url'])): ?>
                  <a href="<?= htmlspecialchars($tool['url']) ?>" target="_blank" rel="noopener" style="font-size:12px;color:var(--accent);"><?= htmlspecialchars(parse_url($tool['url'], PHP_URL_HOST) ?? $tool['url']) ?></a>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td><span class="tag tag-accent"><?= htmlspecialchars($tool['category_name'] ?? 'Uncategorised') ?></span></td>
            <td><span class="tag tag-gray"><?= htmlspecialchars($tool['pricing']) ?></span></td>
            <td style="color:var(--star);font-weight:600;"><?= htmlspecialchars($tool['rating'] ?? '--') ?></td>
            <td class="t-m" style="font-size:13px;"><?= date('d M Y', strtotime($tool['created_at'])) ?></td>
            <td style="text-align:right;white-space:nowrap;">
              <button type="button" class="btn btn-secondary btn-sm" onclick="openEditModal(<?= htmlspecialchars(json_encode([
                'id' => (int)$tool['id'],
                'tool_name' => $tool['tool_name'],
                'url' => $tool['url'] ?? '',
                'icon_url' => $tool['icon_url'] ?? '',
                'category_id' => (int)($tool['category_id'] ?? 0),
                'pricing' => $tool['pricing'],
                'rating' => (float)($tool['rating'] ?? 4.5),
                'description' => $tool['description'] ?? ''
              ]), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
              <form method="POST" action="admin_dashboard.php" onsubmit="return confirm('Delete this tool?');" style="display:inline;margin-left:4px;">
                <input type="hidden" name="action" value="delete_tool">
                <input type="hidden" name="tool_id" value="<?= $tool['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
          <?php endwhile;
          else: ?>
          <tr>
            <td colspan="7" style="text-align:center;padding:40px 16px;color:var(--muted);font-size:14px;">No tools found. Add your first tool!</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<div class="modal-overlay" id="addToolModal">
  <div class="c">
    <div class="f g4" style="justify-content:space-between;margin-bottom:20px;">
      <h2 style="font-size:18px;font-weight:700;">Add AI Tool</h2>
      <button onclick="closeModal()" style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--muted);background:transparent;border:none;cursor:pointer;font-size:20px;">&times;</button>
    </div>

    <form action="admin_dashboard.php" method="POST">
      <input type="hidden" name="action" value="add_tool">

      <div style="margin-bottom:16px;">
        <label class="lbl">Tool Name *</label>
        <input type="text" name="tool_name" required class="i" placeholder="e.g. DevMind AI">
      </div>
      <div style="margin-bottom:16px;">
        <label class="lbl">Website URL</label>
        <input type="url" name="url" class="i" placeholder="https://...">
      </div>
      <div style="margin-bottom:16px;">
        <label class="lbl">Icon URL / Path (optional)</label>
        <input type="text" name="icon_url" class="i" placeholder="e.g. assets/icons/cursor.png or https://...">
        <p class="t-m" style="font-size:11px;margin-top:4px;">Leave empty to auto-detect icon from website URL.</p>
      </div>
      <div class="f g4" style="margin-bottom:16px;">
        <div style="flex:1;">
          <label class="lbl">Category</label>
          <select name="category_id" class="i">
            <option value="0">Select</option>
            <?php
              $cats_result->data_seek(0);
              while ($cat = $cats_result->fetch_assoc()):
            ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div style="flex:1;">
          <label class="lbl">Pricing</label>
          <select name="pricing" class="i">
            <option value="Free">Free</option>
            <option value="Freemium" selected>Freemium</option>
            <option value="Paid">Paid</option>
          </select>
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <label class="lbl">Rating (1.0 - 5.0)</label>
        <input type="number" name="rating" class="i" min="1.0" max="5.0" step="0.1" value="4.5">
      </div>
      <div style="margin-bottom:20px;">
        <label class="lbl">Description</label>
        <textarea name="description" class="i" rows="3" placeholder="Brief description of the tool..."></textarea>
      </div>
      <div class="f g4">
        <button type="button" onclick="closeModal()" class="btn btn-secondary" style="flex:1;">Cancel</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Save Tool</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="editToolModal">
  <div class="c">
    <div class="f g4" style="justify-content:space-between;margin-bottom:20px;">
      <h2 style="font-size:18px;font-weight:700;">Edit AI Tool</h2>
      <button onclick="closeEditModal()" style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--muted);background:transparent;border:none;cursor:pointer;font-size:20px;">&times;</button>
    </div>

    <form action="admin_dashboard.php" method="POST">
      <input type="hidden" name="action" value="update_tool">
      <input type="hidden" name="tool_id" id="edit_tool_id" value="">

      <div style="margin-bottom:16px;">
        <label class="lbl">Tool Name *</label>
        <input type="text" name="tool_name" id="edit_tool_name" required class="i" placeholder="e.g. Cursor">
      </div>
      <div style="margin-bottom:16px;">
        <label class="lbl">Website URL</label>
        <input type="url" name="url" id="edit_url" class="i" placeholder="https://...">
      </div>
      <div style="margin-bottom:16px;">
        <label class="lbl">Icon URL / Path (optional)</label>
        <input type="text" name="icon_url" id="edit_icon_url" class="i" placeholder="e.g. assets/icons/cursor.png or https://...">
      </div>
      <div class="f g4" style="margin-bottom:16px;">
        <div style="flex:1;">
          <label class="lbl">Category</label>
          <select name="category_id" id="edit_category_id" class="i">
            <option value="0">Select</option>
            <?php
              $cats_result->data_seek(0);
              while ($cat = $cats_result->fetch_assoc()):
            ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div style="flex:1;">
          <label class="lbl">Pricing</label>
          <select name="pricing" id="edit_pricing" class="i">
            <option value="Free">Free</option>
            <option value="Freemium">Freemium</option>
            <option value="Paid">Paid</option>
          </select>
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <label class="lbl">Rating (1.0 - 5.0)</label>
        <input type="number" name="rating" id="edit_rating" class="i" min="1.0" max="5.0" step="0.1" placeholder="4.5">
      </div>
      <div style="margin-bottom:20px;">
        <label class="lbl">Description</label>
        <textarea name="description" id="edit_description" class="i" rows="3" placeholder="Brief description of the tool..."></textarea>
      </div>
      <div class="f g4">
        <button type="button" onclick="closeEditModal()" class="btn btn-secondary" style="flex:1;">Cancel</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal() { document.getElementById('addToolModal').classList.add('open'); }
function closeModal() { document.getElementById('addToolModal').classList.remove('open'); }
document.getElementById('addToolModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

function openEditModal(tool) {
  document.getElementById('edit_tool_id').value = tool.id;
  document.getElementById('edit_tool_name').value = tool.tool_name || '';
  document.getElementById('edit_url').value = tool.url || '';
  document.getElementById('edit_icon_url').value = tool.icon_url || '';
  document.getElementById('edit_category_id').value = tool.category_id || 0;
  document.getElementById('edit_pricing').value = tool.pricing || 'Freemium';
  document.getElementById('edit_rating').value = tool.rating !== undefined ? tool.rating : 4.5;
  document.getElementById('edit_description').value = tool.description || '';
  document.getElementById('editToolModal').classList.add('open');
}

function closeEditModal() {
  document.getElementById('editToolModal').classList.remove('open');
}
document.getElementById('editToolModal').addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });

function filterTable(q) {
  document.querySelectorAll('.tool-row').forEach(function(row) {
    row.style.display = row.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
  });
}

<?php if ($edit_tool_data): ?>
document.addEventListener('DOMContentLoaded', function() {
  openEditModal(<?= json_encode([
    'id' => (int)$edit_tool_data['id'],
    'tool_name' => $edit_tool_data['tool_name'],
    'url' => $edit_tool_data['url'] ?? '',
    'category_id' => (int)($edit_tool_data['category_id'] ?? 0),
    'pricing' => $edit_tool_data['pricing'],
    'rating' => (float)($edit_tool_data['rating'] ?? 4.5),
    'description' => $edit_tool_data['description'] ?? ''
  ]) ?>);
});
<?php endif; ?>
</script>

<?php include 'footer.php'; ?>
