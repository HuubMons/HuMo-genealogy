<?php
$htmlEscaper = new \Genealogy\Include\HtmlEscaper();
$descendantHeader = $data['descendant_header'];
?>
<h1><?= $htmlEscaper->escHtml(__('Descendants')); ?></h1>
<ul class="nav nav-tabs d-print-none" id="nav-tab">
    <?php foreach ($descendantHeader['header_text'] as $key => $headerText) { ?>
        <li class="nav-item me-1">
            <a class="nav-link genealogy_nav-link <?= $htmlEscaper->escHtml($descendantHeader['header_active'][$key]); ?>"
                href="<?= $htmlEscaper->escHtml($descendantHeader['header_link'][$key]); ?>" rel="nofollow">
                <?= $htmlEscaper->escHtml($headerText); ?>
            </a>
        </li>
    <?php } ?>
</ul>
<!-- Align content to the left -->
<div style="float: left; background-color:white; padding:10px;">
