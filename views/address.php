<?php
// *** Check user authority ***
if ($data["authorised"] != '') {
    echo $HtmlEscaper->escHtml((string) $data["authorised"]);
    exit();
}

$showSourcesFootnotes = new Genealogy\Include\ShowSourcesFootnotes();
?>

<table class="table">
    <tr>
        <td>
            <h2><?= __('Address'); ?></h2>

            <?php if ($data["address"]->address_address) { ?>
                <b><?= __('Street'); ?>:</b> <?= $HtmlEscaper->escHtml($data["address"]->address_address); ?><br>
            <?php
            }
            if ($data["address"]->address_zip) {
            ?>
                <b><?= __('Zip code'); ?>:</b> <?= $HtmlEscaper->escHtml($data["address"]->address_zip); ?><br>
            <?php
            }
            if ($data["address"]->address_place) {
            ?>
                <b><?= __('Place'); ?>:</b> <?= $HtmlEscaper->escHtml($data["address"]->address_place); ?><br>
            <?php
            }
            if ($data["address"]->address_phone) {
            ?>
                <b><?= __('Phone'); ?>:</b> <?= $HtmlEscaper->escHtml($data["address"]->address_phone); ?><br>
            <?php
            } ?>
        </td>
    </tr>

    <?php if ($data["address"]->address_text) { ?>
        <tr>
            <td>
                <?= nl2br($HtmlEscaper->escHtml($data["address"]->address_text)); ?>
            </td>
        </tr>
    <?php } ?>

    <!-- show pictures by address here ? -->

    <?php if ($data["address_sources"]) { ?>
        <tr>
            <td>
                <?= '<b>' . __('Source') . ' ' . $data["address_sources"]; ?>
            </td>
        </tr>
    <?php
    } ?>

    <?php if ($data["address_connected_persons"]) { ?>
        <tr>
            <td>
                <!-- *** Show persons connected to address *** -->
                <?= $data["address_connected_persons"]; ?>
            </td>
        </tr>
    <?php } ?>

</table>

<?php
// *** If source footnotes are selected, show them here ***
if (isset($_SESSION['save_source_presentation']) && $_SESSION['save_source_presentation'] == 'footnote') {
    echo $showSourcesFootnotes->show_sources_footnotes();
}
