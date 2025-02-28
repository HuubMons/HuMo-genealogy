<div class="center">
    <br>
    <form method="POST" action="index.php" style="display : inline;">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="tab" value="visitors">
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

        <!-- Selection of year -->
        <select size="1" name="year">
            <?php for ($year_select = $statistics['first_year']; $year_select <= $statistics['present_year']; $year_select++) { ?>
                <option value="<?= $year_select; ?>" <?= $statistics['year'] == $year_select ? 'selected' : ''; ?>>
                    <?= $year_select; ?>
                </option>
            <?php } ?>
        </select>

        <input type="submit" name="submit" value="<?= __('Select'); ?>" class="btn btn-sm btn-success">
    </form><br><br>
    <b><?= __('Visitors'); ?></b><br>
</div><br>

<?php
// *** Show graphic of present month ***
if ($statistics['month'] == $statistics['present_month'] && $statistics['year'] == $statistics['present_year']) {
    calender($statistics['month'], $statistics['year'], true);
} else {
    calender($statistics['month'], $statistics['year'], false);
}

// *** Show year graphic ***
echo '<br>';
year_graphics($statistics['month'], $statistics['year']);
?>

<br><b><?= __('User agent information'); ?></b><br>
<div class="container">
    <?php
    // *** Show user agent info (50 most used user agents) ***
    $datasql = $dbh->query("SELECT stat_ip_address, stat_user_agent, count(humo_stat_date.stat_user_agent) as count_lines
        FROM humo_stat_date WHERE stat_user_agent LIKE '_%' GROUP BY humo_stat_date.stat_user_agent ORDER BY count_lines desc LIMIT 0,50");

    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
    ?>
        <div class="row mt-2">
            <div class="col-1">
                <b><?= $dataDb->count_lines; ?></b>
            </div>
            <div class="col-11">
                <?= $dataDb->stat_user_agent; ?>
            </div>
        </div>
    <?php } ?>
</div>

<br><b><?= __('Visitor IP addresses'); ?></b><br>
<div class="container">
    <?php
    // *** Show user agent info (50 most used user agents) ***
    $datasql = $dbh->query("SELECT stat_ip_address, count(humo_stat_date.stat_ip_address) as count_lines
        FROM humo_stat_date WHERE stat_ip_address LIKE '_%' GROUP BY humo_stat_date.stat_ip_address ORDER BY count_lines desc LIMIT 0,50");

    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
    ?>
        <div class="row mt-2">
            <div class="col-1">
                <b><?= $dataDb->count_lines; ?></b>
            </div>
            <div class="col-11">
                <?= $dataDb->stat_ip_address; ?>
            </div>
        </div>
    <?php } ?>
</div>