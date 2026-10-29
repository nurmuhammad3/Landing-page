<?php
declare(strict_types=1);

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$canonical = 'https://landing-page-nur-811a.vercel.app/blog/' . $article['slug'];
$coverExtension = isset($article['cover_file']) ? 'jpg' : 'svg';
$coverUrl = $canonical . '/cover.' . $coverExtension;
$publishedAt = '2026-10-29T10:00:00+06:00';
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $article['title_bn'],
    'description' => $article['description_bn'],
    'image' => [$coverUrl],
    'datePublished' => $publishedAt,
    'dateModified' => $publishedAt,
    'author' => [
        '@type' => 'Person',
        'name' => 'Nur Muhammad',
        'url' => 'https://landing-page-nur-811a.vercel.app/#about',
        'sameAs' => [
            'https://www.facebook.com/share/1FBv45rXjp/',
            'https://www.instagram.com/km_nurmuhammad_/',
            'https://www.linkedin.com/in/nur-muhammad-464158443',
        ],
    ],
    'publisher' => [
        '@type' => 'Person',
        'name' => 'Nur Muhammad',
        'url' => 'https://landing-page-nur-811a.vercel.app/',
    ],
    'mainEntityOfPage' => $canonical,
];
$jsonLd = json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= $escape($article['description_bn']) ?>">
  <meta name="theme-color" content="#f7f8f4">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <link rel="canonical" href="<?= $escape($canonical) ?>">
  <meta property="og:type" content="article">
  <meta property="og:title" content="<?= $escape($article['title_bn']) ?>">
  <meta property="og:description" content="<?= $escape($article['description_bn']) ?>">
  <meta property="og:url" content="<?= $escape($canonical) ?>">
  <meta property="og:image" content="<?= $escape($coverUrl) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <title><?= $escape($article['title_bn']) ?> | Nur Muhammad</title>
  <script type="application/ld+json"><?= $jsonLd ?></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{--ink:#17251f;--muted:#68756e;--green:#246b4b;--lime:#d8f078;--cream:#f7f8f4;--line:#e5e9e1;--white:#fff}
    *{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.8 "Hind Siliguri",sans-serif}html[lang="en"] body{font-family:"DM Sans",sans-serif}a{color:inherit;text-decoration:none}.container{width:min(1050px,calc(100% - 40px));margin:auto}.nav-wrap{background:rgba(247,248,244,.94);border-bottom:1px solid var(--line)}nav{height:72px;display:flex;align-items:center;justify-content:space-between;gap:16px}.brand{display:flex;align-items:center;gap:10px;font:700 17px "DM Sans",sans-serif}.brand-mark{display:grid;place-items:center;width:34px;height:34px;background:var(--green);border-radius:11px;color:#fff}.nav-tools{display:flex;align-items:center;gap:12px}.back-link{font-weight:600;color:var(--green)}.lang-toggle{border:1px solid #cbd5cc;background:transparent;color:var(--ink);padding:8px 12px;border-radius:8px;font-weight:700;cursor:pointer}.article{max-width:850px;margin:0 auto;padding:46px 0 76px}.crumb{font-size:14px;color:var(--muted);margin-bottom:26px}.crumb a{color:var(--green);font-weight:600}.category{display:inline-block;padding:5px 11px;border-radius:20px;background:#e7efe5;color:var(--green);font:700 11px "DM Sans",sans-serif;letter-spacing:.8px;text-transform:uppercase}.article h1{font-size:clamp(34px,6vw,57px);line-height:1.16;letter-spacing:-1.4px;margin:18px 0 15px}.meta{display:flex;align-items:center;gap:13px;color:var(--muted);font-size:14px;margin-bottom:28px}.author-dot{width:37px;height:37px;border-radius:50%;display:grid;place-items:center;background:var(--green);color:white;font-weight:700}.cover{margin:0 0 34px;border-radius:18px;overflow:hidden;background:#e7eee5;border:1px solid var(--line)}.cover img{display:block;width:100%;height:auto}.intro{font-size:19px;color:#425149}.article h2{font-size:25px;line-height:1.35;margin:30px 0 8px}.article p,.article li{color:#526159}.sources{margin-top:40px;padding-top:22px;border-top:1px solid var(--line)}.sources h2{font-size:20px;margin:0 0 12px}.sources ul{padding-left:22px}.sources a{color:var(--green);text-decoration:underline;text-underline-offset:3px}.article-cta{margin-top:44px;padding:28px;border-radius:14px;background:var(--lime);display:flex;align-items:center;justify-content:space-between;gap:20px}.article-cta h2{margin:0 0 4px}.article-cta p{margin:0;color:#4b5a34}.button{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 17px;border-radius:8px;background:var(--green);color:white;font-weight:700;white-space:nowrap}.foot{border-top:1px solid var(--line);padding:20px 0;color:var(--muted);font-size:13px}.foot .container{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap}
    @media(max-width:620px){.container{width:calc(100% - 32px)}nav{height:64px}.article{padding:32px 0 52px}.article h1{letter-spacing:-.8px}.intro{font-size:17px}.article-cta{align-items:flex-start;flex-direction:column}.article-cta .button{width:100%}.meta{margin-bottom:20px}}
  </style>
</head>
<body>
  <div class="nav-wrap"><nav class="container" aria-label="Main navigation"><a class="brand" href="/"><span class="brand-mark">↗</span><span>Nur Muhammad<span style="color:var(--green)">.</span></span></a><div class="nav-tools"><a class="back-link" href="/blog" data-bn="← সব ব্লগ" data-en="← All posts">← সব ব্লগ</a><button class="lang-toggle" id="langToggle" type="button" aria-label="Switch language">EN</button></div></nav></div>
  <main class="container"><article class="article" itemscope itemtype="https://schema.org/BlogPosting">
    <div class="crumb"><a href="/" data-bn="হোম" data-en="Home">হোম</a> / <a href="/blog" data-bn="ব্লগ" data-en="Blog">ব্লগ</a></div>
    <span class="category" data-bn="<?= $escape($article['category_bn']) ?>" data-en="<?= $escape($article['category_en']) ?>"><?= $escape($article['category_bn']) ?></span>
    <h1 itemprop="headline" data-bn="<?= $escape($article['title_bn']) ?>" data-en="<?= $escape($article['title_en']) ?>"><?= $escape($article['title_bn']) ?></h1>
    <div class="meta"><span class="author-dot">N</span><span><strong itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name">Nur Muhammad</span></strong><br><span data-bn="প্রথম প্রকাশ: ২৯ অক্টোবর ২০২৬ · ৫ মিনিটে পড়ুন" data-en="Published: October 29, 2026 · 5 min read">প্রথম প্রকাশ: ২৯ অক্টোবর ২০২৬ · ৫ মিনিটে পড়ুন</span></span></div>
    <figure class="cover"><img src="<?= $escape('/blog/' . $article['slug'] . '/cover.' . $coverExtension) ?>" alt="<?= $escape($article['cover_alt']) ?>" width="1000" height="560" fetchpriority="high"></figure>
    <p class="intro" data-bn="<?= $escape($article['intro_bn']) ?>" data-en="<?= $escape($article['intro_en']) ?>"><?= $escape($article['intro_bn']) ?></p>
    <?php foreach ($article['sections'] as $section): ?>
      <section><h2 data-bn="<?= $escape($section['heading_bn']) ?>" data-en="<?= $escape($section['heading_en']) ?>"><?= $escape($section['heading_bn']) ?></h2><p data-bn="<?= $escape($section['body_bn']) ?>" data-en="<?= $escape($section['body_en']) ?>"><?= $escape($section['body_bn']) ?></p></section>
    <?php endforeach; ?>
    <section class="sources"><h2 data-bn="আরও পড়ুন: মূল নির্দেশিকা" data-en="Further reading: official guides">আরও পড়ুন: মূল নির্দেশিকা</h2><ul><?php foreach ($article['sources'] as $source): ?><li><a href="<?= $escape($source['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $escape($source['label']) ?> ↗</a></li><?php endforeach; ?></ul></section>
    <aside class="article-cta"><div><h2 data-bn="আপনার মার্কেটিং লক্ষ্য নিয়ে কথা বলি?" data-en="Want to discuss your marketing goals?">আপনার মার্কেটিং লক্ষ্য নিয়ে কথা বলি?</h2><p data-bn="WhatsApp-এ Nur Muhammad-কে মেসেজ করুন।" data-en="Message Nur Muhammad on WhatsApp.">WhatsApp-এ Nur Muhammad-কে মেসেজ করুন।</p></div><a class="button" href="https://wa.me/8801314819285" target="_blank" rel="noopener noreferrer">WhatsApp · 01314819285 ↗</a></aside>
  </article></main>
  <footer class="foot"><div class="container"><span>© 2026 Nur Muhammad</span><a href="/" data-bn="হোমে ফিরে যান" data-en="Back to home">হোমে ফিরে যান</a></div></footer>
  <script>
    const button=document.querySelector('#langToggle');
    function setLanguage(language){document.documentElement.lang=language;document.querySelectorAll('[data-bn][data-en]').forEach(element=>{element.textContent=element.dataset[language];});button.textContent=language==='bn'?'EN':'বাংলা';button.setAttribute('aria-label',language==='bn'?'Switch to English':'বাংলায় দেখুন');try{localStorage.setItem('nur-site-language',language);}catch(error){}}
    let saved='bn';try{saved=localStorage.getItem('nur-site-language')||'bn';}catch(error){}setLanguage(saved);button.addEventListener('click',()=>setLanguage(document.documentElement.lang==='bn'?'en':'bn'));
  </script>
</body>
</html>
