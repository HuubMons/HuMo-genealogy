<?php
// *** Check user authority ***
if ($data["authorised"] != '') {
    echo $data["authorised"];
    exit();
}
?>

<table class="humo standard">
    <tr>
        <td>
            <h2><?php echo __('Address'); ?></h2>
            <?php //echo $data["title"]; 
            ?>

            <?php
            if ($data["address"]->address_address) {
                echo '<b>' . __('Street') . ':</b> ' . $data["address"]->address_address . '<br>';
            }
            if ($data["address"]->address_zip) {
                echo '<b>' . __('Zip code') . ':</b> ' . $data["address"]->address_zip . '<br>';
            }
            if ($data["address"]->address_place) {
                echo '<b>' . __('Place') . ':</b> ' . $data["address"]->address_place . '<br>';
            }
            if ($data["address"]->address_phone) {
                echo '<b>' . __('Phone') . ':</b>' . $data["address"]->address_phone . '<br>';
            }
            if ($data["address"]->address_text) { ?>
        </td>
    </tr>
    <tr>
        <td>
        <?= nl2br($data["address"]->address_text);
            }

            // *** show pictures by address here ? ***

            // *** Show source by addresss ***
            if ($data["address_sources"]) { ?>
        </td>
    </tr>
    <tr>
        <td>
            <?= '<b>' . __('Source') . ' ' . $data["address_sources"]; ?>
        <?php
            }

            if ($data["address_connected_persons"]) { ?>
        </td>
    </tr>
    <tr>
        <td>
            <!-- *** Show persons connected to address *** -->
            <?= $data["address_connected_persons"]; ?>
        <?php } ?>
        </td>
    </tr>
</table>

<?php
// *** If source footnotes are selected, show them here ***
if (isset($_SESSION['save_source_presentation']) && $_SESSION['save_source_presentation'] == 'footnote') {
    echo show_sources_footnotes();
}
