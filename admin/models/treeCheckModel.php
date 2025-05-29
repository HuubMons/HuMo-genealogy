<?php
class TreeCheckModel extends AdminBaseModel
{
    public function menu_tab()
    {
        $tab = 'check';
        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        }
        if (isset($_POST['tab'])) {
            $tab = $_POST['tab'];
        }

        return $tab;
    }
}
