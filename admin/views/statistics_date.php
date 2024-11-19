<?php
// *** Selection of month ***
$present_month = date("n");
$month = $present_month;
if (isset($_POST['month']) && is_numeric($_POST['month'])) {
    $month = $_POST['month'];
}

// *** Search oldest record in database***
$datasql = $dbh->query("SELECT * FROM humo_stat_date ORDER BY stat_date_linux LIMIT 0,1");
$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
if (isset($dataDb->stat_date_linux)) {
    $first_year = date("Y", $dataDb->stat_date_linux);
}

$present_year = date("Y");
$year = $present_year;
if (isset($_POST['year']) && is_numeric($_POST['year'])) {
    $year = $_POST['year'];
}
?>

<div class="center">
    <br>
    <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="tab" value="date_statistics">
        <select size='1' name='month'>
            <option value="1" <?php if ($month == '1') echo ' selected'; ?>><?= __('January'); ?></option>
            <option value="2" <?php if ($month == '2') echo ' selected'; ?>><?= __('February'); ?></option>
            <option value="3" <?php if ($month == '3') echo ' selected'; ?>><?= __('March'); ?></option>
            <option value="4" <?php if ($month == '4') echo ' selected'; ?>><?= __('April'); ?></option>
            <option value="5" <?php if ($month == '5') echo ' selected'; ?>><?= __('May'); ?></option>
            <option value="6" <?php if ($month == '6') echo ' selected'; ?>><?= __('June'); ?></option>
            <option value="7" <?php if ($month == '7') echo ' selected'; ?>><?= __('July'); ?></option>
            <option value="8" <?php if ($month == '8') echo ' selected'; ?>><?= __('August'); ?></option>
            <option value="9" <?php if ($month == '9') echo ' selected'; ?>><?= __('September'); ?></option>
            <option value="10" <?php if ($month == '10') echo ' selected'; ?>><?= __('October'); ?></option>
            <option value="11" <?php if ($month == '11') echo ' selected'; ?>><?= __('November'); ?></option>
            <option value="12" <?php if ($month == '12') echo ' selected'; ?>><?= __('December'); ?></option>
        </select>

        <!-- Select year -->
        <select size='1' name='year'>
            <?php for ($year_select = $first_year; $year_select <= $present_year; $year_select++) { ?>
                <option value="<?= $year_select; ?>" <?= $year == $year_select ? 'selected' : ''; ?>><?= $year_select; ?></option>
            <?php } ?>
        </select>
        <input type="submit" name="submit" value="<?= __('Select'); ?>" class="btn btn-sm btn-success">
    </form><br><br>

    <b><?= __('Total number of visited families:'); ?></b><br>
</div><br>

<?php
// Graphic present month
if ($month == $present_month && $year == $present_year) {
    calender($month, $year, true);
} else {
    calender($month, $year, false);
}

// Graphic year
echo "<br>";
year_graphics($month, $year);
