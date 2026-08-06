<footer style="border-top:1px solid var(--border);padding:28px 0;background:var(--bg-soft);margin-top:40px;">
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$li = isset($_SESSION['user_id']);
$un = $_SESSION['username'] ?? '';
$ia = ($_SESSION['role'] ?? '') === 'admin';
?>
<div class="w">
  <div class="f" style="flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;">
    <nav class="f" style="flex-wrap:wrap;align-items:center;gap:4px;">
      <a href="index.php" class="f-link">Home</a>
      <span style="color:var(--muted);font-size:13px;">&middot;</span>
      <a href="tools.php" class="f-link">Tools</a>
      <span style="color:var(--muted);font-size:13px;">&middot;</span>
      <a href="recommend.php" class="f-link">Recommend</a>
<?php if ($li): ?>
      <span style="color:var(--muted);font-size:13px;">&middot;</span>
      <a href="profile.php" class="f-link">Profile</a>
<?php if ($ia): ?>
      <span style="color:var(--muted);font-size:13px;">&middot;</span>
      <a href="admin_dashboard.php" class="f-link">Admin</a>
<?php endif; ?>
<?php endif; ?>
    </nav>
    <span class="t-m" style="font-size:12px;">&copy; 2026 AI Portal. All rights reserved.</span>
  </div>
</div>
<style>.f-link{color:var(--muted);font-size:13px;transition:color .15s}.f-link:hover{color:var(--accent)}</style>
</footer>

</body>
</html>
