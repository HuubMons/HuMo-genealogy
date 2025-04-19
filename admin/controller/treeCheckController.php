<?php
class TreeCheckController
{
    public function detail($dbh, $db_functions, $tree_id)
    {
        $tree_checkModel = new TreeCheckModel($dbh);
        $tree_check['tab'] = $tree_checkModel->menu_tab();

        if ($tree_check['tab'] == 'changes') {
            $tree_check_changesModel = new TreeCheckChangesModel($dbh);
            $tree_check['editor'] = $tree_check_changesModel->get_editor();
            $tree_check['limit'] = $tree_check_changesModel->get_limit();
            $tree_check['show_persons'] = $tree_check_changesModel->get_show_persons();
            $tree_check['show_families'] = $tree_check_changesModel->get_show_families();

            // *** Select persons if no choice is made (first time opening this page) ***
            if (!$tree_check['show_persons'] && !$tree_check['show_families']) {
                $tree_check['show_persons'] = true;
            }

            $tree_check['changes'] = $tree_check_changesModel->get_changes($dbh, $db_functions, $tree_check, $tree_id);
            $tree_check['list_editors'] = $tree_check_changesModel->get_editors($dbh, $tree_id, $tree_check);
        }
        /*
        elseif ($tree_check['tab'] == 'integrity') {
            //
        } elseif ($tree_check['tab'] == 'invalid') {
            //
        } elseif ($tree_check['tab'] == 'consistency') {
            //
        }
        */

        return $tree_check;
    }
}
