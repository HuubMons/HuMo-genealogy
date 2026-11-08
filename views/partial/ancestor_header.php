<?php
$htmlEscaper = new \Genealogy\Include\HtmlEscaper();
$ancestorHeader = $data['ancestor_header'];
?>
<h1><?= $htmlEscaper->escHtml(__('Ancestors')); ?></h1>
<ul class="nav nav-tabs d-print-none">
    <?php foreach ($ancestorHeader['header_text'] as $key => $headerText) { ?>
        <li class="nav-item me-1">
            <a class="nav-link genealogy_nav-link <?= $htmlEscaper->escHtml($ancestorHeader['header_active'][$key]); ?>"
                href="<?= $htmlEscaper->escHtml($ancestorHeader['header_link'][$key]); ?>" rel="nofollow">
                <?= $htmlEscaper->escHtml($headerText); ?>
            </a>
        </li>
    <?php } ?>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; padding:10px;">