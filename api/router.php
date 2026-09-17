<?php
// Vercel Serverless Front-Controller / Router
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriPath = trim($uriPath, '/');

// Remove leading 'api/' if present
if (str_starts_with($uriPath, 'api/')) {
    $uriPath = substr($uriPath, 4);
}

// Remove trailing slashes
$uriPath = trim($uriPath, '/');

// Route mapping
if ($uriPath === '' || $uriPath === 'index' || $uriPath === 'index.php') {
    require __DIR__ . '/index.php';
    exit();
}

$file = basename($uriPath);
if (!str_ends_with($file, '.php')) {
    $file .= '.php';
}

$target = __DIR__ . '/' . $file;
if (file_exists($target) && !in_array($file, ['header.php', 'footer.php', 'config.php', 'router.php'])) {
    require $target;
    exit();
}

// 404
http_response_code(404);
?>
<!DOCTYPE html>
<html>
<head><title>404 Not Found</title></head>
<body style="font-family:sans-serif;text-align:center;padding:50px;">
  <h2>404 - Page Not Found</h2>
  <p><a href="/">Return Home</a></p>
</body>
</html>
