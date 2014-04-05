<?php
// *** Create all tables for a family tree ***

// *** Safety line ***
if (!defined('ADMIN_PAGE')){ exit; }

print '<p><b>'.__('creating tables:').'</b><br>';

// *** Completely remove old table ***
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."persoon");
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."person");
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating persons...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."person (
	pers_id mediumint(6) unsigned NOT NULL auto_increment,
	pers_gedcomnumber varchar(20) CHARACTER SET utf8,
	pers_tree_prefix varchar(10) CHARACTER SET utf8,
	pers_famc varchar(50) CHARACTER SET utf8,
	pers_fams varchar(150) CHARACTER SET utf8,
	pers_indexnr varchar(20) CHARACTER SET utf8,
	pers_firstname varchar(50) CHARACTER SET utf8,
	pers_callname varchar(50) CHARACTER SET utf8,
	pers_prefix varchar(20) CHARACTER SET utf8,
	pers_lastname varchar(50) CHARACTER SET utf8,
	pers_patronym varchar(50) CHARACTER SET utf8,
	pers_name_text text CHARACTER SET utf8,
	pers_name_source text CHARACTER SET utf8,
	pers_sexe varchar(1) CHARACTER SET utf8,
	pers_sexe_source text CHARACTER SET utf8,
	pers_own_code varchar(100) CHARACTER SET utf8,
	pers_birth_place varchar(75) CHARACTER SET utf8,
	pers_birth_date varchar(35) CHARACTER SET utf8,
	pers_birth_time varchar(25) CHARACTER SET utf8,
	pers_birth_text text CHARACTER SET utf8,
	pers_birth_source text CHARACTER SET utf8,
	pers_stillborn varchar(1) CHARACTER SET utf8 DEFAULT 'n',
	pers_bapt_place varchar(75) CHARACTER SET utf8,
	pers_bapt_date varchar(35) CHARACTER SET utf8,
	pers_bapt_text text CHARACTER SET utf8,
	pers_bapt_source text CHARACTER SET utf8,
	pers_religion varchar(50) CHARACTER SET utf8,
	pers_death_place varchar(75) CHARACTER SET utf8,
	pers_death_date varchar(35) CHARACTER SET utf8,
	pers_death_time varchar(25) CHARACTER SET utf8,
	pers_death_text text CHARACTER SET utf8,
	pers_death_source text CHARACTER SET utf8,
	pers_death_cause varchar(50) CHARACTER SET utf8,
	pers_buried_place varchar(75) CHARACTER SET utf8,
	pers_buried_date varchar(35) CHARACTER SET utf8,
	pers_buried_text text CHARACTER SET utf8,
	pers_buried_source text CHARACTER SET utf8,
	pers_cremation varchar(1) CHARACTER SET utf8,
	pers_place_index text CHARACTER SET utf8,
	pers_text text CHARACTER SET utf8,
	pers_text_source text CHARACTER SET utf8,
	pers_alive varchar(20) CHARACTER SET utf8,
	pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	pers_favorite varchar(1) CHARACTER SET utf8,
	pers_unprocessed_tags text CHARACTER SET utf8,
	pers_new_date varchar(35) CHARACTER SET utf8,
	pers_new_time varchar(25) CHARACTER SET utf8,
	pers_changed_date varchar(35) CHARACTER SET utf8,
	pers_changed_time varchar(25) CHARACTER SET utf8,
	PRIMARY KEY (`pers_id`),
	KEY (pers_lastname),
	KEY (pers_gedcomnumber),
	KEY (pers_prefix)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry);

// *** Completely remove old table ***
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."gezin");
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."family");
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating family...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."family (
	fam_id mediumint(6) unsigned NOT NULL auto_increment,
	fam_gedcomnumber varchar(20) CHARACTER SET utf8,
	fam_man varchar(20) CHARACTER SET utf8,
	fam_woman varchar(20) CHARACTER SET utf8,
	fam_children text CHARACTER SET utf8,
	fam_kind varchar(50) CHARACTER SET utf8,
	fam_relation_date varchar(35) CHARACTER SET utf8,
	fam_relation_place varchar(75) CHARACTER SET utf8,
	fam_relation_text text CHARACTER SET utf8,
	fam_relation_source text CHARACTER SET utf8,
	fam_relation_end_date varchar(35) CHARACTER SET utf8,
	fam_marr_notice_date varchar(35) CHARACTER SET utf8,
	fam_marr_notice_place varchar(75) CHARACTER SET utf8,
	fam_marr_notice_text text CHARACTER SET utf8,
	fam_marr_notice_source text CHARACTER SET utf8,
	fam_marr_date varchar(35) CHARACTER SET utf8,
	fam_marr_place varchar(75) CHARACTER SET utf8,
	fam_marr_text text CHARACTER SET utf8,
	fam_marr_source text CHARACTER SET utf8,
	fam_marr_authority text CHARACTER SET utf8,
	fam_marr_church_notice_date varchar(35) CHARACTER SET utf8,
	fam_marr_church_notice_place varchar(75) CHARACTER SET utf8,
	fam_marr_church_notice_text text CHARACTER SET utf8,
	fam_marr_church_notice_source text CHARACTER SET utf8,
	fam_marr_church_date varchar(35) CHARACTER SET utf8,
	fam_marr_church_place varchar(75) CHARACTER SET utf8,
	fam_marr_church_text text CHARACTER SET utf8,
	fam_marr_church_source text CHARACTER SET utf8,
	fam_religion varchar(50) CHARACTER SET utf8,
	fam_div_date varchar(35) CHARACTER SET utf8,
	fam_div_place varchar(75) CHARACTER SET utf8,
	fam_div_text text CHARACTER SET utf8,
	fam_div_source text CHARACTER SET utf8,
	fam_div_authority text CHARACTER SET utf8,
	fam_text text CHARACTER SET utf8,
	fam_text_source text CHARACTER SET utf8,
	fam_alive int(1),
	fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	fam_counter mediumint(8),
	fam_unprocessed_tags text CHARACTER SET utf8,
	fam_new_date varchar(35) CHARACTER SET utf8,
	fam_new_time varchar(25) CHARACTER SET utf8,
	fam_changed_date varchar(35) CHARACTER SET utf8,
	fam_changed_time varchar(25) CHARACTER SET utf8,
	PRIMARY KEY (`fam_id`),
	KEY (fam_gedcomnumber),
	KEY (fam_man),
	KEY (fam_woman)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry);

$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."teksten"); // Remove old table.
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."texts"); // Remove table.
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating texts...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."texts (
	text_id mediumint(6) unsigned NOT NULL auto_increment,
	text_gedcomnr varchar(20) CHARACTER SET utf8,
	text_text text CHARACTER SET utf8,
	text_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	text_unprocessed_tags text CHARACTER SET utf8,
	text_new_date varchar(35) CHARACTER SET utf8,
	text_new_time varchar(25) CHARACTER SET utf8,
	text_changed_date varchar(35) CHARACTER SET utf8,
	text_changed_time varchar(25) CHARACTER SET utf8,
	KEY (text_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry);

$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."bronnen"); // Remove old table.
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."sources"); // Remove table.
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating sources...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."sources (
	source_id mediumint(6) unsigned NOT NULL auto_increment,
	source_status varchar(10) CHARACTER SET utf8,
	source_gedcomnr varchar(20) CHARACTER SET utf8,
	source_order mediumint(6),
	source_title text CHARACTER SET utf8,
	source_abbr varchar(50) CHARACTER SET utf8,
	source_date varchar(35) CHARACTER SET utf8,
	source_publ varchar(150) CHARACTER SET utf8,
	source_place varchar(75) CHARACTER SET utf8,
	source_refn varchar(50) CHARACTER SET utf8,
	source_auth varchar(50) CHARACTER SET utf8,
	source_subj varchar(50) CHARACTER SET utf8,
	source_item varchar(30) CHARACTER SET utf8,
	source_kind varchar(50) CHARACTER SET utf8,
	source_text text CHARACTER SET utf8,
	source_photo text CHARACTER SET utf8,
	source_repo_name varchar(50) CHARACTER SET utf8,
	source_repo_caln varchar(50) CHARACTER SET utf8,
	source_repo_page varchar(50) CHARACTER SET utf8,
	source_repo_gedcomnr varchar(20) CHARACTER SET utf8,
	source_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	source_unprocessed_tags text CHARACTER SET utf8,
	source_new_date varchar(35) CHARACTER SET utf8,
	source_new_time varchar(25) CHARACTER SET utf8,
	source_changed_date varchar(35) CHARACTER SET utf8,
	source_changed_time varchar(25) CHARACTER SET utf8,
	KEY (`source_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry);

$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."adressen"); // Remove old table.
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."addresses"); // Remove table.
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating addresses...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."addresses (
	address_id mediumint(6) unsigned NOT NULL auto_increment,
	address_gedcomnr varchar(20) CHARACTER SET utf8,
	address_order mediumint(6),
	address_person_id varchar(20) CHARACTER SET utf8,
	address_family_id varchar(20) CHARACTER SET utf8,
	address_address text CHARACTER SET utf8,
	address_zip varchar(20) CHARACTER SET utf8,
	address_place varchar(75) CHARACTER SET utf8,
	address_phone varchar(20) CHARACTER SET utf8,
	address_date varchar(35) CHARACTER SET utf8,
	address_source text CHARACTER SET utf8,
	address_text text CHARACTER SET utf8,
	address_photo text CHARACTER SET utf8,
	address_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	address_unprocessed_tags text CHARACTER SET utf8,
	address_new_date varchar(35) CHARACTER SET utf8,
	address_new_time varchar(25) CHARACTER SET utf8,
	address_changed_date varchar(35) CHARACTER SET utf8,
	address_changed_time varchar(25) CHARACTER SET utf8,
	PRIMARY KEY (`address_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry) or die(mysql_error());

$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."vermeldingen"); // Remove old table.
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."events"); // Remove table.
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating notes...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."events (
	event_id mediumint(6) unsigned NOT NULL auto_increment,
	event_gedcomnr varchar(20) CHARACTER SET utf8,
	event_order mediumint(6),
	event_person_id varchar(20) CHARACTER SET utf8,
	event_family_id varchar(20) CHARACTER SET utf8,
	event_kind varchar(20) CHARACTER SET utf8,
	event_event text CHARACTER SET utf8,
	event_event_extra text CHARACTER SET utf8,
	event_gedcom varchar(10) CHARACTER SET utf8,
	event_date varchar(35) CHARACTER SET utf8,
	event_place varchar(75) CHARACTER SET utf8,
	event_source text CHARACTER SET utf8,
	event_text text CHARACTER SET utf8,
	event_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	event_unprocessed_tags text CHARACTER SET utf8,
	event_new_date varchar(35) CHARACTER SET utf8,
	event_new_time varchar(25) CHARACTER SET utf8,
	event_changed_date varchar(35) CHARACTER SET utf8,
	event_changed_time varchar(25) CHARACTER SET utf8,
	PRIMARY KEY (`event_id`),
	KEY (event_person_id),
	KEY (event_family_id),
	KEY (event_kind)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry);

// *** Sources connections table ***
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."connections"); // Remove table.
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating connections...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."connections (
	connect_id mediumint(6) unsigned NOT NULL auto_increment,
	connect_order mediumint(6),
	connect_kind varchar(25) CHARACTER SET utf8,
	connect_sub_kind varchar(30) CHARACTER SET utf8,
	connect_connect_id varchar(20) CHARACTER SET utf8,
	connect_date varchar(35) CHARACTER SET utf8,
	connect_place varchar(75) CHARACTER SET utf8,
	connect_time varchar(25) CHARACTER SET utf8,
	connect_page text CHARACTER SET utf8,
	connect_role varchar(75) CHARACTER SET utf8,
	connect_text text CHARACTER SET utf8,
	connect_source_id varchar(20) CHARACTER SET utf8,
	connect_item_id varchar(20) CHARACTER SET utf8,
	connect_status varchar(10) CHARACTER SET utf8,
	connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	connect_unprocessed_tags text CHARACTER SET utf8,
	connect_new_date varchar(35) CHARACTER SET utf8,
	connect_new_time varchar(25) CHARACTER SET utf8,
	connect_changed_date varchar(35) CHARACTER SET utf8,
	connect_changed_time varchar(25) CHARACTER SET utf8,
	PRIMARY KEY (`connect_id`),
	KEY (connect_connect_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry);

// *** Repositories table ***
$tbldb = $dbh->query("DROP TABLE ".$_SESSION['tree_prefix']."repositories"); // Remove table.
// *** Generate new table ***
print $_SESSION['tree_prefix'].__('creating repositories...').'<br>';
$tbldbqry = "CREATE TABLE ".$_SESSION['tree_prefix']."repositories (
	repo_id mediumint(6) unsigned NOT NULL auto_increment,
	repo_gedcomnr varchar(20) CHARACTER SET utf8,
	repo_name text CHARACTER SET utf8,
	repo_address text CHARACTER SET utf8,
	repo_zip varchar(20) CHARACTER SET utf8,
	repo_place varchar(75) CHARACTER SET utf8,
	repo_phone varchar(20) CHARACTER SET utf8,
	repo_date varchar(35) CHARACTER SET utf8,
	repo_source text CHARACTER SET utf8,
	repo_text text CHARACTER SET utf8,
	repo_photo text CHARACTER SET utf8,
	repo_mail varchar(100) CHARACTER SET utf8,
	repo_url varchar(150) CHARACTER SET utf8,
	repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
	repo_unprocessed_tags text CHARACTER SET utf8,
	repo_new_date varchar(35) CHARACTER SET utf8,
	repo_new_time varchar(25) CHARACTER SET utf8,
	repo_changed_date varchar(35) CHARACTER SET utf8,
	repo_changed_time varchar(25) CHARACTER SET utf8,
	PRIMARY KEY (`repo_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$tbldb = $dbh->query($tbldbqry);
print '<b>'.__('No error messages above? In that case the tables have been created!').'</b><br>';
?>