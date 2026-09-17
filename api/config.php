<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host     = getenv('DB_HOST') ?: 'localhost';
$port     = (int)(getenv('DB_PORT') ?: 3306);
$db_user  = getenv('DB_USER') ?: 'root';
$db_pass  = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'ai_tool_portal';
$use_ssl  = getenv('DB_SSL') === 'true' || str_contains($host, 'aivencloud.com') || str_contains($host, 'tidbcloud.com');

mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn = mysqli_init();
    if ($use_ssl) {
        $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        $connected = @$conn->real_connect($host, $db_user, $db_pass, $database, $port, null, MYSQLI_CLIENT_SSL);
    } else {
        $connected = @$conn->real_connect($host, $db_user, $db_pass, $database, $port);
    }

    if (!$connected || $conn->connect_error) {
        throw new Exception($conn->connect_error ?: 'Connection failed');
    }
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Database Setup Required - AI Portal</title>
      <link rel="stylesheet" href="/assets/styles.css">
      <style>
        body { background:#fbfdfb; display:flex; align-items:center; justify-content:center; min-height:100vh; font-family:Inter,sans-serif; margin:0; padding:16px; box-sizing:border-box; }
        .setup-box { max-width:620px; width:100%; padding:32px; background:#fff; border-radius:12px; border:1px solid #dcdfdc; box-shadow:0 8px 30px rgba(0,0,0,0.06); }
        .badge { background:#fee2e2; color:#b91c1c; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px; display:inline-block; margin-bottom:12px; }
        h1 { font-size:22px; font-weight:800; margin-bottom:8px; color:#121b14; }
        p { font-size:14px; color:#4a554a; line-height:1.6; margin-bottom:16px; }
        code { background:#f0f3f0; padding:2px 6px; border-radius:4px; font-size:13px; color:#1f6f43; font-weight:600; }
        .steps { background:#f8faf8; border:1px solid #e2e8e2; border-radius:8px; padding:18px 20px; font-size:13px; line-height:1.7; }
        .steps ol { margin:8px 0 0 16px; padding:0; }
        .steps li { margin-bottom:8px; }
        .steps ul { margin:4px 0 0 16px; padding:0; list-style-type:square; }
      </style>
    </head>
    <body>
      <div class="setup-box">
        <span class="badge">Cloud MySQL Required for Vercel</span>
        <h1>Connect Your Cloud Database</h1>
        <p>Vercel runs as serverless functions and does not have a local MySQL server installed. To connect this live deployment to your database:</p>
        <div class="steps">
          <strong>Setup Instructions:</strong>
          <ol>
            <li>Create a free cloud MySQL database (e.g. on <strong>Aiven</strong>, <strong>TiDB Cloud</strong>, or <strong>Clever Cloud</strong>).</li>
            <li>Import <code>database.sql</code> into your cloud database to populate the 22 AI tools and admin user.</li>
            <li>In your <strong>Vercel Project Settings &rarr; Environment Variables</strong>, add:
              <ul>
                <li><code>DB_HOST</code> = <em>your-db-host</em></li>
                <li><code>DB_PORT</code> = <em>your-db-port</em> (default: 3306)</li>
                <li><code>DB_USER</code> = <em>your-db-username</em></li>
                <li><code>DB_PASS</code> = <em>your-db-password</em></li>
                <li><code>DB_NAME</code> = <code>ai_tool_portal</code></li>
              </ul>
            </li>
            <li>Redeploy or reload this page.</li>
          </ol>
        </div>
        <p style="margin-top:16px; font-size:12px; color:#718071;"><strong>Connection Status:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
      </div>
    </body>
    </html>
    <?php
    exit();
}