<?php
class GroupsModel
{
    private $group_id;

    public function set_group_id()
    {
        $this->group_id = 3; // Default value

        if (isset($_POST['group_id']) and is_numeric(($_POST['group_id']))) {
            $this->group_id = $_POST['group_id'];
        }
    }
    public function get_group_id()
    {
        return $this->group_id;
    }

    public function update_group($dbh)
    {
        if (isset($_POST['group_add'])) {
            $sql = "INSERT INTO humo_groups SET group_name='new groep', group_privacy='n', group_menu_places='n', group_admin='n',
            group_sources='n', group_source_presentation='title', group_text_presentation='show', group_citation_generation='n',
            group_user_notes='n', group_user_notes_show='n', group_show_restricted_source='y',
            group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j',
            group_religion='n', group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n',
            group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n', group_texts='j',
            group_family_presentation='compact', group_maps_presentation='hide',
            group_menu_cms='y', group_menu_persons='j', group_menu_names='j', group_menu_login='j', group_menu_change_password='y',
            group_showstatistics='j', group_relcalc='j', group_googlemaps='j', group_contact='j', group_latestchanges='j',
            group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
            group_alive_date='1920', group_death_date_act='j', group_death_date='1980',
            group_filter_death='n', group_filter_total='n', group_filter_name='j',
            group_filter_fam='j', group_filter_pers_show_act='j', group_filter_pers_show='*', group_filter_pers_hide_act='n',
            group_filter_pers_hide='#'";
            $dbh->query($sql);

            $this->group_id = $dbh->lastInsertId();
        }

        if (isset($_POST['group_change'])) {
            if ($_POST["group_filter_pers_show"] == '') {
                $_POST["group_filter_pers_show"] = '*';
            }
            if ($_POST["group_filter_pers_hide"] == '') {
                $_POST["group_filter_pers_hide"] = '#';
            }
            if ($_POST["group_pers_hide_totally"] == '') {
                $_POST["group_pers_hide_totally"] = 'X';
            }

            $group_admin = 'n';
            if (isset($_POST["group_admin"])) {
                $group_admin = 'j';
            }
            //$group_editor='n'; if (isset($_POST["group_editor"])){ $group_editor='j'; }
            $group_statistics = 'n';
            if (isset($_POST["group_statistics"])) {
                $group_statistics = 'j';
            }
            $group_birthday_rss = 'n';
            if (isset($_POST["group_birthday_rss"])) {
                $group_birthday_rss = 'j';
            }
            $group_menu_cms = 'n';
            if (isset($_POST["group_menu_cms"])) {
                $group_menu_cms = 'y';
            }
            $group_menu_persons = 'n';
            if (isset($_POST["group_menu_persons"])) {
                $group_menu_persons = 'j';
            }
            $group_menu_names = 'n';
            if (isset($_POST["group_menu_names"])) {
                $group_menu_names = 'j';
            }
            $group_menu_places = 'n';
            if (isset($_POST["group_menu_places"])) {
                $group_menu_places = 'j';
            }
            $group_addresses = 'n';
            if (isset($_POST["group_addresses"])) {
                $group_addresses = 'j';
            }
            $group_pictures = 'n';
            if (isset($_POST["group_pictures"])) {
                $group_pictures = 'j';
            }
            // *** If photobook is enabled, also enable pictures ***
            $group_photobook = 'n';
            if (isset($_POST["group_photobook"])) {
                $group_photobook = 'j';
                $group_pictures = 'j';
            }
            $group_birthday_list = 'n';
            if (isset($_POST["group_birthday_list"])) {
                $group_birthday_list = 'j';
            }
            $group_showstatistics = 'n';
            if (isset($_POST["group_showstatistics"])) {
                $group_showstatistics = 'j';
            }
            $group_relcalc = 'n';
            if (isset($_POST["group_relcalc"])) {
                $group_relcalc = 'j';
            }
            $group_googlemaps = 'n';
            if (isset($_POST["group_googlemaps"])) {
                $group_googlemaps = 'j';
            }
            $group_contact = 'n';
            if (isset($_POST["group_contact"])) {
                $group_contact = 'j';
            }
            $group_latestchanges = 'n';
            if (isset($_POST["group_latestchanges"])) {
                $group_latestchanges = 'j';
            }
            $group_menu_login = 'n';
            if (isset($_POST["group_menu_login"])) {
                $group_menu_login = 'j';
            }
            $group_menu_change_password = 'n';
            if (isset($_POST["group_menu_change_password"])) {
                $group_menu_change_password = 'y';
            }
            $group_gedcomnr = 'n';
            if (isset($_POST["group_gedcomnr"])) {
                $group_gedcomnr = 'j';
            }
            $group_living_place = 'n';
            if (isset($_POST["group_living_place"])) {
                $group_living_place = 'j';
            }
            $group_places = 'n';
            if (isset($_POST["group_places"])) {
                $group_places = 'j';
            }
            $group_religion = 'n';
            if (isset($_POST["group_religion"])) {
                $group_religion = 'j';
            }
            $group_event = 'n';
            if (isset($_POST["group_event"])) {
                $group_event = 'j';
            }
            $group_own_code = 'n';
            if (isset($_POST["group_own_code"])) {
                $group_own_code = 'j';
            }
            $group_pdf_button = 'n';
            if (isset($_POST["group_pdf_button"])) {
                $group_pdf_button = 'y';
            }
            $group_rtf_button = 'n';
            if (isset($_POST["group_rtf_button"])) {
                $group_rtf_button = 'y';
            }
            $group_citation_generation = 'n';
            if (isset($_POST["group_citation_generation"])) {
                $group_citation_generation = 'y';
            }
            $group_show_age_living_person = 'n';
            if (isset($_POST["group_show_age_living_person"])) {
                $group_show_age_living_person = 'y';
            }

            //if (!isset($_POST["group_user_notes"])){ $_POST["group_user_notes"]='n'; }
            $group_user_notes = 'n';
            if (isset($_POST["group_user_notes"])) {
                $group_user_notes = 'y';
            }
            $group_user_notes_show = 'n';
            if (isset($_POST["group_user_notes_show"])) {
                $group_user_notes_show = 'y';
            }

            $group_show_restricted_source = 'n';
            if (isset($_POST["group_show_restricted_source"])) {
                $group_show_restricted_source = 'y';
            }
            $group_work_text = 'n';
            if (isset($_POST["group_work_text"])) {
                $group_work_text = 'j';
            }
            $group_text_pers = 'n';
            if (isset($_POST["group_text_pers"])) {
                $group_text_pers = 'j';
            }
            $group_texts_pers = 'n';
            if (isset($_POST["group_texts_pers"])) {
                $group_texts_pers = 'j';
            }
            $group_texts_fam = 'n';
            if (isset($_POST["group_texts_fam"])) {
                $group_texts_fam = 'j';
            }
            // *** BE AWARE: REVERSED CHECK OF VARIABLE! ***
            $group_privacy = 'j';
            if (isset($_POST["group_privacy"])) {
                $group_privacy = 'n';
            }
            $group_alive = 'n';
            if (isset($_POST["group_alive"])) {
                $group_alive = 'j';
            }
            $group_alive_date_act = 'n';
            if (isset($_POST["group_alive_date_act"])) {
                $group_alive_date_act = 'j';
            }
            $group_death_date_act = 'n';
            if (isset($_POST["group_death_date_act"])) {
                $group_death_date_act = 'j';
            }
            $group_filter_death = 'n';
            if (isset($_POST["group_filter_death"])) {
                $group_filter_death = 'j';
            }
            $group_filter_pers_show_act = 'n';
            if (isset($_POST["group_filter_pers_show_act"])) {
                $group_filter_pers_show_act = 'j';
            }
            $group_filter_pers_hide_act = 'n';
            if (isset($_POST["group_filter_pers_hide_act"])) {
                $group_filter_pers_hide_act = 'j';
            }
            $group_pers_hide_totally_act = 'n';
            if (isset($_POST["group_pers_hide_totally_act"])) {
                $group_pers_hide_totally_act = 'j';
            }
            $group_filter_date = 'n';
            if (isset($_POST["group_filter_date"])) {
                $group_filter_date = 'j';
            }
            $group_gen_protection = 'n';
            if (isset($_POST["group_gen_protection"])) {
                $group_gen_protection = 'j';
            }

            //group_editor='".$group_editor."',
            $sql = "UPDATE humo_groups SET
            group_name='" . $_POST["group_name"] . "',
            group_statistics='" . $group_statistics . "',
            group_privacy='" . $group_privacy . "',
            group_menu_places='" . $group_menu_places . "',
            group_admin='" . $group_admin . "',
            group_sources='" . $_POST["group_sources"] . "',
            group_show_restricted_source='" . $group_show_restricted_source . "',
            group_source_presentation='" . $_POST["group_source_presentation"] . "',
            group_text_presentation='" . $_POST["group_text_presentation"] . "',
            group_citation_generation='" . $group_citation_generation . "',
            group_user_notes='" . $group_user_notes . "',
            group_user_notes_show='" . $group_user_notes_show . "',
            group_birthday_rss='" . $group_birthday_rss . "',
            group_menu_cms='" . $group_menu_cms . "',
            group_menu_persons='" . $group_menu_persons . "',
            group_menu_names='" . $group_menu_names . "',
            group_menu_login='" . $group_menu_login . "',
            group_menu_change_password='" . $group_menu_change_password . "',
            group_birthday_list='" . $group_birthday_list . "',
            group_showstatistics='" . $group_showstatistics . "',
            group_relcalc='" . $group_relcalc . "',
            group_googlemaps='" . $group_googlemaps . "',
            group_contact='" . $group_contact . "',
            group_latestchanges='" . $group_latestchanges . "',
            group_photobook='" . $group_photobook . "',
            group_pictures='" . $group_pictures . "',
            group_gedcomnr='" . $group_gedcomnr . "',
            group_living_place='" . $group_living_place . "',
            group_places='" . $group_places . "',
            group_religion='" . $group_religion . "',
            group_place_date='" . $_POST["group_place_date"] . "',
            group_kindindex='" . $_POST["group_kindindex"] . "',
            group_event='" . $group_event . "',
            group_addresses='" . $group_addresses . "',
            group_own_code='" . $group_own_code . "',
            group_pdf_button='" . $group_pdf_button . "',
            group_rtf_button='" . $group_rtf_button . "',
            group_family_presentation='" . $_POST["group_family_presentation"] . "',
            group_maps_presentation='" . $_POST["group_maps_presentation"] . "',
            group_show_age_living_person='" . $group_show_age_living_person . "',
            group_work_text='" . $group_work_text . "',
            group_texts='" . $_POST["group_texts"] . "',
            group_text_pers='" . $group_text_pers . "',
            group_texts_pers='" . $group_texts_pers . "',
            group_texts_fam='" . $group_texts_fam . "',
            group_alive='" . $group_alive . "',
            group_alive_date_act='" . $group_alive_date_act . "',
            group_alive_date='" . $_POST["group_alive_date"] . "',
            group_death_date_act='" . $group_death_date_act . "',
            group_death_date='" . $_POST["group_death_date"] . "',
            group_filter_death='" . $group_filter_death . "',
            group_filter_total='" . $_POST["group_filter_total"] . "',
            group_filter_name='" . $_POST["group_filter_name"] . "',
            group_filter_fam='" . $_POST["group_filter_fam"] . "',
            group_filter_date='" . $group_filter_date . "',
            group_filter_pers_show_act='" . $group_filter_pers_show_act . "',
            group_filter_pers_show='" . $_POST["group_filter_pers_show"] . "',
            group_filter_pers_hide_act='" . $group_filter_pers_hide_act . "',
            group_filter_pers_hide='" . $_POST["group_filter_pers_hide"] . "',
            group_pers_hide_totally_act='" . $group_pers_hide_totally_act . "',
            group_pers_hide_totally='" . $_POST["group_pers_hide_totally"] . "',
            group_gen_protection='" . $group_gen_protection . "'
            WHERE group_id=" . $this->group_id;
            $dbh->query($sql);
        }

        if (isset($_POST['group_remove2'])) {
            $sql = "DELETE FROM humo_groups WHERE group_id='" . $this->group_id . "'";
            $dbh->query($sql);

            // *** Reset selected group id ***
            $this->group_id = 3;
        }
    }
}
