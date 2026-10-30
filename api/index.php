<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$supabaseUrl = rtrim(trim((string) (getenv('SUPABASE_URL') ?: '')), '/');
$supabaseAnonKey = trim((string) (getenv('SUPABASE_PUBLISHABLE_KEY') ?: getenv('SUPABASE_ANON_KEY') ?: ''));
$GLOBALS['SUPABASE_PUBLIC_CONFIG'] = [
    'url' => $supabaseUrl,
    'anonKey' => $supabaseAnonKey,
];

if ($path === '/api/leads') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $respond = static function (int $status, array $payload): never {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    };

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        $respond(405, ['error' => 'method_not_allowed']);
    }

    if ($supabaseUrl === '' || $supabaseAnonKey === '') {
        $respond(503, ['error' => 'contact_form_not_configured']);
    }

    $rawBody = file_get_contents('php://input');
    if (!is_string($rawBody) || strlen($rawBody) > 10000) {
        $respond(413, ['error' => 'request_too_large']);
    }

    $input = json_decode($rawBody, true);
    if (!is_array($input)) {
        $respond(400, ['error' => 'invalid_request']);
    }

    // Quietly accept bot submissions to a hidden honeypot without storing them.
    if (trim((string) ($input['website'] ?? '')) !== '') {
        $respond(202, ['ok' => true]);
    }

    $name = trim((string) ($input['name'] ?? ''));
    $email = strtolower(trim((string) ($input['email'] ?? '')));
    $phone = trim((string) ($input['phone'] ?? ''));
    $message = trim((string) ($input['message'] ?? ''));
    $privacyConsent = ($input['privacy_consent'] ?? false) === true;

    if ($name === '' || mb_strlen($name) > 120 || mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
        $respond(422, ['error' => 'invalid_fields']);
    }
    if ($email !== '' && (mb_strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
        $respond(422, ['error' => 'invalid_fields']);
    }
    if ($phone !== '' && (mb_strlen($phone) > 40 || preg_match('/^[+0-9() .-]+$/', $phone) !== 1)) {
        $respond(422, ['error' => 'invalid_fields']);
    }
    if ($email === '' && $phone === '') {
        $respond(422, ['error' => 'contact_required']);
    }
    if (!$privacyConsent) {
        $respond(422, ['error' => 'consent_required']);
    }

    $payload = json_encode([
        'name' => $name,
        'email' => $email !== '' ? $email : null,
        'phone' => $phone !== '' ? $phone : null,
        'message' => $message,
        'privacy_consent' => true,
        'source' => 'website',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            // The API key selects the anon role. Do not send a publishable key as a Bearer JWT.
            'header' => "apikey: {$supabaseAnonKey}\r\nContent-Type: application/json\r\nPrefer: return=minimal\r\n",
            'content' => $payload,
            'ignore_errors' => true,
            'timeout' => 8,
        ],
    ]);

    $response = @file_get_contents($supabaseUrl . '/rest/v1/website_leads', false, $context);
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s([0-9]{3})\s/', $statusLine, $statusMatch);
    $supabaseStatus = isset($statusMatch[1]) ? (int) $statusMatch[1] : 0;

    if ($response === false || $supabaseStatus < 200 || $supabaseStatus >= 300) {
        error_log('Supabase lead submission failed with HTTP ' . $supabaseStatus);
        $respond(502, ['error' => 'submission_failed']);
    }

    $respond(201, ['ok' => true]);
}

if ($path === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nAllow: /\nDisallow: /dashboard\n\nUser-agent: OAI-SearchBot\nAllow: /\nDisallow: /dashboard\n\nUser-agent: GPTBot\nAllow: /\nDisallow: /dashboard\n\nUser-agent: Google-Extended\nAllow: /\nDisallow: /dashboard\n\nUser-agent: Googlebot\nAllow: /\nDisallow: /dashboard\n\nSitemap: https://landing-page-nur-811a.vercel.app/sitemap.xml\n";
    exit;
}

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $urls = [
        '/',
        '/blog',
        '/blog/digital-marketing-basics',
        '/blog/seo-for-small-business',
        '/blog/facebook-marketing',
        '/blog/google-ads-basics',
        '/blog/nur-muhammad-personal-branding',
    ];
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        echo '<url><loc>https://landing-page-nur-811a.vercel.app' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc><lastmod>' . date('Y-m-d') . '</lastmod></url>';
    }
    echo '</urlset>';
    exit;
}

if ($path === '/llms.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "# Nur Muhammad\n\n> Digital marketer sharing practical guides about SEO, Facebook marketing, and Google Ads.\n\n## Website\n- Home: https://landing-page-nur-811a.vercel.app/\n- About and services: https://landing-page-nur-811a.vercel.app/#about\n- Blog: https://landing-page-nur-811a.vercel.app/blog\n- Marketing dashboard: https://landing-page-nur-811a.vercel.app/dashboard (demonstration data)\n\n## Articles\n- Digital marketing for small businesses: https://landing-page-nur-811a.vercel.app/blog/digital-marketing-basics\n- SEO for small businesses: https://landing-page-nur-811a.vercel.app/blog/seo-for-small-business\n- Facebook marketing: https://landing-page-nur-811a.vercel.app/blog/facebook-marketing\n- Google Ads: https://landing-page-nur-811a.vercel.app/blog/google-ads-basics\n- Personal branding and profile: https://landing-page-nur-811a.vercel.app/blog/nur-muhammad-personal-branding\n\n## Services\nSocial media marketing, paid advertising, SEO and content, marketing strategy, lead generation, analytics and reporting.\n\n## Contact\n- WhatsApp: https://wa.me/8801314819285\n- Facebook: https://www.facebook.com/share/1FBv45rXjp/\n- Instagram: https://www.instagram.com/km_nurmuhammad_/\n- LinkedIn: https://www.linkedin.com/in/nur-muhammad-464158443\n";
    exit;
}

if ($path === '/dashboard' || $path === '/dashboard/') {
    require dirname(__DIR__) . '/dashboard.php';
    exit;
}

// The catch-all Vercel route sends asset requests through this router too.
if (preg_match('~^/assets/(nur-muhammad(?:-personal-branding)?)\.jpg$~', $path, $matches) === 1) {
    $assetPath = dirname(__DIR__) . '/assets/' . $matches[1] . '.jpg';
    if (!is_file($assetPath)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    readfile($assetPath);
    exit;
}

if (preg_match('~^/blog/([a-z0-9-]+)/cover\.(svg|jpg)$~', $path, $matches) === 1) {
    $posts = require dirname(__DIR__) . '/blog-data.php';
    $slug = $matches[1];
    if (!isset($posts[$slug])) {
        http_response_code(404);
        exit;
    }
    header('Cache-Control: public, max-age=86400');
    if ($matches[2] === 'jpg' && isset($posts[$slug]['cover_file'])) {
        header('Content-Type: image/jpeg');
        readfile(dirname(__DIR__) . '/assets/' . basename($posts[$slug]['cover_file']));
    } elseif ($matches[2] === 'svg' && isset($posts[$slug]['cover_svg'])) {
        header('Content-Type: image/svg+xml; charset=utf-8');
        echo $posts[$slug]['cover_svg'];
    } else {
        http_response_code(404);
    }
    exit;
}

if ($path === '/blog' || $path === '/blog/') {
    require dirname(__DIR__) . '/blog-index.php';
    exit;
}

if ($path === '/blog/digital-marketing-basics' || $path === '/blog/digital-marketing-basics/') {
    require dirname(__DIR__) . '/blog.php';
    exit;
}

$posts = require dirname(__DIR__) . '/blog-data.php';
if (preg_match('~^/blog/([a-z0-9-]+)/?$~', $path, $matches) === 1 && isset($posts[$matches[1]])) {
    $article = $posts[$matches[1]];
    require dirname(__DIR__) . '/blog-post-template.php';
    exit;
}

require dirname(__DIR__) . '/index.php';
