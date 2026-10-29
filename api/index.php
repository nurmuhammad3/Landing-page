<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === '/assets/nur-muhammad.jpg') {
    $photo = dirname(__DIR__) . '/assets/nur-muhammad.jpg';
    if (!is_file($photo)) {
        http_response_code(404);
        exit;
    }

    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400');
    header('Content-Length: ' . filesize($photo));
    readfile($photo);
    exit;
}

if ($path === '/dashboard' || $path === '/dashboard/') {
    require dirname(__DIR__) . '/dashboard.php';
    exit;
}

require dirname(__DIR__) . '/index.php';
