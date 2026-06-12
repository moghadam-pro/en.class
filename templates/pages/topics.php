<?php
// templates/pages/topics.php
$activePage = 'topics';
$seoMeta = new \App\SEO\MetaBuilder();
ob_start();
echo $seoMeta->renderHead($meta);
$headContent = ob_get_clean();
$meta['head'] = $headContent;
$contentTemplate = 'pages/topics_content';
require ROOT_PATH . '/templates/layouts/base.php';
