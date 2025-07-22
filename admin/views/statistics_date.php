<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<div class="center">
    <br>
    <form method="POST" action="index.php?page=statistics&amp;tab=date_statistics" style="display : inline;">
        <select size='1' name='month'>
            <option value="1" <?php if ($statistics['month'] == '1') echo ' selected'; ?>><?= __('January'); ?></option>
            <option value="2" <?php if ($statistics['month'] == '2') echo ' selected'; ?>><?= __('February'); ?></option>
            <option value="3" <?php if ($statistics['month'] == '3') echo ' selected'; ?>><?= __('March'); ?></option>
            <option value="4" <?php if ($statistics['month'] == '4') echo ' selected'; ?>><?= __('April'); ?></option>
            <option value="5" <?php if ($statistics['month'] == '5') echo ' selected'; ?>><?= __('May'); ?></option>
            <option value="6" <?php if ($statistics['month'] == '6') echo ' selected'; ?>><?= __('June'); ?></option>
            <option value="7" <?php if ($statistics['month'] == '7') echo ' selected'; ?>><?= __('July'); ?></option>
            <option value="8" <?php if ($statistics['month'] == '8') echo ' selected'; ?>><?= __('August'); ?></option>
            <option value="9" <?php if ($statistics['month'] == '9') echo ' selected'; ?>><?= __('September'); ?></option>
            <option value="10" <?php if ($statistics['month'] == '10') echo ' selected'; ?>><?= __('October'); ?></option>
            <option value="11" <?php if ($statistics['month'] == '11') echo ' selected'; ?>><?= __('November'); ?></option>
            <option value="12" <?php if ($statistics['month'] == '12') echo ' selected'; ?>><?= __('December'); ?></option>
        </select>

        <!-- Select year -->
        <select size='1' name='year'>
            <?php for ($year_select = $statistics['first_year']; $year_select <= $statistics['present_year']; $year_select++) { ?>
                <option value="<?= $year_select; ?>" <?= $statistics['year'] == $year_select ? 'selected' : ''; ?>><?= $year_select; ?></option>
            <?php } ?>
        </select>
        <input type="submit" name="submit" value="<?= __('Select'); ?>" class="btn btn-sm btn-success">
    </form><br><br>

    <b><?= __('Total number of visited families:'); ?></b><br>
</div><br>

<?php
// Graphic present month
if ($statistics['month'] == $statistics['present_month'] && $statistics['year'] == $statistics['present_year']) {
    calender($statistics['month'], $statistics['year'], true);
} else {
    calender($statistics['month'], $statistics['year'], false);
}

// Graphic year
echo '<br>';
year_graphics($statistics['month'], $statistics['year']);
