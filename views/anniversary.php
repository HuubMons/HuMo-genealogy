<?php

/**
 * Anniversary list
 * 
 * 29-04-2006 Louis Ywema: build first script.
 * 18-06-2011 Huub Mons: translated all remarks and variables into English. And did some minor updates.
 * 10-11-2019 Yossi Beck: Added wedding anniversaries and menu
 * 01-12-2023 Huub Mons: refactor script (improve variables and prepare MVC).
 */

// *** Check user authority ***
if ($user["group_birthday_list"] != 'j') {
    exit(__('You are not authorised to see this page.'));
}

$path = $link_cls->get_link($uri_path, 'anniversary', $tree_id, true);

$max_age = '110';
$last_cal_day = 0;
$months = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');

$person_link = new PersonLink;
$person_name = new PersonName;
$person_privacy = new PersonPrivacy;
$language_date = new LanguageDate;
?>

<!-- *** Center page *** -->
<div class="center">
    <!-- *** Show month and year *** -->
    <h1 class="standard_header"><?= ucfirst($data["show_month"]) . ' ' . date("Y"); ?></h1>

    <div>
        <form name="anniv" id="anniv" action="<?= $path; ?>month=<?= $data["month"]; ?>" method="post">
            <!-- Show line of months -->
            <div>
                <?php foreach ($months as $month) { ?>
                    <?php if ($data["month"] == $month) { ?>
                        <b><?= __($month); ?></b>
                    <?php } else { ?>
                        <a href="<?= $path; ?>month=<?= $month . $data["url_end"]; ?>"><?= __($month); ?></a>
                    <?php } ?>
                    <?php if ($month !== 'dec') {
                        echo '&#124;';
                    } ?>
                <?php } ?>
            </div>

            <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" id='birthd' onClick='document.getElementById("anniv").submit();' type='radio' name='ann_choice' value='birthdays' <?= $data["ann_choice"] ? ' checked' : ''; ?>>
                <label class="form-check-label" for="birthd"><?= __('Birthdays'); ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" id='wedd' onClick='document.getElementById("anniv").submit();' type='radio' name='ann_choice' value='wedding' <?= $data["ann_choice"] == 'wedding' ? ' checked' : ''; ?>>
                <label class="form-check-label" for="wedd"><?= __('Wedding anniversaries'); ?></label>
            </div>

            (<div class="form-check form-check-inline">
                <input class="form-check-input" type='checkbox' onClick='document.getElementById("wedd").checked = true;document.getElementById("anniv").submit();' name='civil' id='civil' value='civil' <?= $data["civil"] ? ' checked' : ''; ?>>
                <label class="form-check-label" for="civil"><?= __('Civil'); ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type='checkbox' onClick='document.getElementById("wedd").checked = true;document.getElementById("anniv").submit();' name='relig' id='relig' value='relig' <?= $data["relig"] ? ' checked' : ''; ?>>
                <label class="form-check-label" for="relig"><?= __('Religious'); ?>)</label>
            </div>
        </form>
    </div><br>

    <?php
    $privcount = 0; // *** Privacy counter ***
    // *** Build page ***
    if ($data["ann_choice"] == 'birthdays') {

        // *** Build query ***
        $sql = "SELECT *, abs(substring( pers_birth_date,1,2 )) as birth_day, substring( pers_birth_date,-4 ) as birth_year
            FROM humo_persons
            WHERE pers_tree_id = :tree_id AND (substring( pers_birth_date, 4,3) = :month
            OR substring( pers_birth_date, 3,3) = :month)
            order by birth_day, birth_year ";

        try {
            $qry = $dbh->prepare($sql);
            $qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
            $qry->bindValue(':month', $data["month"], PDO::PARAM_STR);
            $qry->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . '<br>';
        }
    ?>
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <table class="table">
                    <thead class="table-primary">
                        <tr>
                            <th><?= __('Day'); ?></th>
                            <th><?= ucfirst(__('born')); ?></th>
                            <th><?= __('Name'); ?></th>
                            <th><?= ucfirst(__('died')); ?></th>
                        </tr>
                    </thead>

                    <?php
                    while ($record = $qry->fetch(PDO::FETCH_OBJ)) {
                        $calendar_day = $record->birth_day;
                        $birth_day = $record->birth_day . ' ' . $data["month"];

                        $privacy = $person_privacy->get_privacy($record);
                        $name = $person_name->get_person_name($record, $privacy);

                        if (!$privacy) {
                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $url = $person_link->get_person_link($record);

                            $death_date = $record->pers_death_date;
                            $age = (date("Y") - $record->birth_year);

                            if ($death_date != '') {
                                $died = $language_date->language_date($death_date);
                            } elseif ($age > $max_age) {
                                $died = '? ';
                            } else {
                                $died = '  ';
                            }
                    ?>
                            <!-- Highlight present day -->
                            <tr <?= $birth_day == $data["today"] ? 'class="table-primary"' : ''; ?>>
                                <td><?= $calendar_day == $last_cal_day ? '<br>' : $calendar_day . ' ' . $data["show_month"]; ?></td>
                                <?php $last_cal_day = $calendar_day; ?>

                                <td><?= $privacy ?  __(' PRIVACY FILTER') : $record->birth_year; ?></td>

                                <td align="left"><a href="<?= $url;?>"><?= $name["standard_name"];?></a></td>

                                <td>
                                    <div class="pale"><?= $privacy ? __(' PRIVACY FILTER') : $died; ?>
                                </td>
                            </tr>
                    <?php
                        } else {
                            $privcount++;
                        }
                    }
                    ?>
                </table>
            </div>
        </div>


        <?php if ($privcount) { ?>
            <br><?= $privcount . __(' persons are not shown due to privacy settings'); ?><br>
        <?php
        }
    } else {
        // *** wedding anniversary ***
        $wed = array();
        $cnt = 0;

        // *** Build query ***
        if ($data["civil"]) {
            $sql = "SELECT *, abs(substring( fam_marr_date,1,2 )) as marr_day, substring( fam_marr_date,-4 ) as marr_year
                FROM humo_families
                WHERE fam_tree_id = :tree_id AND (substring( fam_marr_date, 4,3) = :month
                OR substring( fam_marr_date, 3,3) = :month)
                order by marr_day, marr_year ";

            try {
                $qry = $dbh->prepare($sql);
                $qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
                $qry->bindValue(':month', $data["month"], PDO::PARAM_STR);
                $qry->execute();
            } catch (PDOException $e) {
                echo $e->getMessage() . "<br/>";
            }

            while ($record = $qry->fetch(PDO::FETCH_OBJ)) {
                $wed[$cnt]['calday'] = $record->marr_day;
                $wed[$cnt]['marday'] = $record->marr_day . ' ' . $data["month"];
                $wed[$cnt]['maryr'] = $record->marr_year;
                $day = $record->marr_day;
                if (strlen($record->marr_day) == 1) {
                    $day = "0" . $day;
                }
                $wed[$cnt]['dayyear'] = $day . $record->marr_year;
                $wed[$cnt]['man'] = $record->fam_man;
                $wed[$cnt]['woman'] = $record->fam_woman;
                $wed[$cnt]['type'] = __('Civil');
                $cnt++;
            }
        }

        if ($data["relig"]) {
            $sql = "SELECT *, abs(substring( fam_marr_church_date,1,2 )) as marr_day, substring( fam_marr_church_date,-4 ) as marr_year
                FROM humo_families
                WHERE fam_tree_id = :tree_id AND (substring( fam_marr_church_date, 4,3) = :month
                OR substring( fam_marr_church_date, 3,3) = :month)
                order by marr_day, marr_year ";
            try {
                $qry = $dbh->prepare($sql);
                $qry->bindValue(':tree_id', $tree_id, PDO::PARAM_STR);
                $qry->bindValue(':month', $data["month"], PDO::PARAM_STR);
                $ccc = $qry->execute();
            } catch (PDOException $e) {
                echo $e->getMessage() . '<br>';
            }
            while ($record = $qry->fetch(PDO::FETCH_OBJ)) {
                $wed[$cnt]['calday'] = $record->marr_day;
                $wed[$cnt]['marday'] = $record->marr_day . ' ' . $data["month"];
                $wed[$cnt]['maryr'] = $record->marr_year;
                $day = $record->marr_day;
                if (strlen($record->marr_day) == 1) {
                    $day = "0" . $day;
                }  // for sorting array
                $wed[$cnt]['dayyear'] = $day . $record->marr_year;
                $wed[$cnt]['man'] = $record->fam_man;
                $wed[$cnt]['woman'] = $record->fam_woman;
                $wed[$cnt]['type'] = __('Religious');
                $cnt++;
            }
        }
        ?>

        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <table class="table">
                    <thead class="table-primary">
                        <tr>
                            <th><?= __('Day'); ?></th>
                            <th><?= ucfirst(__('Wedding year')); ?></th>
                            <th><?= __('Civil/ Religious'); ?></th>
                            <th><?= __('Spouses'); ?></th>
                        </tr>
                    </thead>

                    <?php
                    if (isset($wed) and count($wed) > 0) {
                        // sort the array to mix civill and religious
                        if ($data["civil"] && $data["relig"]) {
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
                            $manDb = $db_functions->get_person($value['man']);
                            $man_privacy = $person_privacy->get_privacy($manDb);
                            if (!$value['man']) {
                                $man_name = 'N.N.';
                            } else {
                                $name = $person_name->get_person_name($manDb, $man_privacy);

                                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                                $url = $person_link->get_person_link($manDb);

                                $man_name = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
                            }

                            // get wife
                            $womanDb = $db_functions->get_person($value['woman']);
                            $woman_privacy = $person_privacy->get_privacy($womanDb);
                            if (!$value['woman']) {
                                $woman_name = 'N.N.';
                            } else {
                                $name = $person_name->get_person_name($womanDb, $woman_privacy);

                                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                                $url = $person_link->get_person_link($womanDb);

                                $woman_name = '<a href="' . $url . '">' . $name["standard_name"] . '</a>';
                            }

                            $calendar_day = $value['calday'];
                            $marr_day = $value['marday'];

                            if (!$man_privacy && !$woman_privacy) {
                    ?>
                                <!-- Highlight present day -->
                                <tr <?php if ($marr_day == $data["today"]) echo 'class="table-primary"'; ?>>
                                    <td><?= $calendar_day == $last_cal_day ? '<br>' : $calendar_day . ' ' . $data["show_month"]; ?></td>
                                    <?php $last_cal_day = $calendar_day; ?>

                                    <td><?= $man_privacy and !$woman_privacy ? __(' PRIVACY FILTER') : $value['maryr']; ?></td>

                                    <td align="left"><?= $value['type']; ?></td>
                                    <td align="left"><?= $man_name . ' & ' . $woman_name; ?></td>
                                </tr>
                        <?php
                            } else {
                                $privcount++;
                            }
                        }
                        unset($wed);
                    } else {
                        ?>
                        <tr>
                            <td colspan="4"><?= __('No results found for this month'); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
        <?php if ($privcount) { ?>
            <br><?= $privcount . __(' persons are not shown due to privacy settings'); ?><br>
    <?php
        }
    }
    ?>
</div>

<br>
<br>