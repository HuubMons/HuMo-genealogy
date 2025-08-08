<?php

/**
 * Use class. Name: TreeText.
 */

namespace Genealogy\Include;

use PDO;

class ShowTreeText
{
    public function show_tree_text($tree_id, $selected_language): array
    {
        global $dbh;

        // *** Standard tree text values ***
        $treetext_array['name'] = __('NO NAME');
        $treetext_array['mainmenu_text'] = '';
        $treetext_array['mainmenu_source'] = '';
        $treetext_array['family_top'] = '';
        $treetext_array['family_footer'] = '';

        // *** Check for tree texts in selected language ***
        $sql = "SELECT * FROM humo_trees 
            LEFT JOIN humo_tree_texts ON humo_trees.tree_id = humo_tree_texts.treetext_tree_id 
            AND treetext_language = :selected_language 
            WHERE tree_id = :tree_id";
        $datasql = $dbh->prepare($sql);
        $datasql->execute([
            ':selected_language' => $selected_language,
            ':tree_id' => $tree_id
        ]);
        $treeText = $datasql->fetch(PDO::FETCH_OBJ);
        if (isset($treeText->treetext_name)) {
            $treetext_array['id'] = $treeText->treetext_id;
            $treetext_array['name'] = $treeText->treetext_name;
            $treetext_array['mainmenu_text'] = $treeText->treetext_mainmenu_text;
            $treetext_array['mainmenu_source'] = $treeText->treetext_mainmenu_source;
            $treetext_array['family_top'] = $treeText->treetext_family_top;
            $treetext_array['family_footer'] = $treeText->treetext_family_footer;

            return $treetext_array;
        }

        // *** No text found for selected language, use default tree text ***
        $sql = "SELECT * FROM humo_trees 
            LEFT JOIN humo_tree_texts ON humo_trees.tree_id = humo_tree_texts.treetext_tree_id 
            AND treetext_language = :default_language 
            WHERE tree_id = :tree_id";
        $datasql = $dbh->prepare($sql);
        $datasql->execute([
            ':default_language' => 'default',
            ':tree_id' => $tree_id
        ]);
        $treeText = $datasql->fetch(PDO::FETCH_OBJ);
        if (isset($treeText->treetext_name)) {
            $treetext_array['id'] = $treeText->treetext_id;
            $treetext_array['name'] = $treeText->treetext_name;
            $treetext_array['mainmenu_text'] = $treeText->treetext_mainmenu_text;
            $treetext_array['mainmenu_source'] = $treeText->treetext_mainmenu_source;
            $treetext_array['family_top'] = $treeText->treetext_family_top;
            $treetext_array['family_footer'] = $treeText->treetext_family_footer;

            return $treetext_array;
        }

        // *** No texts found, final try to show some texts ***
        $sql = "SELECT * FROM humo_trees 
            LEFT JOIN humo_tree_texts ON humo_trees.tree_id = humo_tree_texts.treetext_tree_id 
            AND treetext_name LIKE :like_name 
            WHERE tree_id = :tree_id";
        $datasql = $dbh->prepare($sql);
        $datasql->execute([
            ':like_name' => '_%',
            ':tree_id' => $tree_id
        ]);
        $treeText = $datasql->fetch(PDO::FETCH_OBJ);
        if (isset($treeText->treetext_name)) {
            $treetext_array['id'] = $treeText->treetext_id;
            $treetext_array['name'] = $treeText->treetext_name;
            $treetext_array['mainmenu_text'] = $treeText->treetext_mainmenu_text;
            $treetext_array['mainmenu_source'] = $treeText->treetext_mainmenu_source;
            $treetext_array['family_top'] = $treeText->treetext_family_top;
            $treetext_array['family_footer'] = $treeText->treetext_family_footer;

            return $treetext_array;
        }

        // *** No texts found at all, return default values ***
        return $treetext_array;
    }
}
