<?php
// templates/pages/home.php
$activePage = 'home';
$seoMeta = new \App\SEO\MetaBuilder();
ob_start();
?>
<?= $seoMeta->renderHead($meta) ?>
<?php $headContent = ob_get_clean(); ?>
<?php
$meta['head'] = $headContent;
$contentTemplate = 'pages/home_content';
require ROOT_PATH . '/templates/layouts/base.php';
