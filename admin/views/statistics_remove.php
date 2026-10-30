<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$month = date("m");
$year = date("Y");
$month--;
if ($month == 0) {
    $month = 12;
    $year--;
}

// *** Remove old statistics ***
if (
    isset($_POST['remove2']) &&
    filter_var($_POST['stat_month'] ?? null, FILTER_VALIDATE_INT) !== false &&
    filter_var($_POST['stat_day'] ?? null, FILTER_VALIDATE_INT) !== false &&
    filter_var($_POST['stat_year'] ?? null, FILTER_VALIDATE_INT) !== false
) {
    $statMonth = (int) $_POST['stat_month'];
    $statDay = (int) $_POST['stat_day'];
    $statYear = (int) $_POST['stat_year'];
    $timestamp = mktime(0, 0, 0, $statMonth, $statDay, $statYear);

    $sql = 'DELETE FROM humo_stat_date WHERE stat_date_linux < "' . $timestamp . '"';
    $result = $dbh->query($sql);
?>
    <div class="alert alert-success">
        <?= $HtmlEscaper->escHtml(__('Old statistics')); ?> <?= $HtmlEscaper->escHtml(date("d-m-Y", $timestamp)); ?> <?= $HtmlEscaper->escHtml(__('are erased')); ?>
    </div>
<?php } ?>

<h2><?= $HtmlEscaper->escHtml(__('Remove statistics')); ?></h2>

<?= $HtmlEscaper->escHtml(__('Statistics will be removed PERMANENTLY. Make a backup first to save the statistics data')); ?><br>

<form method="POST" action="index.php?page=statistics&amp;tab=remove">
    <div class="row my-4">
        <div class="col-md-auto">
            <?= $HtmlEscaper->escHtml(__('Remove ALL statistics BEFORE this date:')); ?>
        </div>
        <div class="col-md-auto">
            <input type="text" name="stat_day" value="1" size="1" class="form-control form-control-sm">
        </div>
        <div class="col-md-auto">
            <input type="text" name="stat_month" value="<?= $HtmlEscaper->escHtml((string) $month); ?>" size="1" class="form-control form-control-sm">
        </div>
        <div class="col-md-auto">
            <input type="text" name="stat_year" value="<?= $HtmlEscaper->escHtml((string) $year); ?>" size="2" class="form-control form-control-sm">
        </div>
        <div class="col-md-auto">
            <?= $HtmlEscaper->escHtml(__('d-m-yyyy')); ?>
        </div>
    </div>
    <input type="submit" name="remove2" value="<?= $HtmlEscaper->escHtml(__('REMOVE statistic data')); ?>" class="btn btn-sm btn-secondary">
</form>