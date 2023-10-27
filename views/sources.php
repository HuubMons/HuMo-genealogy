<?php
// *** Check user authority ***
if ($user['group_sources'] != 'j') {
    echo __('You are not authorised to see this page.');
    exit();
}

include_once(__DIR__."/../include/language_date.php");
include_once(__DIR__."/../include/date_place.php");

// *** Search ***
$source_search = '';
if (isset($_POST['source_search'])) {
    $source_search = safe_text_db($_POST['source_search']);
}
if (isset($_GET['source_search'])) {
    $source_search = safe_text_db($_GET['source_search']);
}


$desc_asc = " ASC ";
$sort_desc = 0;
if (isset($_GET['sort_desc'])) {
    $desc_asc = " ASC ";
    $sort_desc = 0;
    if ($_GET['sort_desc'] == 1) {
        $desc_asc = " DESC ";
        $sort_desc = 1;
    }
}

$order_sources = 'title';
if (isset($_GET['order_sources'])) {
    if ($_GET['order_sources'] == 'title') $order_sources = 'title';
    if ($_GET['order_sources'] == 'date') $order_sources = 'date';
    if ($_GET['order_sources'] == 'place') $order_sources = 'place';
}
if ($order_sources == "title") {
    // *** Default querie: order by title ***
    $querie = "SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'";
    // *** Check user group is restricted sources can be shown ***
    if ($user['group_show_restricted_source'] == 'n') {
        $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
    }

    //	if ($source_search!=''){ $querie.=" AND (source_title LIKE '%".safe_text_db($source_search)."%')"; }
    // *** Only search in source_text if source_title isn't used ***
    if ($source_search != '') {
        $querie .= " AND (source_title LIKE '%" . safe_text_db($source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($source_search) . "%') )";
    }

    //$querie.=" ORDER BY source_title".$desc_asc;
    $querie .= " ORDER BY IF (source_title!='',source_title,source_text)" . $desc_asc; // *** Order by title if exists, else use text ***
}
if ($order_sources == "date") {
    // *** Check user group is restricted sources can be shown ***
    //$querie="SELECT source_status, source_id, source_gedcomnr, source_title, source_date, source_place,

    //CONCAT(right(source_date,4),
    //	date_format( str_to_date( substring(source_date,4,3),'%b' ),'%m'),
    //	date_format( str_to_date( left(source_date,2),'%d' ),'%d') )
    //	as year

    //$querie="SELECT source_status, source_id, source_gedcomnr, source_title, source_text, source_date, source_place,
    //CONCAT(right(source_date,4),
    //	date_format( str_to_date( substring(source_date,-8,3),'%b' ) ,'%m'),
    //	date_format( str_to_date( substring(source_date,-11,2),'%d' ) ,'%d'))
    //	as year
    //FROM humo_sources WHERE source_tree_id='".$tree_id."' AND source_shared='1'";
    $querie = "SELECT source_status, source_id, source_gedcomnr, source_title, source_text, source_date, source_place,
    CONCAT(right(source_date,4),
        date_format( str_to_date( substring(source_date,-8,3),'%b' ) ,'%m'),
        date_format( str_to_date( substring(source_date,-11,2),'%d' ) ,'%d'))
        as year
    FROM humo_sources WHERE source_tree_id='" . $tree_id . "'";
    if ($user['group_show_restricted_source'] == 'n') {
        $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
    }

    //if ($source_search!=''){ $querie.=" AND (source_title LIKE '%".safe_text_db($source_search)."%')"; }
    if ($source_search != '') {
        $querie .= " AND (source_title LIKE '%" . safe_text_db($source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($source_search) . "%') )";
    }

    $querie .= " ORDER BY year" . $desc_asc;
}
if ($order_sources == "place") {
    $querie = "SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'";
    // *** Check user group is restricted sources can be shown ***
    if ($user['group_show_restricted_source'] == 'n') {
        $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
    }

    //if ($source_search!=''){ $querie.=" AND (source_title LIKE '%".safe_text_db($source_search)."%')"; }
    if ($source_search != '') {
        $querie .= " AND (source_title LIKE '%" . safe_text_db($source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($source_search) . "%') )";
    }

    $querie .= " ORDER BY source_place" . $desc_asc;
}

// *** Pages ***
$start = 0;
if (isset($_GET["start"]) and is_numeric($_GET["start"])) $start = $_GET["start"];
$item = 0;
if (isset($_GET['item']) and is_numeric($_GET['item'])) $item = $_GET['item'];
$count_sources = $humo_option['show_persons'];    // *** Number of lines to show ***
//echo $count_sources;

// *** All sources query ***
$all_sources = $dbh->query($querie);
$source = $dbh->query($querie . " LIMIT " . safe_text_db($item) . "," . $count_sources);
$line_pages = __('Page');

//$path = 'sources.php?tree_id=' . $tree_id . '&amp;';
$path = 'index.php?page=sources&amp;tree_id=' . $tree_id . '&amp;';
if ($humo_option["url_rewrite"] == "j") {
    $path = 'sources/' . $tree_id . '?';
}

// "<="
if ($start > 1) {
    $start2 = $start - 20;
    $calculated = ($start - 2) * $count_sources;
    //$line_pages .= '<a href="sources.php?tree_id=' . $tree_id . '&amp;start=' . $start2 . '&amp;item=' . $calculated;
    $line_pages .= '<a href="' . $path . 'start=' . $start2 . '&amp;item=' . $calculated;
    if (isset($_GET['order_sources'])) {
        $line_pages .=  '&amp;order_sources=' . $_GET['order_sources'] . '&sort_desc=' . $sort_desc;
    }
    if ($source_search != '') {
        $line_pages .=  '&amp;source_search=' . $source_search;
    }
    $line_pages .=  '">&lt;= </a>';
}
if ($start <= 0) {
    $start = 1;
}

// 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
for ($i = $start; $i <= $start + 19; $i++) {
    $calculated = ($i - 1) * $count_sources;
    if ($calculated < $all_sources->rowCount()) {
        if ($item == $calculated) {
            $line_pages .=  " <b>$i</b>";
        } else {
            //$line_pages .=  ' <a href="sources.php?tree_id=' . $tree_id . '&amp;item=' . $calculated . '&amp;start=' . $start;
            $line_pages .=  ' <a href="' . $path . 'item=' . $calculated . '&amp;start=' . $start;
            if (isset($_GET['order_sources'])) $line_pages .= '&amp;order_sources=' . $_GET['order_sources'] . '&sort_desc=' . $sort_desc;
            if ($source_search != '') {
                $line_pages .=  '&amp;source_search=' . $source_search;
            }
            $line_pages .=  '">' . $i . '</a>';
        }
    }
}

// "=>"
$calculated = ($i - 1) * $count_sources;
if ($calculated < $all_sources->rowCount()) {
    //$line_pages .=  '<a href="sources.php?tree_id=' . $tree_id . '&amp;start=' . $i . '&amp;item=' . $calculated;
    $line_pages .=  '<a href="' . $path . 'start=' . $i . '&amp;item=' . $calculated;
    if (isset($_GET['order_sources'])) {
        $line_pages .=  '&amp;order_sources=' . $_GET['order_sources'] . '&sort_desc=' . $sort_desc;
    }
    if ($source_search != '') {
        $line_pages .=  '&amp;source_search=' . $source_search;
    }
    $line_pages .=  '"> =&gt;</a>';
}

//$path_form = 'sources.php?tree_id=' . $tree_id;
$path_form = 'index.php?page=sources&amp;tree_id=' . $tree_id;
if ($humo_option["url_rewrite"] == "j") {
    $path_form = 'sources/' . $tree_id;
}

?>
<div class=index_list1><?= $line_pages; ?>
    <form method="post" action="<?= $path_form; ?>" style="display:inline">
        <input type="text" class="fonts" name="source_search" value="<?= $source_search; ?>" size="20">
        <input class="fonts" type="submit" value="<?= __('Search'); ?>">
    </form>
</div><br>

<h1><?= __('Sources'); ?></h1>
<table class="humo index_table" align="center">
    <tr class=table_headline>
        <?php
        $url = $path . 'start=1&amp;item=0';
        if ($source_search != '') {
            $url .=  '&amp;source_search=' . $source_search;
        }

        $style = '';
        $sort_reverse = $sort_desc;
        $img = '';
        if ($order_sources == "title") {
            $style = ' style="background-color:#ffffa0"';
            $sort_reverse = '1';
            if ($sort_desc == '1') {
                $sort_reverse = '0';
                $img = 'up';
            }
        }
        echo '<th><a href="' . $url . '&amp;order_sources=title&amp;sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Title') . ' <img src="images/button3' . $img . '.png"></a></th>';

        $style = '';
        $sort_reverse = $sort_desc;
        $img = '';
        if ($order_sources == "date") {
            $style = ' style="background-color:#ffffa0"';
            $sort_reverse = '1';
            if ($sort_desc == '1') {
                $sort_reverse = '0';
                $img = 'up';
            }
        }
        echo '<th><a href="' . $url . '&amp;order_sources=date&amp;sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Date') . ' <img src="images/button3' . $img . '.png"></a></th>';

        $style = '';
        $sort_reverse = $sort_desc;
        $img = '';
        if ($order_sources == "place") {
            $style = ' style="background-color:#ffffa0"';
            $sort_reverse = '1';
            if ($sort_desc == '1') {
                $sort_reverse = '0';
                $img = 'up';
            }
        }
        echo '<th><a href="' . $url . '&amp;order_sources=place&amp;sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Place') . ' <img src="images/button3' . $img . '.png"></a></th>';
        ?>
    </tr>

    <?php
    while (@$sourceDb = $source->fetch(PDO::FETCH_OBJ)) {
        if ($humo_option["url_rewrite"] == "j") {
            $url = $uri_path . 'source/' . $tree_id . '/' . $sourceDb->source_gedcomnr;
        } else {
            //$url = $uri_path . 'source.php?tree_id=' . $tree_id . '&amp;id=' . $sourceDb->source_gedcomnr;
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
<div class=index_list1><?= $line_pages; ?></div>