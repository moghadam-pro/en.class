<?php // templates/pages/topic_content.php
// This page opens as a slide-in detail view triggered by JS.
// The topic data is injected into the page via a <script> tag and
// FDC.openTopicBySlug() fetches it from the pre-loaded index.
// This file is included by base.php — the detail panel HTML is already
// in base.php as #topicDetail. We only need to seed the JS data here.
?>
<script>
// Full topic data for this page — used when navigating directly via URL
window.FDC_CURRENT_TOPIC = <?= json_encode($topic, JSON_UNESCAPED_UNICODE) ?>;
</script>
