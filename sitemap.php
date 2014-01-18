<?php
header ('Content-type: text/plain; charset=UTF-8');

// **************************************************
// *** Privacy person                             ***
// **************************************************

define("CMS_ROOTPATH", '');

include_once(CMS_ROOTPATH."include/db_login.php"); //Inloggen database.
include_once(CMS_ROOTPATH."include/safe.php"); //Variabelen

// *** Needed for privacy filter ***
include_once(CMS_ROOTPATH."include/settings_global.php"); //Variables
include_once(CMS_ROOTPATH."include/settings_user.php"); // USER variables
include_once(CMS_ROOTPATH."include/person_cls.php");


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

echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n"
.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\r\n";

// *** Database ***
//$datasql = mysql_query("SELECT * FROM humo_trees ORDER BY tree_order",$db);
//$num_rows = mysql_num_rows($datasql);
$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
$num_rows = $datasql->rowCount();
//while (@$dataDb=mysql_fetch_object($datasql)){
while (@$dataDb=$datasql->fetch(PDO::FETCH_OBJ)){
	// *** Check is family tree is shown or hidden for user group ***
	$hide_tree_array=explode(";",$user['group_hide_trees']);
	$hide_tree=false;
	for ($x=0; $x<=count($hide_tree_array)-1; $x++){
		if ($hide_tree_array[$x]==$dataDb->tree_id){ $hide_tree=true; }
	}
	if ($hide_tree==false){

		//$person_qry=mysql_query("SELECT * FROM ".safe_text($dataDb->tree_prefix)."person
		//	GROUP BY pers_indexnr",$db);
		/*
		$person_qry=mysql_query("SELECT * FROM ".safe_text($dataDb->tree_prefix)."person
			WHERE pers_indexnr!='' GROUP BY pers_indexnr
			UNION SELECT * FROM ".safe_text($dataDb->tree_prefix)."person WHERE pers_indexnr=''",$db);
		*/
		$person_qry=$dbh->query("SELECT * FROM ".safe_text($dataDb->tree_prefix)."person
			WHERE pers_indexnr!='' GROUP BY pers_indexnr
			UNION SELECT * FROM ".safe_text($dataDb->tree_prefix)."person WHERE pers_indexnr=''");		
		//while (@$personDb=mysql_fetch_object($person_qry)){
		while (@$personDb=$person_qry->fetch(PDO::FETCH_OBJ)){
			// *** Use class for privacy filter ***
			$person_cls = New person_cls;
			$person_cls->construct($personDb);
			$privacy=$person_cls->privacy;

			// *** Completely filter person ***
			if ($user["group_pers_hide_totally_act"]=='j'
				AND strpos(' '.$personDb->pers_own_code,$user["group_pers_hide_totally"])>0){
				// *** Don't show person ***
			}
			else{
				// *** Example ***
				//http://localhost/humo-gen/family.php?database=humo2_&amp;id=F365&main_person=I1180
				// OR, using url_rewrite:
				//http://localhost/humo-gen/family/humo_//I2354/

				// *** First part of url (strip sitemap.php from path) ***
				$position=strrpos($_SERVER['PHP_SELF'],'/');
				$uri_path= substr($_SERVER['PHP_SELF'],0,$position);
				if ($humo_option["url_rewrite"]=="j"){
					$person_url=$uri_path.'/family/'.$dataDb->tree_prefix.'/'.$personDb->pers_indexnr.'/';
					if ($personDb->pers_indexnr==''){ $person_url.=$personDb->pers_gedcomnumber.'/'; }
				}
				else{
					$person_url=$uri_path.'/family.php?database='.$dataDb->tree_prefix.'&amp;id='.$personDb->pers_indexnr;
					if ($personDb->pers_indexnr==''){ $person_url.='&amp;main_person='.$personDb->pers_gedcomnumber; }
				}
				echo "<url>\r\n<loc>".$person_url."</loc>\r\n</url>\r\n";
			}
		}
	} // *** End of hidden family tree ***
} // *** End of multiple family trees ***

echo '</urlset>';
?>