<?php
declare(strict_types=1);

$posts = require __DIR__ . '/blog-data.php';
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$firstPost = [
    'url' => '/blog/digital-marketing-basics',
    'category_bn' => 'ডিজিটাল মার্কেটিং', 'category_en' => 'Digital Marketing',
    'title_bn' => 'ছোট ব্যবসার ডিজিটাল মার্কেটিং: শুরু করবেন যেভাবে',
    'title_en' => 'Digital marketing for small businesses: where to start',
    'description_bn' => 'লক্ষ্য ঠিক করা থেকে ফলাফল মাপা—ডিজিটাল মার্কেটিং শুরুর পাঁচটি ব্যবহারিক ধাপ।',
    'description_en' => 'Five practical steps to start digital marketing, from choosing a goal to measuring results.',
];
$cards = [$firstPost];
foreach ($posts as $post) {
    $cards[] = $post + ['url' => '/blog/' . $post['slug']];
}
?>
<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="SEO, Facebook marketing, Google Ads and practical digital marketing guides by Nur Muhammad.">
  <meta name="theme-color" content="#f7f8f4">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <link rel="canonical" href="https://landing-page-nur-811a.vercel.app/blog">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Digital Marketing Blog | Nur Muhammad">
  <meta property="og:description" content="Practical SEO, Facebook marketing, Google Ads, and digital marketing guides.">
  <meta property="og:url" content="https://landing-page-nur-811a.vercel.app/blog">
  <title>ডিজিটাল মার্কেটিং ব্লগ | Nur Muhammad</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{--ink:#17251f;--muted:#68756e;--green:#246b4b;--lime:#d8f078;--cream:#f7f8f4;--line:#e5e9e1;--white:#fff}*{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.75 "Hind Siliguri",sans-serif}html[lang="en"] body{font-family:"DM Sans",sans-serif}a{color:inherit;text-decoration:none}.container{width:min(1120px,calc(100% - 40px));margin:auto}.nav-wrap{background:rgba(247,248,244,.94);border-bottom:1px solid var(--line)}nav{height:72px;display:flex;align-items:center;justify-content:space-between;gap:16px}.brand{display:flex;align-items:center;gap:10px;font:700 17px "DM Sans",sans-serif}.brand-mark{display:grid;place-items:center;width:34px;height:34px;background:var(--green);border-radius:11px;color:#fff}.nav-tools{display:flex;align-items:center;gap:14px}.home-link{color:var(--green);font-weight:600}.lang-toggle{border:1px solid #cbd5cc;background:transparent;color:var(--ink);padding:8px 12px;border-radius:8px;font-weight:700;cursor:pointer}.head{max-width:730px;padding:64px 0 34px}.eyebrow{color:var(--green);font:700 12px "DM Sans",sans-serif;letter-spacing:1px;text-transform:uppercase}.head h1{font-size:clamp(36px,5vw,56px);line-height:1.18;margin:12px 0}.head p{font-size:18px;color:var(--muted);margin:0}.posts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;padding-bottom:76px}.post{overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:15px;transition:.2s}.post:hover{transform:translateY(-3px);box-shadow:0 14px 35px #243a1a0c}.post-cover{width:100%;aspect-ratio:16/8.5;object-fit:cover;background:#e8efe5;display:block}.post-body{padding:22px}.category{font:700 11px "DM Sans",sans-serif;letter-spacing:.7px;text-transform:uppercase;color:var(--green)}.post h2{font-size:23px;line-height:1.35;margin:8px 0}.post p{margin:0;color:var(--muted)}.read-link{display:inline-flex;margin-top:16px;color:var(--green);font-weight:700}.footer{border-top:1px solid var(--line);padding:20px 0;color:var(--muted);font-size:13px}.footer .container{display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap}@media(max-width:650px){.container{width:calc(100% - 32px)}nav{height:64px}.head{padding:44px 0 25px}.head p{font-size:16px}.posts{grid-template-columns:1fr;gap:15px}.post-body{padding:18px}.post h2{font-size:21px}}
  </style>
</head>
<body>
  <div class="nav-wrap"><nav class="container" aria-label="Main navigation"><a class="brand" href="/"><span class="brand-mark">↗</span><span>Nur Muhammad<span style="color:var(--green)">.</span></span></a><div class="nav-tools"><a class="home-link" href="/" data-bn="← হোম" data-en="← Home">← হোম</a><button class="lang-toggle" id="langToggle" type="button" aria-label="Switch language">EN</button></div></nav></div>
  <main class="container"><header class="head"><span class="eyebrow" data-bn="নুর মুহাম্মদের ব্লগ" data-en="Nur Muhammad’s blog">নুর মুহাম্মদের ব্লগ</span><h1 data-bn="ডিজিটাল মার্কেটিং—সহজ, বাস্তব, পরিষ্কার" data-en="Digital marketing—clear, practical, useful">ডিজিটাল মার্কেটিং—সহজ, বাস্তব, পরিষ্কার</h1><p data-bn="SEO, Facebook Marketing ও Google Ads নিয়ে ছোট ব্যবসার জন্য ব্যবহারিক গাইড।" data-en="Practical guides to SEO, Facebook marketing, and Google Ads for small businesses.">SEO, Facebook Marketing ও Google Ads নিয়ে ছোট ব্যবসার জন্য ব্যবহারিক গাইড।</p></header>
    <section class="posts" aria-label="Blog posts">
      <?php foreach ($cards as $card): ?>
        <article class="post">
          <?php if (isset($card['slug'])): ?><img class="post-cover" src="<?= $escape('/blog/' . $card['slug'] . '/cover.' . (isset($card['cover_file']) ? 'jpg' : 'svg')) ?>" alt="<?= $escape($card['cover_alt']) ?>" width="1000" height="560" loading="lazy"><?php else: ?><div class="post-cover" aria-hidden="true" style="display:grid;place-items:center;color:#246b4b;font-size:52px">↗</div><?php endif; ?>
          <div class="post-body"><span class="category" data-bn="<?= $escape($card['category_bn']) ?>" data-en="<?= $escape($card['category_en']) ?>"><?= $escape($card['category_bn']) ?></span><h2 data-bn="<?= $escape($card['title_bn']) ?>" data-en="<?= $escape($card['title_en']) ?>"><?= $escape($card['title_bn']) ?></h2><p data-bn="<?= $escape($card['description_bn']) ?>" data-en="<?= $escape($card['description_en']) ?>"><?= $escape($card['description_bn']) ?></p><a class="read-link" href="<?= $escape($card['url']) ?>"><span data-bn="পোস্ট পড়ুন" data-en="Read post">পোস্ট পড়ুন</span> ↗</a></div>
        </article>
      <?php endforeach; ?>
    </section>
  </main>
  <footer class="footer"><div class="container"><span>© 2026 Nur Muhammad</span><a href="https://wa.me/8801314819285" target="_blank" rel="noopener noreferrer">WhatsApp · 01314819285</a></div></footer>
  <script>
    const button=document.querySelector('#langToggle');function setLanguage(language){document.documentElement.lang=language;document.querySelectorAll('[data-bn][data-en]').forEach(element=>{element.textContent=element.dataset[language];});button.textContent=language==='bn'?'EN':'বাংলা';button.setAttribute('aria-label',language==='bn'?'Switch to English':'বাংলায় দেখুন');try{localStorage.setItem('nur-site-language',language);}catch(error){}}let saved='bn';try{saved=localStorage.getItem('nur-site-language')||'bn';}catch(error){}setLanguage(saved);button.addEventListener('click',()=>setLanguage(document.documentElement.lang==='bn'?'en':'bn'));
  </script>
</body>
</html>
