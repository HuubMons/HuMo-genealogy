<?php
// *** Check user authority ***
if ($user['group_addresses'] != 'j') {
    echo __('You are not authorised to see this page.');
    exit();
}

include_once(CMS_ROOTPATH . "include/language_date.php");

$desc_asc = " ASC ";
$sort_desc = 0;
if (isset($_SESSION['sort_desc'])) {
    if ($_SESSION['sort_desc'] == 1) {
        $desc_asc = " DESC ";
        $sort_desc = 1;
    } else {
        $desc_asc = " ASC ";
        $sort_desc = 0;
    }
}
if (isset($_GET['sort_desc'])) {
    if ($_GET['sort_desc'] == 1) {
        $desc_asc = " DESC ";
        $sort_desc = 1;
        $_SESSION['sort_desc'] = 1;
    } else {
        $desc_asc = " ASC ";
        $sort_desc = 0;
        $_SESSION['sort_desc'] = 0;
    }
}
$selectsort = '';
if (isset($_SESSION['sort']) and !isset($_GET['sort'])) {
    $selectsort = $_SESSION['sort'];
}
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == "sort_place") {
        $selectsort = "sort_place";
        $_SESSION['sort'] = $selectsort;
    }
    if ($_GET['sort'] == "sort_address") {
        $selectsort = "sort_address";
        $_SESSION['sort'] = $selectsort;
    }
}
$orderby = " address_place" . $desc_asc . ", address_address" . $desc_asc;
if ($selectsort) {
    if ($selectsort == "sort_place") {
        $orderby = " address_place " . $desc_asc . ", address_address" . $desc_asc;
    }
    if ($selectsort == "sort_address") {
        $orderby = " address_address " . $desc_asc;
    }
}

$where = '';
$adr_place = '';
$adr_address = '';
if (isset($_POST['adr_place']) and $_POST['adr_place'] != '') {
    $adr_place = $_POST['adr_place'];
}
if (isset($_POST['adr_address']) and $_POST['adr_address'] != '') {
    $adr_address = $_POST['adr_address'];
}

if (isset($_GET['adr_place']) and $_GET['adr_place'] != '') {
    $adr_place = $_GET['adr_place'];
}
if (isset($_GET['adr_address']) and $_GET['adr_address'] != '') {
    $adr_address = $_GET['adr_address'];
}

if ($adr_place or $adr_address) {
    if ($adr_place != '') {
        $where .= " AND address_place LIKE '%" . safe_text_db($adr_place) . "%' ";
    }
    if ($adr_address != '') {
        $where .= " AND address_address LIKE '%" . safe_text_db($adr_address) . "%' ";
    }
}
//$path_form = 'addresses.php?tree_id=' . $tree_id;
$path_form = 'index.php?page=addresses&amp;tree_id=' . $tree_id;
if ($humo_option["url_rewrite"] == "j") {
    $path_form = 'addresses/' . $tree_id;
}

$place_style = '';
$place_sort_reverse = $sort_desc;
$place_img = '';
if ($selectsort == "sort_place") {
    $place_style = ' style="background-color:#ffffa0"';
    $place_sort_reverse = '1';
    if ($sort_desc == '1') {
        $place_sort_reverse = '0';
        $place_img = 'up';
    }
}

$address_style = '';
$address_sort_reverse = $sort_desc;
$address_img = '';
if ($selectsort == "sort_address") {
    $address_style = ' style="background-color:#ffffa0"';
    $address_sort_reverse = '1';
    if ($sort_desc == '1') {
        $address_sort_reverse = '0';
        $address_img = 'up';
    }
}

//$path = 'addresses.php?tree_id=' . $tree_id . '&';
$path = 'index.php?page=addresses&amp;tree_id=' . $tree_id . '&';
if ($humo_option["url_rewrite"] == "j") {
    $path = 'addresses/' . $tree_id . '?';
}

?>
<h1 style="text-align:center;"><?= __('Addresses'); ?></h1>
<div>
    <!-- *** Search form *** -->
    <form method="POST" action="<?= $path_form; ?>" style="display : inline;">
        <table class="humo" style="margin-left:auto;margin-right:auto">
            <tr class="table_headline">
                <td><?= __('City'); ?>&nbsp;<input type="text" name="adr_place" size=15></td>
                <td><?= __('Street'); ?>&nbsp;<input type="text" name="adr_address" size=15></td>
                <input type="hidden" name="database" value="<?= $database; ?>">
                <td><input type="submit" value="<?= __('Search'); ?>" name="search_addresses"></td>
            </tr>
        </table><br>
    </form>

    <!-- *** Show results *** -->
    <table class="humo" style="margin-left:auto;margin-right:auto">
        <tr class="table_headline">
            <th><a href="<?= $path; ?>adr_place=<?= safe_text_show($adr_place); ?>&adr_address=<?= safe_text_show($adr_address); ?>&sort=sort_place&sort_desc=<?= $place_sort_reverse; ?>" <?= $place_style; ?>><?= __('City'); ?> <img src="images/button3<?= $place_img; ?>.png"></a></th>
            <th><a href="<?= $path; ?>adr_place=<?= safe_text_show($adr_place); ?>&adr_address=<?= safe_text_show($adr_address); ?>&sort=sort_address&sort_desc=<?= $address_sort_reverse; ?>" <?= $address_style; ?>><?= __('Street'); ?> <img src="images/button3<?= $address_img; ?>.png"></a></th>
            <th><?= __('Text'); ?></th>
        </tr>

        <?php
        $sql = "SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' 
            AND address_shared='1'" . $where . " ORDER BY " . $orderby;
        $address = $dbh->query($sql);
        while (@$addressDb = $address->fetch(PDO::FETCH_OBJ)) {
        ?>
            <tr>
                <td style="padding-left:5px;padding-right:5px">
                    <?php if ($addressDb->address_place != '') echo $addressDb->address_place; ?>
                </td>

                <td style="padding-left:5px;padding-right:5px">
                    <?php
                    if ($addressDb->address_address != '') {
                        if ($humo_option["url_rewrite"] == "j") {
                            echo '<a href="' . CMS_ROOTPATH . 'address/' . $tree_id . '/' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';
                        } else {
                            //echo '<a href="' . CMS_ROOTPATH . 'address.php?tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';
                            echo '<a href="' . CMS_ROOTPATH . 'index.php?page=address&amp;tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr . '">' . $addressDb->address_address . '</a>';
                        }
                    }
                    ?>
                </td>

                <td>
                    <?= substr($addressDb->address_text, 0, 40); ?>
                    <?php if (strlen($addressDb->address_text) > 40) echo '...'; ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </table>
</div>