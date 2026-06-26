<?php
/**
 * api/index.php — Front Controller Router for Vercel Deployments
 * Routes incoming web requests to the appropriate PHP script in the root directory.
 */

// Set working directory to project root so that all relative PHP require/include paths function correctly
$projectRoot = dirname(__DIR__);
chdir($projectRoot);

// Add project root to the PHP include path
set_include_path($projectRoot . PATH_SEPARATOR . get_include_path());

// Retrieve and normalize the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Parse URL components to isolate the path from query arguments
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Clean leading/trailing slashes
$path = trim($path, '/');

// If accessing the root, serve index.php
if ($path === '') {
    $path = 'index.php';
}

// Map clean URL paths lacking extensions (e.g., 'login', 'signup', 'owner-login') to their PHP files
if (!preg_match('/\.php$/i', $path) && !is_dir($projectRoot . '/' . $path)) {
    // If the path contains subdirectories (like admin/login), check if adding .php makes it a valid file
    if (file_exists($projectRoot . '/' . $path . '.php')) {
        $path .= '.php';
    }
}

// If the path is a directory (e.g. 'admin' or 'admin/'), check for an index.php within it
if (is_dir($projectRoot . '/' . $path)) {
    $indexPath = trim($path . '/index.php', '/');
    if (file_exists($projectRoot . '/' . $indexPath)) {
        $path = $indexPath;
    }
}

// Define the full path to the target script
$targetFile = $projectRoot . '/' . $path;

// Prevent Directory Traversal vulnerability by resolving the real canonicalized paths
$realTarget = realpath($targetFile);

// Check if the file exists, is inside the project root, is a PHP script, and is not this router itself
if ($realTarget && strpos($realTarget, $projectRoot) === 0) {
    if (is_file($realTarget) && pathinfo($realTarget, PATHINFO_EXTENSION) === 'php') {
        // Prevent infinite recursion if the target matches the router script path
        $routerPath = realpath(__FILE__);
        if ($realTarget !== $routerPath) {
            require_once $realTarget;
            exit;
        }
    }
}

// Fallback to the home page index.php if the script does not exist (or serve a 404 for missing static files)
// For static assets, Vercel routes will handle them, but if they fall through to PHP, we can return 404.
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|otf|mp4)$/i', $path)) {
    http_response_code(404);
    echo "404 - Static asset not found";
    exit;
}

$fallbackFile = $projectRoot . '/index.php';
if (file_exists($fallbackFile)) {
    require_once $fallbackFile;
} else {
    http_response_code(404);
    echo "404 - Not Found";
}
