<?php
$family_qry = $dbh->query("SELECT * FROM humo_trees as humo_trees2 RIGHT JOIN
( SELECT stat_tree_id, count(humo_stat_date.stat_easy_id) as count_lines FROM humo_stat_date GROUP BY stat_tree_id )
 as humo_stat_date2 ON humo_trees2.tree_id=humo_stat_date2.stat_tree_id ORDER BY tree_order desc");
?>

<h2 align="center"><?= __('Status statistics table'); ?></h2>
<div class="row mb-2">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <table class="table">
            <thead class="table-primary">
                <tr>
                    <th><?= ucfirst(__('family tree')); ?></th>
                    <th><?= __('Records'); ?></th>
                    <th><?= __('Number of unique visitors'); ?></th>
                </tr>
            </thead>
            <?php while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) { ?>
                <tr>
                    <?php
                    //statistics_line($familyDb);
                    if ($familyDb->tree_prefix) {
                        $tree_id = $familyDb->tree_id;
                        // *** Show family tree name ***
                        $treetext = $showTreeText ->show_tree_text($familyDb->tree_id, $selected_language);
                    ?>
                        <td><?= $treetext['name']; ?></td>
                    <?php } else { ?>
                        <td><b><?= __('FAMILY TREE ERASED'); ?></b></td>
                    <?php } ?>
                    <td><?= $familyDb->count_lines; ?></td>

                    <?php
                    // *** Total number of unique visitors ***
                    $count_visitors = 0;
                    if ($familyDb->tree_id) {
                        //$stat=$dbh->query("SELECT *
                        //	FROM humo_stat_date LEFT JOIN humo_trees
                        //	ON humo_trees.tree_id=humo_stat_date.stat_tree_id
                        //	WHERE humo_trees.tree_id=".$familyDb->tree_id."
                        //	GROUP BY stat_ip_address
                        //	");
                        $stat = $dbh->query("SELECT stat_ip_address FROM humo_stat_date LEFT JOIN humo_trees
                            ON humo_trees.tree_id=humo_stat_date.stat_tree_id WHERE humo_trees.tree_id=" . $familyDb->tree_id . "
                            GROUP BY stat_ip_address");
                        $count_visitors = $stat->rowCount();
                    }
                    ?>
                    <td><?= $count_visitors; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

<h2 align="center"><?= __('General statistics:'); ?></h2>

<div class="row mb-2">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <table class="table">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Item'); ?></th>
                    <th><?= __('Counter'); ?></th>
                </tr>
            </thead>
            <?php
            // *** Total number unique visitors ***
            $stat = $dbh->query("SELECT stat_ip_address FROM humo_stat_date GROUP BY stat_ip_address");
            $count_visitors = $stat->rowCount();
            ?>
            <tr>
                <td><?= __('Total number of unique visitors:'); ?></td>
                <td><?= $count_visitors; ?></td>
            </tr>

            <?php
            // *** Total number visited families ***
            $datasql = $dbh->query("SELECT stat_id FROM humo_stat_date");
            if ($datasql) {
                $total = $datasql->rowCount();
            }
            ?>
            <tr>
                <td><?= __('Total number of visited families:'); ?></td>
                <td><?= $total; ?></td>
            </tr>

            <?php
            // Visitors per day/ month/ year.
            // 1 day = 86400
            $time_period = strtotime("now") - 3600; // 1 hour
            $datasql = $dbh->query("SELECT * FROM humo_stat_date WHERE stat_date_linux > " . $time_period);
            if ($datasql) {
                $total = $datasql->rowCount();
            }
            ?>
            <tr>
                <td><?= __('Total number of families in the last hour:'); ?></td>
                <td><?= $total; ?></td>
            </tr>
        </table>
    </div>
</div>

<!-- Country statistics -->
<h2 align="center"><?= __('Unique visitors - Country of origin'); ?></h2>
<?php
$temp = $dbh->query("SHOW TABLES LIKE 'humo_stat_country'");
if ($temp->rowCount()) {
    $max = 400; // *** For now just show all countries ***

    // *** Names of countries ***
    include_once(__DIR__ . '/../include/countries.php');

    $statqry = "SELECT stat_country_code, count(stat_country_code) as count_country_code FROM humo_stat_country
        GROUP BY stat_country_code ORDER BY count_country_code DESC LIMIT 0," . $max;
    $stat = $dbh->query($statqry);

?>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <table class="table" border="1" cellspacing="0">
                <thead class="table-primary">
                    <tr>
                        <th><?= __('Country of origin'); ?></th>
                        <th><?= __('Number of unique visitors'); ?></th>
                    </tr>
                </thead>
                <?php
                while ($statDb = $stat->fetch(PDO::FETCH_OBJ)) {
                    $country_code = $statDb->stat_country_code;
                    $flag = "images/flags/" . $country_code . ".gif";
                    if (!file_exists($flag)) {
                        $flag = 'images/flags/noflag.gif';
                    }
                ?>
                    <tr>
                        <td>
                            <img src="<?= $flag; ?>" width="30" height="15">&nbsp;
                            <?php
                            if ($country_code != __('Unknown') && $country_code && isset($countries[$country_code][1])) {
                                echo $countries[$country_code][1] . '&nbsp;(' . $country_code . ')';
                            } else {
                                echo $country_code;
                            }
                            ?>
                        </td>
                        <td><?= $statDb->count_country_code; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
<?php } ?>
<div class="row mb-2">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <?= $humo_option['ip_api_collection'] != '' ? '<b>' . __('Collection of country statistics is disabled!') . '</b><br>' : ''; ?>
        <a href="index.php?page=settings#country_statistics"><?= __('Settings for country statistics'); ?></a>
    </div>
</div>

<?php
$nr_lines = 15; // *** Nr. of statistics lines ***

//$family_qry=$dbh->query("SELECT *, count(humo_stat_date.stat_easy_id) as count_lines
//	FROM humo_stat_date, humo_trees
//	WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
//	GROUP BY humo_stat_date.stat_easy_id desc
//	ORDER BY count_lines desc
//	LIMIT 0,".$nr_lines);

// *** Didn't use "GROUP BY stat_easy_id" because stat_tree_id is also needed, and 2 results in GROUP BY is not allowed in > MySQL 5.7 ***
$family_qry = $dbh->query("SELECT * FROM humo_trees as humo_trees2
RIGHT JOIN
(
    SELECT stat_tree_id, stat_gedcom_fam, stat_gedcom_man, stat_gedcom_woman, count(humo_stat_date.stat_easy_id) as count_lines FROM humo_stat_date
    GROUP BY stat_tree_id, stat_gedcom_fam, stat_gedcom_man, stat_gedcom_woman
) as humo_stat_date2
ON humo_trees2.tree_id=humo_stat_date2.stat_tree_id ORDER BY count_lines desc LIMIT 0," . $nr_lines);
?>

<h2 align="center"><?= $nr_lines; ?> <?= __('Most visited families:'); ?></h2>
<div class="row mb-2">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <table class="table">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th><?= __('family tree'); ?></th>
                    <th><?= __('family'); ?></th>
                </tr>
            </thead>
            <?php
            while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
                statistics_line($familyDb);
            }
            ?>
        </table>
    </div>
</div>

<?php
//$family_qry=$dbh->query("SELECT * FROM humo_stat_date, humo_trees
//	WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
//	ORDER BY humo_stat_date.stat_date_stat DESC LIMIT 0,".$nr_lines);
// *** First line is a bit strange, but was needed for a specific provider ***
$family_qry = $dbh->query("SELECT humo_stat_date.* , humo_trees.tree_id, humo_trees.tree_prefix FROM humo_stat_date, humo_trees 
    WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id 
    ORDER BY humo_stat_date.stat_date_stat DESC LIMIT 0," . $nr_lines);
?>
<h2 align="center"><?= $nr_lines; ?> <?= __('last visited families:'); ?></h2>
<div class="row mb-2">
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <table class="table">
            <thead class="table-primary">
                <tr>
                    <th><?= __('family tree'); ?></th>
                    <th><?= __('date-time'); ?></th>
                    <th><?= __('family'); ?></th>
                </tr>
            </thead>
            <?php
            while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
                statistics_line($familyDb);
            }
            ?>
        </table>
    </div>
</div>

<?php
// *** Show 1 statistics line ***
function statistics_line($familyDb)
{
    global $selected_language, $db_functions;

    $personPrivacy = new PersonPrivacy();
    $personName = new PersonName();
    $showTreeText = new showTreeText();
    $processLinks = new ProcessLinks();

    $tree_id = $familyDb->tree_id;
    if (isset($tree_id) && $tree_id) {
        $db_functions->set_tree_id($tree_id);
    }
?>
    <tr>
        <?php if (isset($familyDb->count_lines)) { ?>
            <td><?= $familyDb->count_lines; ?></td>
        <?php
        }

        $treetext = $showTreeText ->show_tree_text($familyDb->tree_id, $selected_language);
        ?>
        <td><?= $treetext['name']; ?></td>

        <?php if (!isset($familyDb->count_lines)) { ?>
            <td><?= $familyDb->stat_date_stat; ?></td>
        <?php
        }

        // *** Check if family is still in the genealogy! ***
        $checkDb = $db_functions->get_family($familyDb->stat_gedcom_fam);
        $check = false;
        if ($checkDb && $checkDb->fam_man == $familyDb->stat_gedcom_man && $checkDb->fam_woman == $familyDb->stat_gedcom_woman) {
            $check = true;
        }

        ?>
        <td>
            <?php
            if ($check == true) {
                $vars['pers_family'] = $familyDb->stat_gedcom_fam;
                $link = $processLinks->get_link('../', 'family', $familyDb->tree_id, false, $vars);
                echo '<a href="' . $link . '">' . __('Family') . ': </a>';

                //*** Man ***
                $personDb = $db_functions->get_person($familyDb->stat_gedcom_man);

                if (!$familyDb->stat_gedcom_man) {
                    echo 'N.N.';
                } else {
                    $privacy = $personPrivacy->get_privacy($personDb);
                    $name = $personName->get_person_name($personDb, $privacy);
                    echo $name["standard_name"];
                }

                echo " &amp; ";

                //*** Woman ***
                $personDb = $db_functions->get_person($familyDb->stat_gedcom_woman);
                if (!$familyDb->stat_gedcom_woman) {
                    echo 'N.N.';
                } else {
                    $privacy = $personPrivacy->get_privacy($personDb);
                    $name = $personName->get_person_name($personDb, $privacy);
                    echo $name["standard_name"];
                }
            } else {
                echo '<b>' . __('FAMILY NOT FOUND IN FAMILY TREE') . '</b>';
            }
            ?>
        </td>
    </tr>
<?php
}
