<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
$selectedMonth = (int) $statistics['month'];
$selectedYear = (int) $statistics['year'];
$firstYear = (int) $statistics['first_year'];
$presentYear = (int) $statistics['present_year'];
$presentMonth = (int) $statistics['present_month'];
?>

<div class="mb-3">
    <form method="POST" action="index.php?page=statistics&amp;tab=date_statistics">
        <div class="row">
            <div class="col-md-auto">
                <select size="1" name="month" class="form-select form-select-sm">
                    <option value="1" <?= $selectedMonth === 1 ? 'selected' : ''; ?>><?= __('January'); ?></option>
                    <option value="2" <?= $selectedMonth === 2 ? 'selected' : ''; ?>><?= __('February'); ?></option>
                    <option value="3" <?= $selectedMonth === 3 ? 'selected' : ''; ?>><?= __('March'); ?></option>
                    <option value="4" <?= $selectedMonth === 4 ? 'selected' : ''; ?>><?= __('April'); ?></option>
                    <option value="5" <?= $selectedMonth === 5 ? 'selected' : ''; ?>><?= __('May'); ?></option>
                    <option value="6" <?= $selectedMonth === 6 ? 'selected' : ''; ?>><?= __('June'); ?></option>
                    <option value="7" <?= $selectedMonth === 7 ? 'selected' : ''; ?>><?= __('July'); ?></option>
                    <option value="8" <?= $selectedMonth === 8 ? 'selected' : ''; ?>><?= __('August'); ?></option>
                    <option value="9" <?= $selectedMonth === 9 ? 'selected' : ''; ?>><?= __('September'); ?></option>
                    <option value="10" <?= $selectedMonth === 10 ? 'selected' : ''; ?>><?= __('October'); ?></option>
                    <option value="11" <?= $selectedMonth === 11 ? 'selected' : ''; ?>><?= __('November'); ?></option>
                    <option value="12" <?= $selectedMonth === 12 ? 'selected' : ''; ?>><?= __('December'); ?></option>
                </select>
            </div>

            <div class="col-md-auto">
                <!-- Select year -->
                <select size="1" name="year" class="form-select form-select-sm">
                    <?php for ($yearSelect = $firstYear; $yearSelect <= $presentYear; $yearSelect++) { ?>
                        <option value="<?= $yearSelect; ?>" <?= $selectedYear === $yearSelect ? 'selected' : ''; ?>><?= $yearSelect; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-auto">
                <input type="submit" name="submit" value="<?= __('Select'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>
    </form><br>

    <b><?= __('Total number of visited families:'); ?></b>
</div>

<?php
// Graphic present month
if ($selectedMonth === $presentMonth && $selectedYear === $presentYear) {
    calender($selectedMonth, $selectedYear, true);
} else {
    calender($selectedMonth, $selectedYear, false);
}

// Graphic year
echo '<br>';
year_graphics($selectedMonth, $selectedYear);
