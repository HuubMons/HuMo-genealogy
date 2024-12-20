<?php
class TreeTextModel
{
    public function get_tree_texts($dbh, $tree_id, $language)
    {
        $data2sql = $dbh->query("SELECT * FROM humo_tree_texts WHERE treetext_tree_id='" . $tree_id . "' AND treetext_language='" . $language . "'");
        $data2Db = $data2sql->fetch(PDO::FETCH_OBJ);
        if ($data2Db) {
            $trees['treetext_id'] = $data2Db->treetext_id;
            $trees['treetext_name'] = $data2Db->treetext_name;
            $trees['treetext_mainmenu_text'] = $data2Db->treetext_mainmenu_text;
            $trees['treetext_mainmenu_source'] = $data2Db->treetext_mainmenu_source;
            $trees['treetext_family_top'] = $data2Db->treetext_family_top;
            $trees['treetext_family_footer'] = $data2Db->treetext_family_footer;
        } else {
            $trees['treetext_id'] = '';
            $trees['treetext_name'] = __('NO NAME');
            $trees['treetext_mainmenu_text'] = '';
            $trees['treetext_mainmenu_source'] = '';
            $trees['treetext_family_top'] = '';
            $trees['treetext_family_footer'] = '';
        }
        return $trees;
    }
}
