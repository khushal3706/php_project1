<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Tool Recommendation Portal</title>
<meta name="description" content="Discover and compare AI tools for your workflow.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/styles.css">
<style>
body{background:linear-gradient(180deg,#ffffff 0%,#fbfdfb 100%)}
.navbar{
  position:fixed;top:0;width:100%;z-index:50;
  background:rgba(255,255,255,.9);
  backdrop-filter:blur(12px);
  -webkit-backdrop-filter:blur(12px);
  border-bottom:1px solid var(--border);
}
.logo-box{
  width:30px;height:30px;border-radius:8px;background:var(--accent);
  color:#fff;display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:13px;
}
.brand{font-weight:800;font-size:15px;letter-spacing:-.01em}
.brand em{font-style:normal;color:var(--accent)}
.hero-wrap{
  position:relative;
  background:
    radial-gradient(900px 380px at 50% -120px, var(--accent-subtle), transparent 70%),
    #ffffff;
}
</style>
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$li = isset($_SESSION['user_id']);
$un = $_SESSION['username'] ?? '';
$ia = ($_SESSION['role'] ?? '') === 'admin';
?>

<nav class="navbar">
  <div class="w">
    <div class="f" style="justify-content:space-between;height:60px;">
      <a href="index.php" class="f g2">
        <span class="logo-box">AI</span>
        <span class="brand">AI <em>Portal</em></span>
      </a>
      <div class="f g2" style="display:none;" id="deskNav">
        <a href="index.php" class="nav-lk">Home</a>
        <a href="tools.php" class="nav-lk" style="display:inline-flex;align-items:center;gap:6px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a4 4 0 0 1 4 4c0 .73-.2 1.41-.54 2H18a3 3 0 0 1 3 3v2a3 3 0 0 1-3 3h-2.54c.34.59.54 1.27.54 2a4 4 0 1 1-8 0c0-.73.2-1.41.54-2H6a3 3 0 0 1-3-3v-2a3 3 0 0 1 3-3h2.54A3.98 3.98 0 0 1 8 6a4 4 0 0 1 4-4z"/>
            <circle cx="9" cy="13" r="1" fill="currentColor"/>
            <circle cx="15" cy="13" r="1" fill="currentColor"/>
          </svg>
          Tools
        </a>
        <a href="recommend.php" class="nav-lk">Recommend</a>
<?php if ($li): ?>
        <a href="profile.php" class="nav-lk">Profile</a>
<?php if ($ia): ?>
        <a href="admin_dashboard.php" class="nav-lk">Admin</a>
<?php endif; ?>
        <div class="f g2" style="margin-left:12px;padding-left:12px;border-left:1px solid var(--border);">
          <span style="font-size:14px;font-weight:600;color:var(--secondary);"><?= htmlspecialchars($un) ?></span>
          <a href="logout.php" class="btn btn-sm btn-secondary">Sign Out</a>
        </div>
<?php else: ?>
        <a href="login.php" class="nav-lk">Login</a>
        <a href="register.php" class="btn btn-sm btn-primary" style="margin-left:4px;">Get Started</a>
<?php endif; ?>
      </div>
      <button id="mbBtn" class="btn btn-sm" style="background:#fff;border:1px solid var(--border);padding:8px;display:none;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
    <div id="mbMenu" style="display:none;padding-bottom:12px;border-top:1px solid var(--border);">
      <div class="f" style="flex-direction:column;gap:2px;padding-top:8px;">
        <a href="index.php" class="nav-lk">Home</a>
        <a href="tools.php" class="nav-lk" style="display:inline-flex;align-items:center;gap:6px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a4 4 0 0 1 4 4c0 .73-.2 1.41-.54 2H18a3 3 0 0 1 3 3v2a3 3 0 0 1-3 3h-2.54c.34.59.54 1.27.54 2a4 4 0 1 1-8 0c0-.73.2-1.41.54-2H6a3 3 0 0 1-3-3v-2a3 3 0 0 1 3-3h2.54A3.98 3.98 0 0 1 8 6a4 4 0 0 1 4-4z"/>
            <circle cx="9" cy="13" r="1" fill="currentColor"/>
            <circle cx="15" cy="13" r="1" fill="currentColor"/>
          </svg>
          Tools
        </a>
        <a href="recommend.php" class="nav-lk">Recommend</a>
<?php if ($li): ?>
        <a href="profile.php" class="nav-lk">Profile</a>
<?php if ($ia): ?>
        <a href="admin_dashboard.php" class="nav-lk">Admin</a>
<?php endif; ?>
        <div style="padding-top:8px;margin-top:8px;border-top:1px solid var(--border);">
          <span style="font-size:14px;padding:8px 14px;display:block;color:var(--secondary);"><?= htmlspecialchars($un) ?></span>
          <a href="logout.php" class="btn btn-sm btn-secondary" style="margin:4px 14px;">Sign Out</a>
        </div>
<?php else: ?>
        <a href="login.php" class="nav-lk">Login</a>
        <a href="register.php" class="btn btn-sm btn-primary" style="margin:4px 14px;display:inline-block;">Get Started</a>
<?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<div style="height:60px;"></div>

<script>
(function(){var b=document.getElementById('mbBtn'),m=document.getElementById('mbMenu'),d=document.getElementById('deskNav');
function u(){var w=window.innerWidth;if(w<768){d.style.display='none';b.style.display=''}else{d.style.display='flex';b.style.display='none';m.style.display='none'}}
u();window.addEventListener('resize',u);
b&&b.addEventListener('click',function(){m.style.display=m.style.display==='none'?'block':'none'})})();
</script>
