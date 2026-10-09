<?php
// *** Check user authority ***

if ($user['group_sources'] != 'j') {
    exit(__('You are not authorised to see this page.'));
}

$path = $processLinks->get_link($uri_path, 'sources', $tree_id, true);
$url_order = $path . 'start=1&item=0';
if ($data["source_search"] != '') {
    $url_order .= '&source_search=' . rawurlencode((string) $data["source_search"]);
}

$path_form = $processLinks->get_link($uri_path, 'sources', $tree_id);

$datePlace = new \Genealogy\Include\DatePlace();
?>

<h1><?= __('Sources'); ?></h1>

<form method="post" action="<?= $HtmlEscaper->escHtml($path_form); ?>">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-4"></div>

            <div class="col-sm-4">
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm" name="source_search" value="<?= $HtmlEscaper->escHtml($data["source_search"]); ?>" size="20">
                    <button type="submit" class="btn btn-success btn-sm"><?= __('Search'); ?></button>
                </div>
            </div>

            <div class="col-sm-4"></div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/partial/pagination.php'; ?>

<table class="table">
    <thead class="table-primary">
        <tr>
            <?php
            $sort_reverse = $data["sort_desc"];
            $img = '';
            if ($data["order_sources"] == "title") {
                $sort_reverse = '1';
                if ($data["sort_desc"] == '1') {
                    $sort_reverse = '0';
                    $img = 'up';
                }
            }
            ?>
            <th>
                <a href="<?= $HtmlEscaper->escHtml($url_order . '&order_sources=title&sort_desc=' . (int) $sort_reverse); ?>" <?= $data["order_sources"] == "title" ? 'style="background-color:#ffffa0"' : ''; ?>><?= __('Title'); ?>
                    <img src="images/button3<?= $img; ?>.png" alt="<?= __('Sort'); ?>" title="<?= __('Sort'); ?>">
                </a>
            </th>

            <?php
            $sort_reverse = $data["sort_desc"];
            $img = '';
            if ($data["order_sources"] == "date") {
                $sort_reverse = '1';
                if ($data["sort_desc"] == '1') {
                    $sort_reverse = '0';
                    $img = 'up';
                }
            }
            ?>
            <th>
                <a href="<?= $HtmlEscaper->escHtml($url_order . '&order_sources=date&sort_desc=' . (int) $sort_reverse); ?>" <?= $data["order_sources"] == "date" ? 'style="background-color:#ffffa0"' : ''; ?>><?= __('Date'); ?>
                    <img src="images/button3<?= $img; ?>.png" alt="<?= __('Sort'); ?>" title="<?= __('Sort'); ?>">
                </a>
            </th>

            <?php
            $sort_reverse = $data["sort_desc"];
            $img = '';
            if ($data["order_sources"] == "place") {
                $sort_reverse = '1';
                if ($data["sort_desc"] == '1') {
                    $sort_reverse = '0';
                    $img = 'up';
                }
            }
            ?>
            <th>
                <a href="<?= $HtmlEscaper->escHtml($url_order . '&order_sources=place&sort_desc=' . (int) $sort_reverse); ?>" <?= $data["order_sources"] == "place" ? 'style="background-color:#ffffa0"' : ''; ?>><?= __('Place'); ?>
                    <img src="images/button3<?= $img; ?>.png" alt="<?= __('Sort'); ?>" title="<?= __('Sort'); ?>">
                </a>
            </th>
        </tr>
    </thead>

    <?php foreach ($data["listsources"] as $sourceDb) { ?>
        <?php
        // TODO use function
        if ($humo_option["url_rewrite"] == "j") {
            $url = $uri_path . 'source/' . rawurlencode((string) $tree_id) . '/' . rawurlencode((string) $sourceDb->source_gedcomnr);
        } else {
            $url = $uri_path . 'index.php?page=source&tree_id=' . rawurlencode((string) $tree_id) . '&id=' . rawurlencode((string) $sourceDb->source_gedcomnr);
        }
        //$vars['source_gedcomnr'] = $sourceDb->source_gedcomnr;
        //$sourcestring = $processLinks->get_link('../', 'source', $tree_id, false, $vars);
        ?>

        <tr>
            <td>
                <a href="<?= $HtmlEscaper->escHtml($url); ?>">
                    <?php
                    // *** Aldfaer sources don't have a title! ***
                    if ($sourceDb->source_title) {
                        echo $HtmlEscaper->escHtml($sourceDb->source_title);
                    } else {
                        if ($sourceDb->source_text) {
                            echo $HtmlEscaper->escHtml(substr($sourceDb->source_text, 0, 40));
                            if (strlen($sourceDb->source_text) > 40) echo '...';
                        } else {
                            // *** No title, no text. Could be an empty source ***
                            echo '...';
                        }
                    }
                    ?>
                </a>
            </td>
            <td><?= $HtmlEscaper->escHtmlDecoded($datePlace->date_place($sourceDb->source_date, '')); ?></td>
            <td><?= $HtmlEscaper->escHtml($sourceDb->source_place); ?></td>
        </tr>
    <?php } ?>
</table>
<br>

<?php include __DIR__ . '/partial/pagination.php'; ?>
<br><br>