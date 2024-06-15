<?php
// *** June 2024: new general function ***
function show_tree_date($tree_date, $show_time = false)
{
    $month = ''; // *** empty date ***
    if (substr($tree_date, 5, 2) === '01') {
        $month = ' ' . __('jan') . ' ';
    }
    if (substr($tree_date, 5, 2) === '02') {
        $month = ' ' . __('feb') . ' ';
    }
    if (substr($tree_date, 5, 2) === '03') {
        $month = ' ' . __('mar') . ' ';
    }
    if (substr($tree_date, 5, 2) === '04') {
        $month = ' ' . __('apr') . ' ';
    }
    if (substr($tree_date, 5, 2) === '05') {
        $month = ' ' . __('may') . ' ';
    }
    if (substr($tree_date, 5, 2) === '06') {
        $month = ' ' . __('jun') . ' ';
    }
    if (substr($tree_date, 5, 2) === '07') {
        $month = ' ' . __('jul') . ' ';
    }
    if (substr($tree_date, 5, 2) === '08') {
        $month = ' ' . __('aug') . ' ';
    }
    if (substr($tree_date, 5, 2) === '09') {
        $month = ' ' . __('sep') . ' ';
    }
    if (substr($tree_date, 5, 2) === '10') {
        $month = ' ' . __('oct') . ' ';
    }
    if (substr($tree_date, 5, 2) === '11') {
        $month = ' ' . __('nov') . ' ';
    }
    if (substr($tree_date, 5, 2) === '12') {
        $month = ' ' . __('dec') . ' ';
    }
    $tree_date2 = substr($tree_date, 8, 2) . $month . substr($tree_date, 0, 4);
    if ($show_time) {
        $tree_date2 .= ' ' . substr($tree_date, 11, 5);
    }

    return $tree_date2;
}
