<?php
require_once 'config.php';

if (!empty($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'index.php'));
    exit();
}

$error   = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Account created successfully! Please sign in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
            usleep(300000);
        }
    }
}
?>
<?php include 'header.php'; ?>

<main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:96px 16px 64px;">
  <div style="width:100%;max-width:440px;">

    <div style="text-align:center;margin-bottom:28px;">
      <div class="avatar avatar-sm" style="width:48px;height:48px;border-radius:12px;font-size:20px;margin:0 auto 16px;">AI</div>
      <h1 style="font-size:24px;font-weight:800;margin-bottom:4px;">Sign In</h1>
      <p class="t-m" style="font-size:14px;">Enter your credentials to continue</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-e"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-s"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="c">
      <form action="login.php" method="POST">
        <div style="margin-bottom:16px;">
          <label for="emailInput" class="lbl">Email Address</label>
          <input type="email" id="emailInput" name="email" required class="i" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div style="margin-bottom:24px;">
          <label for="passwordInput" class="lbl">Password</label>
          <input type="password" id="passwordInput" name="password" required class="i" placeholder="Enter your password">
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
      </form>
    </div>

    <p style="text-align:center;color:var(--muted);font-size:14px;margin-top:20px;">
      Don't have an account?
      <a href="register.php" style="color:var(--accent);font-weight:600;">Register</a>
    </p>

  </div>
</main>

<?php include 'footer.php'; ?>
