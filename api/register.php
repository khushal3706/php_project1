<?php
require_once 'config.php';

$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'student')"
        );
        $stmt->bind_param('sss', $username, $email, $hash);
        if ($stmt->execute()) {
            $_SESSION['user_id']  = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role']     = 'student';
            $stmt->close();
            header("Location: login.php?registered=1");
            exit();
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}
?>
<?php include 'header.php'; ?>

<main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:96px 16px 64px;">
  <div style="width:100%;max-width:440px;">

    <div style="text-align:center;margin-bottom:28px;">
      <div class="avatar avatar-sm" style="width:48px;height:48px;border-radius:12px;font-size:20px;margin:0 auto 16px;">AI</div>
      <h1 style="font-size:24px;font-weight:800;margin-bottom:4px;">Create Account</h1>
      <p class="t-m" style="font-size:14px;">Start your AI journey today</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-e">
      <?php foreach ($errors as $err): ?>
        <div><?= htmlspecialchars($err) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="c">
      <form action="register.php" method="POST">
        <div style="margin-bottom:16px;">
          <label for="usernameInput" class="lbl">Username</label>
          <input type="text" id="usernameInput" name="username" required class="i" placeholder="Choose a username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div style="margin-bottom:16px;">
          <label for="emailInput" class="lbl">Email Address</label>
          <input type="email" id="emailInput" name="email" required class="i" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div style="margin-bottom:24px;">
          <label for="passwordInput" class="lbl">Password</label>
          <input type="password" id="passwordInput" name="password" required class="i" placeholder="Create a strong password">
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
      </form>
    </div>

    <p style="text-align:center;color:var(--muted);font-size:14px;margin-top:20px;">
      Already have an account?
      <a href="login.php" style="color:var(--accent);font-weight:600;">Sign in</a>
    </p>

  </div>
</main>

<?php include 'footer.php'; ?>
