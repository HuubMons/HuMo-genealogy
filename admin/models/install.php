<?php
class InstallModel
{
    public function check_tables($dbh)
    {
        // *** Check if tables exists ***
        $install['table_settings'] = false;
        $install['table_trees'] = false;
        $install['table_stat_date'] = false;
        $install['table_users'] = false;
        $install['table_groups'] = false;
        $install['table_cms_menu'] = false;
        $install['table_cms_pages'] = false;
        $install['table_user_notes'] = false;
        $install['table_user_log'] = false;
        $install['table_stat_country'] = false;

        $query = $dbh->query("SHOW TABLES");
        while ($row = $query->fetch()) {
            if ($row[0] == 'humo_settings') {
                $install['table_settings'] = true;
            }
            if ($row[0] == 'humo_trees') {
                $install['table_trees'] = true;
            }
            if ($row[0] == 'humo_stat_date') {
                $install['table_stat_date'] = true;
            }
            if ($row[0] == 'humo_users') {
                $install['table_users'] = true;
            }
            if ($row[0] == 'humo_groups') {
                $install['table_groups'] = true;
            }
            if ($row[0] == 'humo_cms_menu') {
                $install['table_cms_menu'] = true;
            }
            if ($row[0] == 'humo_cms_pages') {
                $install['table_cms_pages'] = true;
            }
            if ($row[0] == 'humo_user_notes') {
                $install['table_user_notes'] = true;
            }
            if ($row[0] == 'humo_user_log') {
                $install['table_user_log'] = true;
            }
            if ($row[0] == 'humo_stat_country') {
                $install['table_stat_country'] = true;
            }
        }
        return $install;
    }
}
