<?php
class AdminSourcesModel extends AdminBaseModel
{
    public function get_pers_gedcomnumber(): string
    {
        if (isset($_SESSION['admin_pers_gedcomnumber'])) {
            return $_SESSION['admin_pers_gedcomnumber'];
        }
    }

    public function get_fam_gedcomnumber(): string
    {
        if (isset($_SESSION['admin_fam_gedcomnumber'])) {
            return $_SESSION['admin_fam_gedcomnumber'];
        }
    }

    // *** Use the null coalescing operator "??" to check for isset($_GET['connect_kind']) ***
    public function get_connect_kind()
    {
        return $_GET['connect_kind'] ?? '';
    }
    public function get_connect_sub_kind()
    {
        return $_GET['connect_sub_kind'] ?? '';
    }
    public function get_connect_connect_id()
    {
        return $_GET['connect_connect_id'] ?? '';
    }

    public function get_header_connect_kind($connect_sub_kind): array
    {
        $editSources['source_header'] = '';
        $editSources['connect_kind'] = '';

        if ($connect_sub_kind == 'pers_name_source') {
            $editSources['source_header'] = __('Name');
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by sex ***
        if ($connect_sub_kind == 'pers_sexe_source') {
            $editSources['source_header'] = __('Sex');
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by birth ***
        if ($connect_sub_kind == 'pers_birth_source') {
            $editSources['source_header'] = ucfirst(__('born'));
            $editSources['connect_kind'] = 'person';
        }

        // *** Sept. 2024: new seperate item ***
        if ($connect_sub_kind == 'birth_decl_source') {
            $editSources['source_header'] = ucfirst(__('born'));
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by baptise ***
        if ($connect_sub_kind == 'pers_bapt_source') {
            $editSources['source_header'] = ucfirst(__('baptised'));
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by death ***
        if ($connect_sub_kind == 'pers_death_source') {
            $editSources['source_header'] = ucfirst(__('died'));
            $editSources['connect_kind'] = 'person';
        }

        // *** Sept. 2024: new seperate item ***
        if ($connect_sub_kind == 'death_decl_source') {
            $editSources['source_header'] = ucfirst(__('died'));
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by buried ***
        if ($connect_sub_kind == 'pers_buried_source') {
            $editSources['source_header'] = ucfirst(__('buried'));
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by text ***
        if ($connect_sub_kind == 'pers_text_source') {
            $editSources['source_header'] = __('source');
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by person ***
        if ($connect_sub_kind == 'person_source') {
            $editSources['source_header'] = __('person');
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by person-address connection by person ***
        if ($connect_sub_kind == 'pers_address_connect_source') {
            $editSources['source_header'] = __('Address');
            $editSources['connect_kind'] = 'person';
        }

        // *** Edit source by living together ***
        if ($connect_sub_kind == 'fam_relation_source') {
            $editSources['source_header'] = __('Living together');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by fam_marr_notice ***
        if ($connect_sub_kind == 'fam_marr_notice_source') {
            $editSources['source_header'] = __('Notice of Marriage');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by fam_marr ***
        if ($connect_sub_kind == 'fam_marr_source') {
            $editSources['source_header'] = __('Marriage');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by fam_church_notice ***
        if ($connect_sub_kind == 'fam_marr_church_notice_source') {
            $editSources['source_header'] = __('Religious Notice of Marriage');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by fam_marr_church ***
        if ($connect_sub_kind == 'fam_marr_church_source') {
            $editSources['source_header'] = __('Religious Marriage');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by fam_div ***
        if ($connect_sub_kind == 'fam_div_source') {
            $editSources['source_header'] = __('Divorce');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by fam_text ***
        if ($connect_sub_kind == 'fam_text_source') {
            $editSources['source_header'] = __('text');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by relation ***
        if ($connect_sub_kind == 'family_source') {
            $editSources['source_header'] = __('relation');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by family-address connection by family ***
        if ($connect_sub_kind == 'fam_address_connect_source') {
            $editSources['source_header'] = __('Address');
            $editSources['connect_kind'] = 'family';
        }

        // *** Edit source by address (in address editor) AND ADD ADDRES-SOURCE IN PERSON/ FAMILY SCREEN ***
        if ($connect_sub_kind == 'address_source') {
            $editSources['source_header'] = __('Address');
            $editSources['connect_kind'] = 'address';
            //echo '<p>';
        }

        // *** Edit source by person event ***
        if ($connect_sub_kind == 'pers_event_source') {
            $editSources['source_header'] = __('Event');
            $editSources['connect_kind'] = 'person';
            //echo '<p>';
        }

        // *** Edit source by family event ***
        if ($connect_sub_kind == 'fam_event_source') {
            $editSources['source_header'] = __('Event');
            $editSources['connect_kind'] = 'family';
            //echo '<p>';
        }
        return $editSources;
    }
}
