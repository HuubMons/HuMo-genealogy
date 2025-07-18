<?php
header('Content-type: application/xml; charset=UTF-8');

/**
 * Show sitemap
 * 
 * If number of records > 50.000: multiple sitemap files are created, and sitemap index is used.
 */

// *** Autoload composer classes ***
require __DIR__ . '/vendor/autoload.php';

include_once(__DIR__ . "/include/db_login.php");

// *** Needed for privacy filter ***
$generalSettings = new \Genealogy\Include\GeneralSettings();
$humo_option = $generalSettings->get_humo_option($dbh);

$userSettings = new \Genealogy\Include\UserSettings();
$user = $userSettings->get_user_settings($dbh);

$db_functions = new \Genealogy\Include\DbFunctions($dbh);

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

$max_loc = 49999;
//$max_loc = 600; // Test line
$loc = array();
$filenumber = 0;
// *** Family trees ***
$datasql = $db_functions->get_trees();
foreach ($datasql as $dataDb) {
    // *** Check if family tree is shown or hidden for user group ***
    $hide_tree_array = explode(";", $user['group_hide_trees']);
    if (!in_array($dataDb->tree_id, $hide_tree_array)) {
        // *** Get all family pages ***
        $person_qry = $dbh->query("SELECT fam_gedcomnumber FROM humo_families WHERE fam_tree_id='" . $dataDb->tree_id . "' ORDER BY fam_gedcomnumber");
        while ($personDb = $person_qry->fetch(PDO::FETCH_OBJ)) {
            //$personPrivacy = new PersonPrivacy();
            //$privacy=$personPrivacy->get_privacy($personDb);

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
                //$person_url = $uri_path . '/family/' . $dataDb->tree_id . '/' . $personDb->fam_gedcomnumber . '/';
                $person_url = $uri_path . '/family/' . $dataDb->tree_id . '/' . $personDb->fam_gedcomnumber;
            } else {
                $person_url = $uri_path . '/index.php?page=family&amp;tree_id=' . $dataDb->tree_id . '&amp;id=' . $personDb->fam_gedcomnumber;
            }

            $loc[] = $person_url;

            // *** Save to file ***
            if (count($loc) > $max_loc) {
                $filenumber++;
                generateSitemap($loc, 'sitemap' . $filenumber . '.xml');
                unset($loc);
            }

            //}
        }

        // *** Get all single persons ***
        $person_qry = $dbh->query("SELECT pers_tree_id, pers_famc, pers_fams, pers_gedcomnumber, pers_own_code FROM humo_persons
            WHERE pers_tree_id='" . $dataDb->tree_id . "' AND pers_famc='' AND pers_fams=''");
        while ($personDb = $person_qry->fetch(PDO::FETCH_OBJ)) {
            //$personPrivacy = new PersonPrivacy();
            //$privacy=$personPrivacy->get_privacy($personDb);

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

                // A single person doesn't have a famc or fams.
                $pers_family = '';
                //if ($personDb->pers_famc) {
                //    $pers_family = $personDb->pers_famc;
                //}
                //if ($personDb->pers_fams) {
                //    $pers_fams = explode(';', $personDb->pers_fams);
                //    $pers_family = $pers_fams[0];
                //}

                if ($humo_option["url_rewrite"] == "j") {
                    $person_url = $uri_path . '/family/' . $dataDb->tree_id . '/' . $pers_family . '?main_person=' . $personDb->pers_gedcomnumber;
                } else {
                    $person_url = $uri_path . '/index.php?page=family&amp;tree_id=' . $dataDb->tree_id . '&amp;id=' . $pers_family . '&amp;main_person=' . $personDb->pers_gedcomnumber;
                }

                $loc[] = $person_url;

                // *** Save to file ***
                if (count($loc) > $max_loc) {
                    $filenumber++;
                    generateSitemap($loc, 'sitemap' . $filenumber . '.xml');
                    unset($loc);
                }
            }
        }
    } // *** End of hidden family tree ***
} // *** End of multiple family trees ***
unset($datasql);

// *** Save last records to file ***
if ($filenumber > 0 && isset($loc) && count($loc) > 1) {
    $filenumber++;
    generateSitemap($loc, 'sitemap' . $filenumber . '.xml');
    unset($loc);
}

function generateSitemap($urls, $filename)
{
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    foreach ($urls as $url) {
        $urlElement = $xml->addChild('url');
        $urlElement->addChild('loc', htmlspecialchars($url));
        //$urlElement->addChild('lastmod', date('Y-m-d'));
        //$urlElement->addChild('changefreq', 'weekly');
        //$urlElement->addChild('priority', '0.8');
    }

    $xml->asXML($filename);
}

// *** If number of records > 50.000: multiple sitemaps were created, use sitemap index ***
// *** REMARK: don't refactor this code, layout is better using echo ***
// TODO check link of sitemap index
if ($filenumber > 0) {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
    echo "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
    for ($i = 1; $i <= $filenumber; $i++) {
        echo "  <sitemap>\r\n";
        echo '      <loc>' . $uri_path . '/sitemap' . $i . ".xml</loc>\r\n";
        echo "  </sitemap>\r\n";
    }
    echo '</sitemapindex>';
} else {
    echo '<?xml version="1.0" encoding="UTF-8"?>'. "\r\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'. "\r\n";
    foreach ($loc as $loc_value) {
        echo "  <url>\r\n";
        echo '      <loc>' . $loc_value . "</loc>\r\n";
        echo "  </url>\r\n";
    }
    echo '</urlset>';
}
