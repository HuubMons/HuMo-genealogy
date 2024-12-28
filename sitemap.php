<?php
header('Content-type: text/plain; charset=UTF-8');

/**
 * Sitemap
 */

include_once(__DIR__ . "/include/db_login.php"); //Inloggen database.
include_once(__DIR__ . "/include/safe.php"); //Variabelen

// *** Needed for privacy filter ***
include_once(__DIR__ . "/include/generalSettings.php");
$GeneralSettings = new GeneralSettings();
$user = $GeneralSettings->get_user_settings($dbh);
$humo_option = $GeneralSettings->get_humo_option($dbh);

include_once(__DIR__ . "/include/personCls.php");

include_once(__DIR__ . "/include/dbFunctions.php");
$db_functions = new DbFunctions($dbh);

$person_cls = new PersonCls;

// *** Example, see: http://www.sitemaps.org/protocol.html ***
/*
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>http://www.example.com/</loc>
        <lastmod>2005-01-01</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>http://www.example.com/catalog?item=12&amp;desc=vacation_hawaii</loc>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc>http://www.example.com/catalog?item=73&amp;desc=vacation_new_zealand</loc>
        <lastmod>2004-12-23</lastmod>
        <changefreq>weekly</changefreq>
    </url>
</urlset>
*/

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n"
    . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";

// *** Family trees ***
$datasql = $db_functions->get_trees();
foreach ($datasql as $dataDb) {
    // *** Check is family tree is shown or hidden for user group ***
    $hide_tree_array = explode(";", $user['group_hide_trees']);
    if (!in_array($dataDb->tree_id, $hide_tree_array)) {
        // *** Get all family pages ***
        $person_qry = $dbh->query("SELECT fam_gedcomnumber FROM humo_families
            WHERE fam_tree_id='" . $dataDb->tree_id . "' ORDER BY fam_gedcomnumber");
        while ($personDb = $person_qry->fetch(PDO::FETCH_OBJ)) {
            // *** Use class for privacy filter ***
            //$person_cls = new PersonCls($personDb);
            //$privacy=$person_cls->privacy;

            // *** Completely filter person ***
            //if ($user["group_pers_hide_totally_act"]=='j'
            //	AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){
            //	// *** Don't show person ***
            //}
            //else{
            // *** Example ***
            //http://localhost/humo-gen/index.php?page=family&amp;database=humo2_&amp;id=F365&main_person=I1180
            // OR, using url_rewrite:
            //http://localhost/humo-gen/family/humo_//I2354/

            // *** First part of url (strip sitemap.php from path) ***
            $position = strrpos($_SERVER['PHP_SELF'], '/');

            // *** April 2022: Using full path: http://localhost/humo-genealogy/sitemap.php ***
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                $uri_path = 'https://' . $_SERVER['SERVER_NAME'] . substr($_SERVER['PHP_SELF'], 0, $position);
            } else {
                $uri_path = 'http://' . $_SERVER['SERVER_NAME'] . substr($_SERVER['PHP_SELF'], 0, $position);
            }

            if ($humo_option["url_rewrite"] == "j") {
                $person_url = $uri_path . '/family/' . $dataDb->tree_id . '/' . $personDb->fam_gedcomnumber . '/';
            } else {
                $person_url = $uri_path . '/index.php?page=family&amp;tree_id=' . $dataDb->tree_id . '&amp;id=' . $personDb->fam_gedcomnumber;
            }

            echo "<url>\r\n<loc>" . $person_url . "</loc>\r\n</url>\r\n";
            //}
        }

        // *** Get all single persons ***
        $person_qry = $dbh->query("SELECT pers_tree_id, pers_famc, pers_fams, pers_gedcomnumber, pers_own_code FROM humo_persons
            WHERE pers_tree_id='" . $dataDb->tree_id . "' AND pers_famc='' AND pers_fams=''");
        while ($personDb = $person_qry->fetch(PDO::FETCH_OBJ)) {
            // *** Use class for privacy filter ***
            //$person_cls = new PersonCls($personDb);
            //$privacy=$person_cls->privacy;

            // *** Completely filter person ***
            if (
                $user["group_pers_hide_totally_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_pers_hide_totally"]) > 0
            ) {
                // *** Don't show person ***
            } else {
                // *** Example ***
                //http://localhost/humo-gen/index.php?page=family&amp;tree_id=1&amp;id=F365&main_person=I1180
                // OR, using url_rewrite:
                //http://localhost/humo-gen/family/humo_/?&main_person=I2354

                // *** First part of url (strip sitemap.php from path) ***
                $position = strrpos($_SERVER['PHP_SELF'], '/');

                //$uri_path= substr($_SERVER['PHP_SELF'],0,$position);
                // *** April 2022: Using full path: http://localhost/humo-genealogy/sitemap.php ***
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                    $uri_path = 'https://' . $_SERVER['SERVER_NAME'] . substr($_SERVER['PHP_SELF'], 0, $position);
                } else {
                    $uri_path = 'http://' . $_SERVER['SERVER_NAME'] . substr($_SERVER['PHP_SELF'], 0, $position);
                }

                $pers_family = '';
                if ($personDb->pers_famc) {
                    $pers_family = $personDb->pers_famc;
                }
                if ($personDb->pers_fams) {
                    $pers_fams = explode(';', $personDb->pers_fams);
                    $pers_family = $pers_fams[0];
                }

                if ($humo_option["url_rewrite"] == "j") {
                    $person_url = $uri_path . '/family/' . $dataDb->tree_id . '/' . $pers_family . '?main_person=' . $personDb->pers_gedcomnumber;
                } else {
                    $person_url = $uri_path . '/index.php?page=family&amp;tree_id=' . $dataDb->tree_id . '&amp;id=' . $pers_family . '&amp;main_person=' . $personDb->pers_gedcomnumber;
                }

                echo "<url>\r\n<loc>" . $person_url . "</loc>\r\n</url>\r\n";
            }
        }
    } // *** End of hidden family tree ***
} // *** End of multiple family trees ***
unset($datasql);

echo '</urlset>';
