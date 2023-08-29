<?php

/**
 * Birtday list
 * 
 * Author : Louis Ywema
 * Date  : 29-04-2006
 * 
 * 18-06-2011 Huub: translated all remarks and variables into English. And did some minor updates.
 * 
 * 10-11-2019 Yossi Beck - Added wedding anniversaries and menu
 */

include_once(CMS_ROOTPATH . "include/language_date.php");
include_once(CMS_ROOTPATH . "include/person_cls.php");

// *** Check user authority ***
if ($user["group_birthday_list"] != 'j') {
    echo __('You are not authorised to see this page.');
    exit();
}

if ($humo_option["url_rewrite"] == "j") {
    $path = 'birthday_list';
    $path2 = 'birthday_list?';
} else {
    $path = CMS_ROOTPATH . 'index.php?page=birthday_list';
    $path2 = CMS_ROOTPATH . 'index.php?page=birthday_list&amp;';
}

// *** Month to show ***
$month = date("M");
if (isset($_GET['month']) and strlen($_GET['month']) == '3') {
    $month_check = $_GET['month'];
    if ($month_check == 'jan') $month = 'jan';
    if ($month_check == 'feb') $month = 'feb';
    if ($month_check == 'mar') $month = 'mar';
    if ($month_check == 'apr') $month = 'apr';
    if ($month_check == 'may') $month = 'may';
    if ($month_check == 'jun') $month = 'jun';
    if ($month_check == 'jul') $month = 'jul';
    if ($month_check == 'aug') $month = 'aug';
    if ($month_check == 'sep') $month = 'sep';
    if ($month_check == 'oct') $month = 'oct';
    if ($month_check == 'nov') $month = 'nov';
    if ($month_check == 'dec') $month = 'dec';
}
$show_month = language_date($month);

// *** Calculate present date, month and year ***
$today = date('j') . ' ' . date('M');
$today = strtolower($today);

$url_end = '';
if (isset($_POST['ann_choice']) and $_POST['ann_choice'] == 'wedding') {
    $url_end = '&amp;ann_choice=wedding';
    if (isset($_POST['civil'])) $url_end .= '&amp;civil=civil';
    if (isset($_POST['relig'])) $url_end .= '&amp;relig=relig';
}

if (isset($_GET['ann_choice']) and $_GET['ann_choice'] == 'wedding') {
    $url_end = '&amp;ann_choice=wedding';
    if (isset($_GET['civil'])) $url_end .= '&amp;civil=civil';
    if (isset($_GET['relig'])) $url_end .= '&amp;relig=relig';
}

// *** If month is clicked, also set $_POST ***
if (isset($_GET['ann_choice'])) $_POST['ann_choice'] = 'wedding';
if (isset($_GET['civil'])) {
    $_POST['ann_choice'] = 'wedding';
    $_POST['civil'] = 'wedding';
}
if (isset($_GET['relig'])) {
    $_POST['ann_choice'] = 'wedding';
    $_POST['relig'] = 'relig';
}

$spacer = '&nbsp;&#124;&nbsp;';
$max_age = '110';
$last_cal_day = 0;
?>

<!-- *** Center page *** -->
<div class="fonts center">
    <?php
    // *** Show navigation ***
    echo '[ ';
    echo '<a href="' . $path2 . 'month=jan' . $url_end . '">' . __('jan') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=feb' . $url_end . '">' . __('feb') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=mar' . $url_end . '">' . __('mar') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=apr' . $url_end . '">' . __('apr') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=may' . $url_end . '">' . __('may') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=jun' . $url_end . '">' . __('jun') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=jul' . $url_end . '">' . __('jul') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=aug' . $url_end . '">' . __('aug') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=sep' . $url_end . '">' . __('sep') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=oct' . $url_end . '">' . __('oct') . '</a>' . $spacer;
    echo '<a href="' . $path2 . 'month=nov' . $url_end . '">' . __('nov') . '</a>' . $spacer;
    echo '<a href="' . $path2 . '   month=dec' . $url_end . '">' . __('dec') . '</a>';
    echo " ]\n";
    ?>

    <!-- *** Show month and year *** -->
    <h1 class="standard_header"><?= ucfirst($show_month) . ' ' . date("Y"); ?></h1>

    <div>
        <form name="anniv" id="anniv" action="<?= $path; ?>?month=<?= $month; ?>" method="post">
            <table class="humo" style="text-align:center;width:40%;margin-left:auto;margin-right:auto;border:1px solid black;">
                <tr>
                    <?php
                    $check = ' checked';
                    if (isset($_POST['ann_choice']) and $_POST['ann_choice'] != 'birthdays') $check = '';
                    echo "<td style='border:none'><input id='birthd' onClick='document.getElementById(\"anniv\").submit();' type='radio' name='ann_choice' value='birthdays'" . $check . ">" . __('Birthdays') . "</td>";

                    $check = '';
                    if (isset($_POST['ann_choice']) and $_POST['ann_choice'] == 'wedding') $check = " checked";
                    echo "<td style='border:none'><input id='wedd' onClick='document.getElementById(\"anniv\").submit();' type='radio' name='ann_choice' value='wedding'" . $check . ">" . __('Wedding anniversaries') . "&nbsp;&nbsp;";

                    $check = ' checked';
                    if (isset($_POST['ann_choice']) and !isset($_POST['civil'])) $check = '';
                    echo "<span style='font-size:90%'>(<input type='checkbox' onClick='document.getElementById(\"wedd\").checked = true;document.getElementById(\"anniv\").submit();' name='civil' id='civil' value='civil'" . $check . ">" . __('Civil');

                    $check = '';
                    if (isset($_POST['ann_choice']) and isset($_POST['relig'])) $check = " checked";
                    echo "&nbsp;&nbsp;<input type='checkbox' onClick='document.getElementById(\"wedd\").checked = true;document.getElementById(\"anniv\").submit();' name='relig' id='relig' value='relig'" . $check . ">" . __('Religious') . ")</span></td>";
                    ?>
                </tr>
            </table>
        </form>
    </div><br>

    <?php
    // *** Build page ***
    if (!isset($_POST['ann_choice']) or $_POST['ann_choice'] == "birthdays") {
        $privcount = 0; // *** Count privacy persons ***

        // *** Build query ***
        $sql = "SELECT *,
            abs(substring( pers_birth_date,1,2 )) as birth_day,
            substring( pers_birth_date,-4 ) as birth_year
            FROM humo_persons
            WHERE pers_tree_id = :tree_id AND (substring( pers_birth_date, 4,3) = :month
            OR substring( pers_birth_date, 3,3) = :month)
            order by birth_day, birth_year ";

        try {
            $qry = $dbh->prepare($sql);
            $qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
            $qry->bindValue(':month', $month, PDO::PARAM_STR);
            $qry->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
    ?>
        <table class="humo" align="center">
            <tr class="table_headline">
                <!-- *** Show headers *** -->
                <th><?= __('Day'); ?></th>
                <th><?= ucfirst(__('born')); ?></th>
                <th><?= __('Name'); ?></th>
                <th><?= ucfirst(__('died')); ?></th>
            </tr>

            <?php
            while ($record = $qry->fetch(PDO::FETCH_OBJ)) {
                $calendar_day = $record->birth_day;
                $birth_day = $record->birth_day . ' ' . $month;
                $person_cls = new person_cls($record);
                $name = $person_cls->person_name($record);

                if (!$person_cls->privacy) {
                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $url = $person_cls->person_url2($record->pers_tree_id, $record->pers_famc, $record->pers_fams, $record->pers_gedcomnumber);

                    $person_name = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';

                    $death_date = $record->pers_death_date;
                    $age = (date("Y") - $record->birth_year);

                    if ($death_date != '') {
                        $died = language_date($death_date);
                    } else if ($age > $max_age) {
                        $died = '? ';
                    } else {
                        $died = '  ';
                    }

            ?>
                    <!-- Highlight present day -->
                    <tr <?php if ($birth_day == $today) echo 'bgcolor="#BFBFBF"'; ?>>
                        <td><?php echo ($calendar_day == $last_cal_day) ? '<br>' : $calendar_day . ' ' . $show_month; ?></td>
                        <?php $last_cal_day = $calendar_day; ?>

                        <td><?php echo ($person_cls->privacy) ?  __(' PRIVACY FILTER') : $record->birth_year; ?></td>

                        <td align="left"><?= $person_name; ?></td>

                        <td>
                            <div class="pale"><?php echo ($person_cls->privacy) ? __(' PRIVACY FILTER') : $died; ?>
                        </td>
                    </tr>
            <?php
                } else
                    $privcount++;
            }
            ?>
        </table>
        <?php

        if ($privcount) {
            echo "<br>" . $privcount . __(' persons are not shown due to privacy settings') . ".<br>";
        }
    } else {
        // wedding anniversary
        $privcount = 0; // *** Count privacy persons ***
        ?>
        <table class="humo" align="center">
            <tr class="table_headline">
                <th><?= __('Day'); ?></th>
                <th><?= ucfirst(__('Wedding year')); ?></th>
                <th><?= __('Civil/ Religious'); ?></th>
                <th><?= __('Spouses'); ?></th>
            </tr>

            <?php
            $wed = array();
            $cnt = 0;

            // *** Build query ***
            if (isset($_POST['civil'])) {
                $sql = "SELECT *,
                    abs(substring( fam_marr_date,1,2 )) as marr_day,
                    substring( fam_marr_date,-4 ) as marr_year
                    FROM humo_families
                    WHERE fam_tree_id = :tree_id AND (substring( fam_marr_date, 4,3) = :month
                    OR substring( fam_marr_date, 3,3) = :month)
                    order by marr_day, marr_year ";

                try {
                    $qry = $dbh->prepare($sql);
                    $qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
                    $qry->bindValue(':month', $month, PDO::PARAM_STR);
                    $qry->execute();
                } catch (PDOException $e) {
                    echo $e->getMessage() . "<br/>";
                }

                while ($record = $qry->fetch(PDO::FETCH_OBJ)) {
                    $wed[$cnt]['calday'] = $record->marr_day;
                    $wed[$cnt]['marday'] = $record->marr_day . ' ' . $month;
                    $wed[$cnt]['maryr'] = $record->marr_year;
                    $day = $record->marr_day;
                    if (strlen($record->marr_day) == 1) $day = "0" . $day;
                    $wed[$cnt]['dayyear'] = $day . $record->marr_year;
                    $wed[$cnt]['man'] = $record->fam_man;
                    $wed[$cnt]['woman'] = $record->fam_woman;
                    $wed[$cnt]['type'] = __('Civil');
                    $cnt++;
                }
            }

            if (isset($_POST['relig'])) {
                $sql = "SELECT *,
                    abs(substring( fam_marr_church_date,1,2 )) as marr_day,
                    substring( fam_marr_church_date,-4 ) as marr_year
                    FROM humo_families
                    WHERE fam_tree_id = :tree_id AND (substring( fam_marr_church_date, 4,3) = :month
                    OR substring( fam_marr_church_date, 3,3) = :month)
                    order by marr_day, marr_year ";
                try {
                    $qry = $dbh->prepare($sql);
                    $qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
                    $qry->bindValue(':month', $month, PDO::PARAM_STR);
                    $ccc = $qry->execute();
                } catch (PDOException $e) {
                    echo $e->getMessage() . '<br>';
                }
                while ($record = $qry->fetch(PDO::FETCH_OBJ)) {
                    $wed[$cnt]['calday'] = $record->marr_day;
                    $wed[$cnt]['marday'] = $record->marr_day . ' ' . $month;
                    $wed[$cnt]['maryr'] = $record->marr_year;
                    $day = $record->marr_day;
                    if (strlen($record->marr_day) == 1) $day = "0" . $day;  // for sorting array
                    $wed[$cnt]['dayyear'] = $day . $record->marr_year;
                    $wed[$cnt]['man'] = $record->fam_man;
                    $wed[$cnt]['woman'] = $record->fam_woman;
                    $wed[$cnt]['type'] = __('Religious');
                    $cnt++;
                }
            }
            if (isset($wed) and count($wed) > 0) {
                // sort the array to mix civill and religious
                if (isset($_POST['civil']) and isset($_POST['relig'])) {
                    function custom_sort($a, $b)
                    {
                        //return $a['dayyear']>$b['dayyear']; // DEPRECATED in PHP 8.
                        return $a['dayyear'] <=> $b['dayyear'];
                    }
                    // Sort the multidimensional array
                    usort($wed, "custom_sort");
                    // Define the custom sort function
                }

                foreach ($wed as $key => $value) {
                    // get husband
                    @$manDb = $db_functions->get_person($value['man']);
                    // *** Use class to process person ***
                    $man_cls = new person_cls($manDb);
                    if (!$value['man'])
                        $man_name = 'N.N.';
                    else {
                        $name = $man_cls->person_name($manDb);

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $man_cls->person_url2($manDb->pers_tree_id, $manDb->pers_famc, $manDb->pers_fams, $manDb->pers_gedcomnumber);

                        $man_name = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    }

                    // get wife
                    @$womanDb = $db_functions->get_person($value['woman']);
                    // *** Use class to process person ***
                    $woman_cls = new person_cls($womanDb);
                    if (!$value['woman'])
                        $woman_name = 'N.N.';
                    else {
                        $name = $woman_cls->person_name($womanDb);

                        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                        $url = $woman_cls->person_url2($womanDb->pers_tree_id, $womanDb->pers_famc, $womanDb->pers_fams, $womanDb->pers_gedcomnumber);

                        $woman_name = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
                    }

                    $calendar_day = $value['calday'];
                    $marr_day = $value['marday'];

                    if (!$man_cls->privacy and !$woman_cls->privacy) {
            ?>
                        <!-- Highlight present day -->
                        <tr <?php if ($marr_day == $today) echo 'bgcolor="#BFBFBF"'; ?>>
                            <td><?php echo ($calendar_day == $last_cal_day) ? '<br>' : $calendar_day . ' ' . $show_month; ?></td>
                            <?php $last_cal_day = $calendar_day;; ?>

                            <td><?php echo ($man_cls->privacy and !$woman_cls->privacy) ? __(' PRIVACY FILTER') : $value['maryr']; ?></td>

                            <td align="left"><?= $value['type']; ?></td>
                            <td align="left"><?= $man_name . ' & ' . $woman_name; ?></td>
                        </tr>
            <?php
                    } else
                        $privcount++;
                }
                unset($wed);
            } else {
                echo '<tr><td colspan="4">' . __('No results found for this month') . '</td></tr>';
            }
            ?>
        </table>
    <?php
        if ($privcount) {
            echo "<br>" . $privcount . __(' persons are not shown due to privacy settings') . ".<br>";
        }
    }
    ?>
</div>