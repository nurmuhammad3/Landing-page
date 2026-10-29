<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

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
    echo "# Nur Muhammad\n\n> Digital marketer sharing practical guides about SEO, Facebook marketing, and Google Ads.\n\n## Website\n- Home: https://landing-page-nur-811a.vercel.app/\n- About and services: https://landing-page-nur-811a.vercel.app/#about\n- Blog: https://landing-page-nur-811a.vercel.app/blog\n- Marketing dashboard: https://landing-page-nur-811a.vercel.app/dashboard (demonstration data)\n\n## Articles\n- Digital marketing for small businesses: https://landing-page-nur-811a.vercel.app/blog/digital-marketing-basics\n- SEO for small businesses: https://landing-page-nur-811a.vercel.app/blog/seo-for-small-business\n- Facebook marketing: https://landing-page-nur-811a.vercel.app/blog/facebook-marketing\n- Google Ads: https://landing-page-nur-811a.vercel.app/blog/google-ads-basics\n\n## Services\nSocial media marketing, paid advertising, SEO and content, marketing strategy, lead generation, analytics and reporting.\n\n## Contact\n- WhatsApp: https://wa.me/8801314819285\n- Facebook: https://www.facebook.com/share/1FBv45rXjp/\n- Instagram: https://www.instagram.com/km_nurmuhammad_/\n- LinkedIn: https://www.linkedin.com/in/nur-muhammad-464158443\n";
    exit;
}

if ($path === '/dashboard' || $path === '/dashboard/') {
    require dirname(__DIR__) . '/dashboard.php';
    exit;
}

if (preg_match('~^/blog/([a-z0-9-]+)/cover\.svg$~', $path, $matches) === 1) {
    $posts = require dirname(__DIR__) . '/blog-data.php';
    $slug = $matches[1];
    if (!isset($posts[$slug])) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: image/svg+xml; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    echo $posts[$slug]['cover_svg'];
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
