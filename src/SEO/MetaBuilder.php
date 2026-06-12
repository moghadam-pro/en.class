<?php
// src/SEO/MetaBuilder.php

namespace App\SEO;

class MetaBuilder
{
    private array $cfg;

    public function __construct()
    {
        $this->cfg = config('seo');
    }

    public function forHome(): array
    {
        $app = config('app');
        return [
            'title'        => $app['name'] . ' — ' . $app['tagline'],
            'description'  => $this->cfg['default_description'],
            'canonical'    => $app['url'] . '/',
            'og_title'     => $app['name'],
            'og_type'      => 'website',
            'og_image'     => $app['url'] . $this->cfg['default_image'],
            'og_url'       => $app['url'] . '/',
            'breadcrumbs'  => [['name' => 'Home', 'url' => $app['url'] . '/']],
        ];
    }

    public function forTopic(array $topic): array
    {
        $app   = config('app');
        $url   = $app['url'] . '/topic/' . $topic['slug'];
        $title = $topic['title'] . ' — Discussion Questions | ' . $app['name'];
        $desc  = truncate($topic['summary'] ?? $this->cfg['default_description'], 160);
        $image = !empty($topic['cover']) ? $app['url'] . $topic['cover'] : $app['url'] . $this->cfg['default_image'];

        $faqs = [];
        foreach (array_slice($topic['questions'] ?? [], 0, 5) as $q) {
            $text = is_array($q) ? ($q['text'] ?? '') : $q;
            if ($text) $faqs[] = ['question' => $text, 'answer' => 'Discuss this in class with your teacher and classmates.'];
        }

        return [
            'title'        => $title,
            'description'  => $desc,
            'canonical'    => $url,
            'og_title'     => $topic['title'] . ' — Discussion Questions',
            'og_type'      => 'article',
            'og_image'     => $image,
            'og_url'       => $url,
            'og_tags'      => $topic['tags'] ?? [],
            'level'        => $topic['level'] ?? '',
            'faq_schema'   => $faqs,
            'breadcrumbs'  => [
                ['name' => 'Home', 'url' => $app['url'] . '/'],
                ['name' => 'Topics', 'url' => $app['url'] . '/topics'],
                ['name' => $topic['title'], 'url' => $url],
            ],
        ];
    }

    public function forTopics(array $filters = []): array
    {
        $app   = config('app');
        $title = 'All Discussion Topics';
        if (!empty($filters['level'])) $title = $filters['level'] . ' Topics';
        if (!empty($filters['tag']))   $title = '"' . $filters['tag'] . '" Topics';
        if (!empty($filters['q']))     $title = 'Search: ' . $filters['q'];

        return [
            'title'       => $title . ' | ' . $app['name'],
            'description' => 'Browse all ESL discussion topics. ' . $this->cfg['default_description'],
            'canonical'   => $app['url'] . '/topics',
            'og_title'    => $title,
            'og_type'     => 'website',
            'og_image'    => $app['url'] . $this->cfg['default_image'],
            'og_url'      => $app['url'] . '/topics',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $app['url'] . '/'],
                ['name' => 'Topics', 'url' => $app['url'] . '/topics'],
            ],
        ];
    }

    public function renderHead(array $meta): string
    {
        $app = config('app');
        ob_start();
        ?>
<title><?= e($meta['title']) ?></title>
<meta name="description" content="<?= e($meta['description']) ?>">
<link rel="canonical" href="<?= e($meta['canonical']) ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?= e($meta['og_title'] ?? $meta['title']) ?>">
<meta property="og:description" content="<?= e($meta['description']) ?>">
<meta property="og:type" content="<?= e($meta['og_type'] ?? 'website') ?>">
<meta property="og:url" content="<?= e($meta['og_url'] ?? $meta['canonical']) ?>">
<meta property="og:image" content="<?= e($meta['og_image'] ?? '') ?>">
<meta property="og:site_name" content="<?= e($app['name']) ?>">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="<?= e($this->cfg['twitter_handle']) ?>">
<meta name="twitter:title" content="<?= e($meta['og_title'] ?? $meta['title']) ?>">
<meta name="twitter:description" content="<?= e($meta['description']) ?>">
<meta name="twitter:image" content="<?= e($meta['og_image'] ?? '') ?>">

<?php if (!empty($meta['breadcrumbs'])): ?>
<!-- Breadcrumb Schema -->
<script type="application/ld+json">
<?= json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => array_map(fn($b, $i) => [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $b['name'],
        'item'     => $b['url'],
    ], $meta['breadcrumbs'], array_keys($meta['breadcrumbs'])),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php endif; ?>

<?php if (!empty($meta['faq_schema'])): ?>
<!-- FAQ Schema -->
<script type="application/ld+json">
<?= json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($faq) => [
        '@type'          => 'Question',
        'name'           => $faq['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
    ], $meta['faq_schema']),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php endif; ?>
        <?php
        return ob_get_clean();
    }

    public function generateSitemap(array $slugs): string
    {
        $app  = config('app');
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $staticUrls = [
            ['loc' => $app['url'] . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $app['url'] . '/topics', 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        foreach ($staticUrls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$u['loc']}</loc>\n";
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        foreach ($slugs as $slug) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$app['url']}/topic/{$slug}</loc>\n";
            $xml .= "    <changefreq>monthly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }
}
