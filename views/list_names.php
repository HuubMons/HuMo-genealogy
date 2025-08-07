<!-- Show line of first character last names -->
<div style="text-align:center" class="mt-2">
    <?php
    foreach ($list_names["alphabet_array"] as $alphabet) {
        $vars['last_name'] = $alphabet;
        $link = $processLinks->get_link($uri_path, 'list_names', $tree_id, false, $vars);
    ?>
        <a href="<?= $link; ?>"><?= $alphabet; ?></a>
    <?php
    }

    $vars['last_name'] = 'all';
    $link = $processLinks->get_link($uri_path, 'list_names', $tree_id, false, $vars);
    ?>
    <a href="<?= $link; ?>"><?= __('All names'); ?></a>
</div>

<!-- Show options line -->
<?php
if ($humo_option["url_rewrite"] == "j") {
    $url = $uri_path . 'list_names/' . $tree_id . '/' . $list_names["last_name"];
} else {
    $url = 'index.php?page=list_names&amp;tree_id=' . $tree_id . '&amp;last_name=' . $list_names["last_name"];
}
?>
<form method="POST" action="<?= $url; ?>" style="display:inline;" id="frqnames">
    <div class="row mb-3 me-1 mt-3">
        <div class="col-sm-3"></div>
        <div class="col-sm-3">
            <select size=1 name="freqsurnames" class="form-select form-select-sm" onChange="this.form.submit();">
                <option><?= __('Number of displayed surnames'); ?></option>
                <option value="25">25</option>
                <option value="51">50</option> <!-- 51 so no empty last field (if more names than this) -->
                <option value="75">75</option>
                <option value="100">100</option>
                <option value="201">200</option> <!-- 201 so no empty last field (if more names than this) -->
                <option value="300">300</option>
                <option value="999"><?= __('All'); ?></option>
            </select>
        </div>
        <div class="col-sm-3">
            <select size=1 name="maxcols" class="form-select form-select-sm" onChange="this.form.submit();">
                <option><?= __('Number of columns'); ?></option>
                <?php for ($i = 1; $i < 7; $i++) { ?>
                    <option value="<?= $i; ?>"><?= $i; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-3"></div>
    </div>
</form>

<?php if ($list_names['show_pagination']) { ?>
    <div style="text-align:center">
        <?php $data = $list_names; ?>
        <?php include __DIR__ . '/partial/pagination.php'; ?>
    </div>
<?php } ?>

<?php
$col_width = ((round(100 / $list_names["max_cols"])) - 6) . "%";
$path_tmp = $processLinks->get_link($uri_path, 'list', $tree_id, true);
?>
<table class="table table-sm nametbl">
    <thead class="table-primary">
        <tr>
            <?php for ($x = 1; $x < $list_names["max_cols"]; $x++) { ?>
                <th width="<?= $col_width; ?>"><?= __('Name'); ?></th>
                <th style="text-align:center;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
            <?php } ?>
            <th width="<?= $col_width; ?>"><?= __('Name'); ?></th>
            <th style="text-align:center;width:6%"><?= __('Total'); ?></th>
        </tr>
    </thead>

    <?php if (isset($list_names['row'])) { ?>
        <?php for ($i = 0; $i < $list_names['row']; $i++) { ?>
            <tr>
                <?php
                // *** Show names in columns and rows ***
                for ($n = 0; $n < $list_names["max_cols"]; $n++) {
                    $nr = $i + ($list_names['row'] * $n);
                ?>
                    <td class="namelst">
                        <?php if (isset($list_names['link_name'][$nr])) { ?>
                            <a href="<?= $path_tmp; ?>pers_lastname=<?= $list_names['link_name'][$nr]; ?>&amp;part_lastname=equals">
                                <?= $list_names['show_name'][$nr]; ?>
                            </a>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>

                    <td class="namenr" style="text-align:center">
                        <?= isset($list_names['show_name'][$nr]) ? $list_names['freq_count_last_names'][$nr] : '-'; ?>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    <?php } ?>
</table>

<!--Show gray bar in name box. Graphical indication of number of names -->
<script>
    var baseperc = <?= (int)$list_names['number_high']; ?>;
</script>
<script src="assets/js/stats_graphical_bar.js"></script>