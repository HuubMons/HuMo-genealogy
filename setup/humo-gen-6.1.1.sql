-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 12 mai 2023 à 12:43
-- Version du serveur : 10.6.5-MariaDB
-- Version de PHP : 8.0.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `humo-gen`
--

-- --------------------------------------------------------

--
-- Structure de la table `humo_addresses`
--

DROP TABLE IF EXISTS `humo_addresses`;
CREATE TABLE IF NOT EXISTS `humo_addresses` (
  `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `address_tree_id` smallint(5) DEFAULT NULL,
  `address_gedcomnr` varchar(25) DEFAULT NULL,
  `address_shared` varchar(1) DEFAULT '',
  `address_order` mediumint(6) DEFAULT NULL,
  `address_connect_kind` varchar(25) DEFAULT NULL,
  `address_connect_sub_kind` varchar(30) DEFAULT NULL,
  `address_connect_id` varchar(25) DEFAULT NULL,
  `address_address` text DEFAULT NULL,
  `address_zip` varchar(20) DEFAULT NULL,
  `address_place` varchar(120) DEFAULT NULL,
  `address_phone` varchar(20) DEFAULT NULL,
  `address_date` varchar(35) DEFAULT NULL,
  `address_text` text DEFAULT NULL,
  `address_quality` varchar(1) DEFAULT '',
  `address_new_user` varchar(200) DEFAULT NULL,
  `address_new_date` varchar(35) DEFAULT NULL,
  `address_new_time` varchar(25) DEFAULT NULL,
  `address_changed_user` varchar(200) DEFAULT NULL,
  `address_changed_date` varchar(35) DEFAULT NULL,
  `address_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`address_id`),
  KEY `address_tree_id` (`address_tree_id`),
  KEY `address_gedcomnr` (`address_gedcomnr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_cms_menu`
--

DROP TABLE IF EXISTS `humo_cms_menu`;
CREATE TABLE IF NOT EXISTS `humo_cms_menu` (
  `menu_id` int(10) NOT NULL AUTO_INCREMENT,
  `menu_parent_id` int(10) NOT NULL DEFAULT 0,
  `menu_order` int(5) NOT NULL DEFAULT 0,
  `menu_name` varchar(25) DEFAULT '',
  PRIMARY KEY (`menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_cms_pages`
--

DROP TABLE IF EXISTS `humo_cms_pages`;
CREATE TABLE IF NOT EXISTS `humo_cms_pages` (
  `page_id` int(10) NOT NULL AUTO_INCREMENT,
  `page_status` varchar(1) DEFAULT '',
  `page_menu_id` int(10) NOT NULL DEFAULT 0,
  `page_order` int(10) NOT NULL DEFAULT 0,
  `page_counter` int(10) NOT NULL DEFAULT 0,
  `page_date` datetime DEFAULT NULL,
  `page_edit_date` datetime DEFAULT NULL,
  `page_title` varchar(50) DEFAULT '',
  `page_text` longtext DEFAULT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_connections`
--

DROP TABLE IF EXISTS `humo_connections`;
CREATE TABLE IF NOT EXISTS `humo_connections` (
  `connect_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `connect_tree_id` smallint(5) DEFAULT NULL,
  `connect_order` mediumint(6) DEFAULT NULL,
  `connect_kind` varchar(25) DEFAULT NULL,
  `connect_sub_kind` varchar(30) DEFAULT NULL,
  `connect_connect_id` varchar(25) DEFAULT NULL,
  `connect_date` varchar(35) DEFAULT NULL,
  `connect_place` varchar(120) DEFAULT NULL,
  `connect_time` varchar(25) DEFAULT NULL,
  `connect_page` text DEFAULT NULL,
  `connect_role` varchar(75) DEFAULT NULL,
  `connect_text` text DEFAULT NULL,
  `connect_source_id` varchar(25) DEFAULT NULL,
  `connect_item_id` varchar(25) DEFAULT NULL,
  `connect_status` varchar(10) DEFAULT NULL,
  `connect_quality` varchar(1) DEFAULT '',
  `connect_new_user` varchar(200) DEFAULT NULL,
  `connect_new_date` varchar(35) DEFAULT NULL,
  `connect_new_time` varchar(25) DEFAULT NULL,
  `connect_changed_user` varchar(200) DEFAULT NULL,
  `connect_changed_date` varchar(35) DEFAULT NULL,
  `connect_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`connect_id`),
  KEY `connect_connect_id` (`connect_connect_id`),
  KEY `connect_tree_id` (`connect_tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_events`
--

DROP TABLE IF EXISTS `humo_events`;
CREATE TABLE IF NOT EXISTS `humo_events` (
  `event_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_tree_id` smallint(5) DEFAULT NULL,
  `event_gedcomnr` varchar(25) DEFAULT NULL,
  `event_order` mediumint(6) DEFAULT NULL,
  `event_connect_kind` varchar(25) DEFAULT NULL,
  `event_connect_id` varchar(25) DEFAULT NULL,
  `event_pers_age` varchar(15) DEFAULT NULL,
  `event_kind` varchar(20) DEFAULT NULL,
  `event_event` text DEFAULT NULL,
  `event_event_extra` text DEFAULT NULL,
  `event_gedcom` varchar(20) DEFAULT NULL,
  `event_date` varchar(35) DEFAULT NULL,
  `event_place` varchar(120) DEFAULT NULL,
  `event_text` text DEFAULT NULL,
  `event_quality` varchar(1) DEFAULT '',
  `event_new_user` varchar(200) DEFAULT NULL,
  `event_new_date` varchar(35) DEFAULT NULL,
  `event_new_time` varchar(25) DEFAULT NULL,
  `event_changed_user` varchar(200) DEFAULT NULL,
  `event_changed_date` varchar(35) DEFAULT NULL,
  `event_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `event_tree_id` (`event_tree_id`),
  KEY `event_connect_id` (`event_connect_id`),
  KEY `event_kind` (`event_kind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_families`
--

DROP TABLE IF EXISTS `humo_families`;
CREATE TABLE IF NOT EXISTS `humo_families` (
  `fam_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fam_tree_id` mediumint(7) DEFAULT NULL,
  `fam_gedcomnumber` varchar(25) DEFAULT NULL,
  `fam_man` varchar(25) DEFAULT NULL,
  `fam_man_age` varchar(15) DEFAULT NULL,
  `fam_woman` varchar(25) DEFAULT NULL,
  `fam_woman_age` varchar(15) DEFAULT NULL,
  `fam_children` text DEFAULT NULL,
  `fam_kind` varchar(50) DEFAULT NULL,
  `fam_relation_date` varchar(35) DEFAULT NULL,
  `fam_relation_place` varchar(120) DEFAULT NULL,
  `fam_relation_text` text DEFAULT NULL,
  `fam_relation_end_date` varchar(35) DEFAULT NULL,
  `fam_marr_notice_date` varchar(35) DEFAULT NULL,
  `fam_marr_notice_place` varchar(120) DEFAULT NULL,
  `fam_marr_notice_text` text DEFAULT NULL,
  `fam_marr_date` varchar(35) DEFAULT NULL,
  `fam_marr_place` varchar(120) DEFAULT NULL,
  `fam_marr_text` text DEFAULT NULL,
  `fam_marr_authority` text DEFAULT NULL,
  `fam_marr_church_notice_date` varchar(35) DEFAULT NULL,
  `fam_marr_church_notice_place` varchar(120) DEFAULT NULL,
  `fam_marr_church_notice_text` text DEFAULT NULL,
  `fam_marr_church_date` varchar(35) DEFAULT NULL,
  `fam_marr_church_place` varchar(120) DEFAULT NULL,
  `fam_marr_church_text` text DEFAULT NULL,
  `fam_religion` varchar(50) DEFAULT NULL,
  `fam_div_date` varchar(35) DEFAULT NULL,
  `fam_div_place` varchar(120) DEFAULT NULL,
  `fam_div_text` text DEFAULT NULL,
  `fam_div_authority` text DEFAULT NULL,
  `fam_text` text DEFAULT NULL,
  `fam_alive` int(1) DEFAULT NULL,
  `fam_cal_date` varchar(35) DEFAULT NULL,
  `fam_quality` varchar(1) DEFAULT '',
  `fam_counter` mediumint(7) DEFAULT NULL,
  `fam_new_user` varchar(200) DEFAULT NULL,
  `fam_new_date` varchar(35) DEFAULT NULL,
  `fam_new_time` varchar(25) DEFAULT NULL,
  `fam_changed_user` varchar(200) DEFAULT NULL,
  `fam_changed_date` varchar(35) DEFAULT NULL,
  `fam_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`fam_id`),
  KEY `fam_tree_id` (`fam_tree_id`),
  KEY `fam_gedcomnumber` (`fam_gedcomnumber`),
  KEY `fam_man` (`fam_man`),
  KEY `fam_woman` (`fam_woman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_groups`
--

DROP TABLE IF EXISTS `humo_groups`;
CREATE TABLE IF NOT EXISTS `humo_groups` (
  `group_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` varchar(25) DEFAULT NULL,
  `group_privacy` varchar(1) DEFAULT NULL,
  `group_menu_places` varchar(1) DEFAULT NULL,
  `group_admin` varchar(1) DEFAULT NULL,
  `group_statistics` varchar(1) NOT NULL DEFAULT 'j',
  `group_menu_persons` varchar(1) NOT NULL DEFAULT 'j',
  `group_menu_names` varchar(1) NOT NULL DEFAULT 'j',
  `group_menu_login` varchar(1) NOT NULL DEFAULT 'j',
  `group_menu_cms` varchar(1) NOT NULL DEFAULT 'y',
  `group_menu_change_password` varchar(1) NOT NULL DEFAULT 'y',
  `group_birthday_rss` varchar(1) NOT NULL DEFAULT 'j',
  `group_birthday_list` varchar(1) NOT NULL DEFAULT 'j',
  `group_latestchanges` varchar(1) NOT NULL DEFAULT 'j',
  `group_contact` varchar(1) NOT NULL DEFAULT 'j',
  `group_googlemaps` varchar(1) NOT NULL DEFAULT 'j',
  `group_relcalc` varchar(1) NOT NULL DEFAULT 'j',
  `group_showstatistics` varchar(1) NOT NULL DEFAULT 'j',
  `group_sources` varchar(1) DEFAULT NULL,
  `group_show_restricted_source` varchar(1) NOT NULL DEFAULT 'y',
  `group_source_presentation` varchar(20) DEFAULT NULL,
  `group_text_presentation` varchar(20) NOT NULL DEFAULT 'show',
  `group_pictures` varchar(1) DEFAULT NULL,
  `group_photobook` varchar(1) NOT NULL DEFAULT 'n',
  `group_gedcomnr` varchar(1) DEFAULT NULL,
  `group_living_place` varchar(1) DEFAULT NULL,
  `group_places` varchar(1) DEFAULT NULL,
  `group_religion` varchar(1) DEFAULT NULL,
  `group_place_date` varchar(1) DEFAULT NULL,
  `group_kindindex` varchar(1) DEFAULT NULL,
  `group_event` varchar(1) DEFAULT NULL,
  `group_addresses` varchar(1) DEFAULT NULL,
  `group_own_code` varchar(1) DEFAULT NULL,
  `group_citation_generation` varchar(1) NOT NULL DEFAULT 'n',
  `group_user_notes` varchar(1) NOT NULL DEFAULT 'n',
  `group_user_notes_notes` varchar(1) NOT NULL DEFAULT 'n',
  `group_user_notes_show` varchar(1) NOT NULL DEFAULT 'n',
  `group_family_presentation` varchar(10) NOT NULL DEFAULT 'compact',
  `group_maps_presentation` varchar(10) NOT NULL DEFAULT 'hide',
  `group_show_age_living_person` varchar(1) NOT NULL DEFAULT 'y',
  `group_pdf_button` varchar(1) DEFAULT NULL,
  `group_rtf_button` varchar(1) NOT NULL DEFAULT 'n',
  `group_work_text` varchar(1) DEFAULT NULL,
  `group_texts` varchar(1) DEFAULT NULL,
  `group_text_pers` varchar(1) DEFAULT NULL,
  `group_texts_pers` varchar(1) DEFAULT NULL,
  `group_texts_fam` varchar(1) DEFAULT NULL,
  `group_alive` varchar(1) DEFAULT NULL,
  `group_alive_date_act` varchar(1) DEFAULT NULL,
  `group_alive_date` varchar(4) DEFAULT NULL,
  `group_death_date_act` varchar(1) DEFAULT NULL,
  `group_death_date` varchar(4) DEFAULT NULL,
  `group_filter_date` varchar(1) NOT NULL DEFAULT 'n',
  `group_filter_death` varchar(1) DEFAULT NULL,
  `group_filter_total` varchar(1) DEFAULT NULL,
  `group_filter_name` varchar(1) DEFAULT NULL,
  `group_filter_fam` varchar(1) DEFAULT NULL,
  `group_filter_pers_show_act` varchar(1) DEFAULT NULL,
  `group_filter_pers_show` varchar(50) DEFAULT NULL,
  `group_filter_pers_hide_act` varchar(1) DEFAULT NULL,
  `group_filter_pers_hide` varchar(50) DEFAULT NULL,
  `group_pers_hide_totally_act` varchar(1) NOT NULL DEFAULT 'n',
  `group_pers_hide_totally` varchar(50) NOT NULL DEFAULT 'X',
  `group_gen_protection` varchar(1) NOT NULL DEFAULT 'n',
  `group_hide_trees` varchar(200) NOT NULL DEFAULT '',
  `group_edit_trees` varchar(200) NOT NULL DEFAULT '',
  `group_hide_photocat` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_persons`
--

DROP TABLE IF EXISTS `humo_persons`;
CREATE TABLE IF NOT EXISTS `humo_persons` (
  `pers_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pers_gedcomnumber` varchar(25) DEFAULT NULL,
  `pers_tree_id` mediumint(7) DEFAULT NULL,
  `pers_tree_prefix` varchar(10) DEFAULT NULL,
  `pers_famc` varchar(50) DEFAULT NULL,
  `pers_fams` varchar(150) DEFAULT NULL,
  `pers_indexnr` varchar(25) DEFAULT NULL,
  `pers_firstname` varchar(60) DEFAULT NULL,
  `pers_callname` varchar(50) DEFAULT NULL,
  `pers_prefix` varchar(20) DEFAULT NULL,
  `pers_lastname` varchar(60) DEFAULT NULL,
  `pers_patronym` varchar(50) DEFAULT NULL,
  `pers_name_text` text DEFAULT NULL,
  `pers_sexe` varchar(1) DEFAULT NULL,
  `pers_own_code` varchar(100) DEFAULT NULL,
  `pers_birth_place` varchar(120) DEFAULT NULL,
  `pers_birth_date` varchar(35) DEFAULT NULL,
  `pers_birth_time` varchar(25) DEFAULT NULL,
  `pers_birth_text` text DEFAULT NULL,
  `pers_stillborn` varchar(1) DEFAULT 'n',
  `pers_bapt_place` varchar(120) DEFAULT NULL,
  `pers_bapt_date` varchar(35) DEFAULT NULL,
  `pers_bapt_text` text DEFAULT NULL,
  `pers_religion` varchar(50) DEFAULT NULL,
  `pers_death_place` varchar(120) DEFAULT NULL,
  `pers_death_date` varchar(35) DEFAULT NULL,
  `pers_death_time` varchar(25) DEFAULT NULL,
  `pers_death_text` text DEFAULT NULL,
  `pers_death_cause` varchar(255) DEFAULT NULL,
  `pers_death_age` varchar(15) DEFAULT NULL,
  `pers_buried_place` varchar(120) DEFAULT NULL,
  `pers_buried_date` varchar(35) DEFAULT NULL,
  `pers_buried_text` text DEFAULT NULL,
  `pers_cremation` varchar(1) DEFAULT NULL,
  `pers_place_index` text DEFAULT NULL,
  `pers_text` text DEFAULT NULL,
  `pers_alive` varchar(20) DEFAULT NULL,
  `pers_cal_date` varchar(35) DEFAULT NULL,
  `pers_quality` varchar(1) DEFAULT '',
  `pers_new_user` varchar(200) DEFAULT NULL,
  `pers_new_date` varchar(35) DEFAULT NULL,
  `pers_new_time` varchar(25) DEFAULT NULL,
  `pers_changed_user` varchar(200) DEFAULT NULL,
  `pers_changed_date` varchar(35) DEFAULT NULL,
  `pers_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`pers_id`),
  KEY `pers_prefix` (`pers_prefix`),
  KEY `pers_lastname` (`pers_lastname`),
  KEY `pers_gedcomnumber` (`pers_gedcomnumber`),
  KEY `pers_tree_id` (`pers_tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_repositories`
--

DROP TABLE IF EXISTS `humo_repositories`;
CREATE TABLE IF NOT EXISTS `humo_repositories` (
  `repo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `repo_tree_id` smallint(5) DEFAULT NULL,
  `repo_gedcomnr` varchar(25) DEFAULT NULL,
  `repo_name` text DEFAULT NULL,
  `repo_address` text DEFAULT NULL,
  `repo_zip` varchar(20) DEFAULT NULL,
  `repo_place` varchar(120) DEFAULT NULL,
  `repo_phone` varchar(25) DEFAULT NULL,
  `repo_date` varchar(35) DEFAULT NULL,
  `repo_text` text DEFAULT NULL,
  `repo_mail` varchar(100) DEFAULT NULL,
  `repo_url` varchar(150) DEFAULT NULL,
  `repo_quality` varchar(1) DEFAULT '',
  `repo_new_user` varchar(200) DEFAULT NULL,
  `repo_new_date` varchar(35) DEFAULT NULL,
  `repo_new_time` varchar(25) DEFAULT NULL,
  `repo_changed_user` varchar(200) DEFAULT NULL,
  `repo_changed_date` varchar(35) DEFAULT NULL,
  `repo_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`repo_id`),
  KEY `repo_tree_id` (`repo_tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_settings`
--

DROP TABLE IF EXISTS `humo_settings`;
CREATE TABLE IF NOT EXISTS `humo_settings` (
  `setting_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_variable` varchar(50) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_order` smallint(5) DEFAULT NULL,
  `setting_tree_id` smallint(5) DEFAULT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_sources`
--

DROP TABLE IF EXISTS `humo_sources`;
CREATE TABLE IF NOT EXISTS `humo_sources` (
  `source_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_tree_id` smallint(5) DEFAULT NULL,
  `source_status` varchar(10) DEFAULT NULL,
  `source_gedcomnr` varchar(25) DEFAULT NULL,
  `source_shared` varchar(1) DEFAULT '',
  `source_order` mediumint(6) DEFAULT NULL,
  `source_title` text DEFAULT NULL,
  `source_abbr` varchar(50) DEFAULT NULL,
  `source_date` varchar(35) DEFAULT NULL,
  `source_publ` varchar(150) DEFAULT NULL,
  `source_place` varchar(120) DEFAULT NULL,
  `source_refn` varchar(50) DEFAULT NULL,
  `source_auth` varchar(50) DEFAULT NULL,
  `source_subj` varchar(248) DEFAULT NULL,
  `source_item` varchar(30) DEFAULT NULL,
  `source_kind` varchar(50) DEFAULT NULL,
  `source_text` text DEFAULT NULL,
  `source_repo_name` varchar(50) DEFAULT NULL,
  `source_repo_caln` varchar(50) DEFAULT NULL,
  `source_repo_page` varchar(50) DEFAULT NULL,
  `source_repo_gedcomnr` varchar(25) DEFAULT NULL,
  `source_quality` varchar(1) DEFAULT '',
  `source_new_user` varchar(200) DEFAULT NULL,
  `source_new_date` varchar(35) DEFAULT NULL,
  `source_new_time` varchar(25) DEFAULT NULL,
  `source_changed_user` varchar(200) DEFAULT NULL,
  `source_changed_date` varchar(35) DEFAULT NULL,
  `source_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`source_id`),
  KEY `source_tree_id` (`source_tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_stat_date`
--

DROP TABLE IF EXISTS `humo_stat_date`;
CREATE TABLE IF NOT EXISTS `humo_stat_date` (
  `stat_id` int(10) NOT NULL AUTO_INCREMENT,
  `stat_easy_id` varchar(100) DEFAULT NULL,
  `stat_ip_address` varchar(40) DEFAULT NULL,
  `stat_user_agent` varchar(255) DEFAULT NULL,
  `stat_tree_id` varchar(5) DEFAULT NULL,
  `stat_gedcom_fam` varchar(25) DEFAULT NULL,
  `stat_gedcom_man` varchar(25) DEFAULT NULL,
  `stat_gedcom_woman` varchar(25) DEFAULT NULL,
  `stat_date_stat` datetime DEFAULT NULL,
  `stat_date_linux` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`stat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_texts`
--

DROP TABLE IF EXISTS `humo_texts`;
CREATE TABLE IF NOT EXISTS `humo_texts` (
  `text_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `text_tree_id` smallint(5) DEFAULT NULL,
  `text_gedcomnr` varchar(25) DEFAULT NULL,
  `text_text` text DEFAULT NULL,
  `text_quality` varchar(1) DEFAULT '',
  `text_new_user` varchar(200) DEFAULT NULL,
  `text_new_date` varchar(35) DEFAULT NULL,
  `text_new_time` varchar(25) DEFAULT NULL,
  `text_changed_user` varchar(200) DEFAULT NULL,
  `text_changed_date` varchar(35) DEFAULT NULL,
  `text_changed_time` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`text_id`),
  KEY `text_tree_id` (`text_tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_trees`
--

DROP TABLE IF EXISTS `humo_trees`;
CREATE TABLE IF NOT EXISTS `humo_trees` (
  `tree_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tree_order` smallint(5) DEFAULT NULL,
  `tree_prefix` varchar(10) DEFAULT NULL,
  `tree_date` varchar(20) DEFAULT NULL,
  `tree_persons` varchar(10) DEFAULT NULL,
  `tree_families` varchar(10) DEFAULT NULL,
  `tree_email` varchar(100) DEFAULT NULL,
  `tree_owner` varchar(100) DEFAULT NULL,
  `tree_pict_path` varchar(100) DEFAULT NULL,
  `tree_privacy` varchar(100) DEFAULT NULL,
  `tree_gedcom` varchar(100) DEFAULT NULL,
  `tree_gedcom_program` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`tree_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_tree_texts`
--

DROP TABLE IF EXISTS `humo_tree_texts`;
CREATE TABLE IF NOT EXISTS `humo_tree_texts` (
  `treetext_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `treetext_tree_id` smallint(5) DEFAULT NULL,
  `treetext_language` varchar(100) DEFAULT NULL,
  `treetext_name` varchar(100) DEFAULT NULL,
  `treetext_mainmenu_text` text DEFAULT NULL,
  `treetext_mainmenu_source` text DEFAULT NULL,
  `treetext_family_top` text DEFAULT NULL,
  `treetext_family_footer` text DEFAULT NULL,
  PRIMARY KEY (`treetext_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_unprocessed_tags`
--

DROP TABLE IF EXISTS `humo_unprocessed_tags`;
CREATE TABLE IF NOT EXISTS `humo_unprocessed_tags` (
  `tag_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tag_pers_id` int(10) DEFAULT NULL,
  `tag_rel_id` int(10) DEFAULT NULL,
  `tag_event_id` int(10) DEFAULT NULL,
  `tag_source_id` int(10) DEFAULT NULL,
  `tag_connect_id` int(10) DEFAULT NULL,
  `tag_repo_id` int(10) DEFAULT NULL,
  `tag_place_id` int(10) DEFAULT NULL,
  `tag_address_id` int(10) DEFAULT NULL,
  `tag_text_id` int(10) DEFAULT NULL,
  `tag_tree_id` smallint(5) DEFAULT NULL,
  `tag_tag` text DEFAULT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `tag_tree_id` (`tag_tree_id`),
  KEY `tag_pers_id` (`tag_pers_id`),
  KEY `tag_rel_id` (`tag_rel_id`),
  KEY `tag_event_id` (`tag_event_id`),
  KEY `tag_source_id` (`tag_source_id`),
  KEY `tag_connect_id` (`tag_connect_id`),
  KEY `tag_repo_id` (`tag_repo_id`),
  KEY `tag_place_id` (`tag_place_id`),
  KEY `tag_address_id` (`tag_address_id`),
  KEY `tag_text_id` (`tag_text_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_users`
--

DROP TABLE IF EXISTS `humo_users`;
CREATE TABLE IF NOT EXISTS `humo_users` (
  `user_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name` varchar(25) DEFAULT NULL,
  `user_mail` varchar(100) DEFAULT NULL,
  `user_trees` text DEFAULT NULL,
  `user_remark` text DEFAULT NULL,
  `user_password` varchar(50) DEFAULT NULL,
  `user_password_salted` varchar(255) DEFAULT NULL,
  `user_2fa_enabled` varchar(1) DEFAULT '',
  `user_2fa_auth_secret` varchar(50) DEFAULT '',
  `user_status` varchar(1) DEFAULT NULL,
  `user_group_id` smallint(5) DEFAULT NULL,
  `user_hide_trees` varchar(200) NOT NULL DEFAULT '',
  `user_edit_trees` varchar(200) NOT NULL DEFAULT '',
  `user_ip_address` varchar(45) DEFAULT '',
  `user_register_date` varchar(20) DEFAULT NULL,
  `user_last_visit` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_user_log`
--

DROP TABLE IF EXISTS `humo_user_log`;
CREATE TABLE IF NOT EXISTS `humo_user_log` (
  `log_id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_username` varchar(25) DEFAULT NULL,
  `log_date` varchar(20) DEFAULT NULL,
  `log_ip_address` varchar(45) DEFAULT '',
  `log_user_admin` varchar(5) DEFAULT '',
  `log_status` varchar(10) DEFAULT '',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `humo_user_notes`
--

DROP TABLE IF EXISTS `humo_user_notes`;
CREATE TABLE IF NOT EXISTS `humo_user_notes` (
  `note_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `note_order` smallint(5) DEFAULT NULL,
  `note_new_date` varchar(20) DEFAULT NULL,
  `note_new_time` varchar(25) DEFAULT NULL,
  `note_new_user_id` smallint(5) DEFAULT NULL,
  `note_changed_date` varchar(20) DEFAULT NULL,
  `note_changed_time` varchar(25) DEFAULT NULL,
  `note_changed_user_id` smallint(5) DEFAULT NULL,
  `note_guest_name` varchar(25) DEFAULT NULL,
  `note_guest_mail` varchar(25) DEFAULT NULL,
  `note_note` text DEFAULT NULL,
  `note_status` varchar(15) DEFAULT NULL,
  `note_priority` varchar(15) DEFAULT NULL,
  `note_tree_id` mediumint(7) DEFAULT NULL,
  `note_kind` varchar(10) DEFAULT NULL,
  `note_connect_kind` varchar(20) DEFAULT NULL,
  `note_connect_id` varchar(25) DEFAULT NULL,
  `note_names` text DEFAULT NULL,
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
