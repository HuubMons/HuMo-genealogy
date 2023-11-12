<?php
// *** Check user authority ***
if ($user['group_sources'] != 'j') {
    echo __('You are not authorised to see this page.');
    exit();
}

$path = $link_cls->get_link($uri_path, 'sources', $tree_id, true);
$path_form = $link_cls->get_link($uri_path, 'sources', $tree_id);
?>

<h1><?= __('Sources'); ?></h1>

<div class=index_list1><?= $data["line_pages"]; ?>
    <form method="post" action="<?= $path_form; ?>" style="display:inline">
        <input type="text" class="fonts" name="source_search" value="<?= $data["source_search"]; ?>" size="20">
        <input class="fonts" type="submit" value="<?= __('Search'); ?>">
    </form>
</div><br>

<table class="humo index_table" align="center">
    <tr class=table_headline>
        <?php
        // TODO improve MVC
        $url = $path . 'start=1&amp;item=0';
        if ($data["source_search"] != '') {
            $url .=  '&amp;source_search=' . $data["source_search"];
        }

        $style = '';
        $sort_reverse = $data["sort_desc"];
        $img = '';
        if ($data["order_sources"] == "title") {
            $style = ' style="background-color:#ffffa0"';
            $sort_reverse = '1';
            if ($data["sort_desc"] == '1') {
                $sort_reverse = '0';
                $img = 'up';
            }
        }
        echo '<th><a href="' . $url . '&amp;order_sources=title&amp;sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Title') . ' <img src="images/button3' . $img . '.png"></a></th>';

        $style = '';
        $sort_reverse = $data["sort_desc"];
        $img = '';
        if ($data["order_sources"] == "date") {
            $style = ' style="background-color:#ffffa0"';
            $sort_reverse = '1';
            if ($data["sort_desc"] == '1') {
                $sort_reverse = '0';
                $img = 'up';
            }
        }
        echo '<th><a href="' . $url . '&amp;order_sources=date&amp;sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Date') . ' <img src="images/button3' . $img . '.png"></a></th>';

        $style = '';
        $sort_reverse = $data["sort_desc"];
        $img = '';
        if ($data["order_sources"] == "place") {
            $style = ' style="background-color:#ffffa0"';
            $sort_reverse = '1';
            if ($data["sort_desc"] == '1') {
                $sort_reverse = '0';
                $img = 'up';
            }
        }
        echo '<th><a href="' . $url . '&amp;order_sources=place&amp;sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Place') . ' <img src="images/button3' . $img . '.png"></a></th>';
        ?>
    </tr>

    <?php foreach ($data["listsources"] as $sourceDb) { ?>

    <?php
        // TODO use function
        if ($humo_option["url_rewrite"] == "j") {
            $url = $uri_path . 'source/' . $tree_id . '/' . $sourceDb->source_gedcomnr;
        } else {
            $url = $uri_path . 'index.php?page=source&amp;tree_id=' . $tree_id . '&amp;id=' . $sourceDb->source_gedcomnr;
        }
    ?>

        <tr>
            <td>
                <a href="<?= $url; ?>">
                    <?php
                    // *** Aldfaer sources don't have a title! ***
                    if ($sourceDb->source_title) {
                        echo $sourceDb->source_title;
                    } else {
                        if ($sourceDb->source_text) {
                            echo substr($sourceDb->source_text, 0, 40);
                            if (strlen($sourceDb->source_text) > 40) echo '...';
                        } else
                            // *** No title, no text. Could be an empty source ***
                            echo '...';
                    }
                    ?>
                </a>
            </td>
            <td><?= date_place($sourceDb->source_date, ''); ?></td>
            <td><?= $sourceDb->source_place; ?></td>
        </tr>
    <?php
    }
    ?>
</table>

<br>
<div class=index_list1><?= $data["line_pages"]; ?></div><br>