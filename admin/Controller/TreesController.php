<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\TreesModel;
use Genealogy\Admin\Models\TreeAdminModel;
use Genealogy\Admin\Models\GedcomModel;
use Genealogy\Admin\Models\TreeTextModel;
use Genealogy\Admin\Models\TreeMergeModel;

class TreesController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail($selected_language): array
    {
        $treesModel = new TreesModel($this->admin_config);

        $treesModel->set_tree_id();
        $treesModel->update_tree();
        $trees['tree_id'] = $treesModel->get_tree_id();
        $trees['language'] = $treesModel->get_language($selected_language);
        // *** Select language for texts at page ***
        $trees['language2'] = $treesModel->get_language2($trees['language'], $selected_language);
        $trees['menu_tab'] = $treesModel->get_menu_tab();

        // *** Use a seperate model for each menu tab ***
        if ($trees['menu_tab'] == 'tree_main') {
            include_once(__DIR__ . "/../../views/partial/select_language.php");
            include(__DIR__ . '/../../languages/' . $trees['language2'] . '/language_data.php');

            $tree_adminModel = new TreeAdminModel();
            $trees['count_trees'] = $tree_adminModel->count_trees($this->admin_config['dbh']);
            $trees['collation'] = $tree_adminModel->get_collation($this->admin_config['dbh']);

            $trees['language_path'] = 'index.php?page=tree&amp;tree_id=' . $trees['tree_id'] . '&amp;';
        } elseif ($trees['menu_tab'] == 'tree_gedcom') {
            include_once(__DIR__ . "/../include/gedcom_asciihtml.php");
            include_once(__DIR__ . "/../include/gedcom_anselhtml.php");
            include_once(__DIR__ . "/../include/gedcom_ansihtml.php");

            // *** Support for GEDCOM files for MAC computers ***
            // *** Still needed in april 2023. Will be deprecated in PHP 9.0!***
            // *** TODO improve processing of line_endings ***
            @ini_set('auto_detect_line_endings', TRUE);

            // Because of processing very large GEDCOM files.
            @set_time_limit(4000);

            $_SESSION['debug_person'] = 1;

            $gedcomModel = new GedcomModel();
            $trees['step'] = $gedcomModel->get_step();
            //$trees['check_processed'] = get_check_processed();

            if ($trees['step'] == '1') {
                $upload_status = $gedcomModel->upload_gedcom();
                $trees = array_merge($trees, $upload_status);

                $trees['gedcom_directory'] = $gedcomModel->get_gedcom_directory();
            }
            $trees['removed_filenames'] = $gedcomModel->remove_gedcom_files($trees);

            //if ($trees['step'] == '2') {
            //
            //}
        } elseif ($trees['menu_tab'] == 'tree_data') {
            $trees['tree_pict_path'] = $treesModel->get_tree_pict_path($this->admin_config['dbh'], $this->admin_config['tree_id']);

            // *** Check for default path ***
            if (substr($trees['tree_pict_path'], 0, 1) === '|') {
                $trees['tree_pict_path'] = substr($trees['tree_pict_path'], 1);
                $trees['default_path'] = true;
            } else {
                $trees['default_path'] = false;
            }

            //require_once __DIR__ . "/../models/tree_data.php";
            //$tree_dataModel = new TreeDataModel($this->admin_config['dbh']);
            //$trees['count_trees'] = $tree_dataModel->count_trees($this->admin_config['dbh']);
        } elseif ($trees['menu_tab'] == 'tree_text') {
            $tree_textModel = new TreeTextModel();

            // *** Select language for texts at page ***
            include(__DIR__ . '/../../languages/' . $trees['language2'] . '/language_data.php');

            $tree_texts = $tree_textModel->get_tree_texts($this->admin_config['dbh'], $trees['tree_id'], $trees['language']);
            $trees = array_merge($trees, $tree_texts);
        } elseif ($trees['menu_tab'] == 'tree_merge') {
            $treeMergeModel = new TreeMergeModel();
            $trees['relatives_merge'] = $treeMergeModel->get_relatives_merge($this->admin_config['dbh'], $trees['tree_id']);
            $treeMergeModel->update_settings($this->admin_config['db_functions']); // *** Store and reset tree merge settings ***

            $trees['show_settings'] = $treeMergeModel->show_settings_page();

            $trees['show_manual'] = $treeMergeModel->show_manual_page();
        }

        return $trees;
    }
}
