<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === '/dashboard' || $path === '/dashboard/') {
    require dirname(__DIR__) . '/dashboard.php';
    exit;
}

if ($path === '/blog' || $path === '/blog/' || $path === '/blog/digital-marketing-basics') {
    require dirname(__DIR__) . '/blog.php';
    exit;
}

require dirname(__DIR__) . '/index.php';
