<?php
$month = date("m");
$year = date("Y");
$month--;
if ($month == 0) {
    $month = 12;
    $year--;
}

// *** Remove old statistics ***
if (isset($_POST['remove2']) && is_numeric($_POST['stat_month']) && is_numeric($_POST['stat_day']) && is_numeric($_POST['stat_year'])) {
    $timestamp = mktime(0, 0, 0, $_POST['stat_month'], $_POST['stat_day'], $_POST['stat_year']);

    $sql = 'DELETE FROM humo_stat_date WHERE stat_date_linux < "' . $timestamp . '"';
    $result = $dbh->query($sql);
?>
    <div class="alert alert-success">
        <?= __('Old statistics'); ?> <?= date("d-m-Y", $timestamp); ?> <?= __('are erased'); ?>
    </div>
<?php } ?>

<h2><?= __('Remove statistics'); ?></h2>

<?= __('Statistics will be removed PERMANENTLY. Make a backup first to save the statistics data'); ?><br>

<form method="POST" action="index.php?page=statistics&amp;tab=remove">
    <?= __('Remove ALL statistics BEFORE this date:'); ?>
    <input type="text" name="stat_day" value="1" size="1">
    <input type="text" name="stat_month" value="<?= $month; ?>" size="1">
    <input type="text" name="stat_year" value="<?= $year; ?>" size="2"> <?= __('d-m-yyyy'); ?><br>
    <input type="submit" name="remove2" value="<?= __('REMOVE statistic data'); ?>" class="btn btn-sm btn-secondary">
</form>