<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration3
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        try {
            $this->dbh->query("DROP TABLE humo_cms_menu");
        } catch (Exception $e) {
            //
        }
        print __('creating humo_cms_menu...') . '<br>';
        $this->dbh->query("CREATE TABLE humo_cms_menu (
            menu_id int(10) NOT NULL AUTO_INCREMENT,
            menu_parent_id int(10) NOT NULL DEFAULT '0',
            menu_order int(5) NOT NULL DEFAULT '0',
            menu_name varchar(25) CHARACTER SET utf8 DEFAULT '',
            PRIMARY KEY (`menu_id`)
            ) DEFAULT CHARSET=utf8");

        try {
            $this->dbh->query("DROP TABLE humo_cms_pages");
        } catch (Exception $e) {
            //
        }
        print __('creating humo_cms_pages...') . '<br>';
        $this->dbh->query("CREATE TABLE humo_cms_pages (
            page_id int(10) NOT NULL AUTO_INCREMENT,
            page_status varchar(1) CHARACTER SET utf8 DEFAULT '',
            page_menu_id int(10) NOT NULL DEFAULT '0',
            page_order int(10) NOT NULL DEFAULT '0',
            page_counter int(10) NOT NULL DEFAULT '0',
            page_date datetime,
            page_edit_date datetime,
            page_title varchar(50) CHARACTER SET utf8 DEFAULT '',
            page_text longtext CHARACTER SET utf8 DEFAULT '',
            PRIMARY KEY (`page_id`)
            ) DEFAULT CHARSET=utf8");
    }

    public function down(): void {}
}
