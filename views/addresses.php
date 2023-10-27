<?php
// *** Check user authority ***
if ($data["authorised"] != '') {
    echo $data["authorised"];
    exit();
}

$path_form = $link_cls->get_link($uri_path, 'addresses', $tree_id);
$path = $link_cls->get_link($uri_path, 'addresses', $tree_id, true);
?>

<h1 style="text-align:center;"><?= __('Addresses'); ?></h1>
<div>
    <!-- *** Search form *** -->
    <form method="POST" action="<?= $path_form; ?>" style="display : inline;">
        <table class="humo" style="margin-left:auto;margin-right:auto">
            <tr class="table_headline">
                <td><?= __('City'); ?> <input type="text" name="adr_place" size="15"></td>
                <td><?= __('Street'); ?> <input type="text" name="adr_address" size="15"></td>
                <!-- TODO check database variable -->
                <input type="hidden" name="database" value="<?= $database; ?>">
                <td><input type="submit" value="<?= __('Search'); ?>" name="search_addresses"></td>
            </tr>
        </table><br>
    </form>

    <!-- *** Show results *** -->
    <table class="humo" style="margin-left:auto;margin-right:auto">
        <tr class="table_headline">
            <th><a href="<?= $path; ?><?= $data["place_link"]; ?>" <?php if ($data["select_sort"] == 'sort_place') echo ' style="background-color:#ffffa0"'; ?>><?= __('City'); ?> <img src="<?= $data["place_image"]; ?>"></a></th>
            <th><a href="<?= $path; ?><?= $data["address_link"]; ?>" <?php if ($data["select_sort"] == 'sort_address') echo ' style="background-color:#ffffa0"'; ?>><?= __('Street'); ?> <img src="<?= $data["address_image"]; ?>"></a></th>
            <th><?= __('Text'); ?></th>
        </tr>

        <?php foreach ($data["addresses"] as $addressDb) { ?>
            <tr>
                <td style="padding-left:5px;padding-right:5px">
                    <?php if ($addressDb->address_place != '') echo $addressDb->address_place; ?>
                </td>

                <td style="padding-left:5px;padding-right:5px">
                    <?php
                    if ($addressDb->address_address != '') {
                        // TODO use function to get link.
                        if ($humo_option["url_rewrite"] == "j") {
                            echo '<a href="address/' . $tree_id . '/' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';
                        } else {
                            echo '<a href="index.php?page=address&amp;tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';
                        }
                    }
                    ?>
                </td>

                <td>
                    <?= substr($addressDb->address_text, 0, 40); ?>
                    <?php if (strlen($addressDb->address_text) > 40) echo '...'; ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>