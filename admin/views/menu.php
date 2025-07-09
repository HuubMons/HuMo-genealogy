<?php

/**
 * Admin menu
 */

$popup_style = '';
//if ($popup == true){
//  $popup_style = ' style="top:0px;"';
//}

if ($page != 'login' && $page != 'update') {
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    }
}

$menu_path_website = '../index.php';
$menu_path_logoff = 'index.php?log_off=1';

if ($popup == false) {
?>
    <!-- Bootstrap menu using hoover effect -->
    <!-- Example from: https://bootstrap-menu.com/detail-basic-hover.html -->
    <!-- <nav class="mt-5 navbar navbar-expand-lg bg-light border-bottom border-success"> -->
    <!-- <nav class="mt-5 navbar navbar-expand-lg border-bottom border-success genealogy_menu" style="margin: 0 !important;"> -->
    <!-- <nav class="mt-5 navbar navbar-expand-lg border-bottom border-dark-subtle genealogy_menu"> -->
    <!-- <nav class="navbar navbar-expand-lg border-bottom border-dark-subtle genealogy_menu"> -->
    <!-- <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;"> -->
    <!-- <nav class="navbar navbar-expand-lg border-bottom border-dark-subtle" style="background: linear-gradient(rgb(244, 244, 255) 0%, rgb(219, 219, 219) 100%);"> -->
    <nav class="navbar navbar-expand-lg bg-light border-bottom border-dark-subtle">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main_nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="main_nav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php if ($page == 'admin') echo 'active'; ?>" href="index.php?page=admin" data-bs-toggle="dropdown">
                        <!-- <img src="../images/menu_mobile.png" width="18" alt="<?= __('Administration'); ?>"> -->
                        <?= __('Home'); ?>
                    </a>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item <?php if ($page == 'admin') echo 'active'; ?>" href="index.php?page=admin"><?= __('Administration'); ?> - <?= __('Main menu'); ?></a></li>
                        <li><a class="dropdown-item" href="<?= $menu_path_website; ?>"><?= __('Website'); ?></a></li>

                        <?php if (isset($_SESSION["user_name_admin"])) {; ?>
                            <li><a class="dropdown-item" href="<?= $menu_path_logoff ?>"><?= __('Logoff'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>

                <!-- Control -->
                <?php if ($main_admin['show_menu'] == true and $page != 'login') {; ?>
                    <?php if ($group_administrator == 'j') {; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php if ($page == 'install' || $page == 'extensions' || $page == 'settings' || $page == 'settings_homepage' || $page == 'settings_special' || $page == 'edit_cms_pages' || $page == 'language_editor' || $page == 'prefix_editor' || $page == 'maps')  echo 'active'; ?>" href="index.php?page=admin" data-bs-toggle="dropdown"><?= __('Control'); ?></a>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item <?php if ($page == 'install') echo 'active'; ?>" href="index.php?page=install"><?= __('Install'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'extensions') echo 'active'; ?>" href="index.php?page=extensions"><?= __('Extensions'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'settings') echo 'active'; ?>" href="index.php?page=settings"><?= __('Settings'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'settings_homepage') echo 'active'; ?>" href="index.php?page=settings&amp;menu_admin=settings_homepage"><?= __('Homepage'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'settings_special') echo 'active'; ?>" href="index.php?page=settings&amp;menu_admin=settings_special"><?= __('Special settings'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'edit_cms_pages') echo 'active'; ?>" href="index.php?page=edit_cms_pages"><?= __('CMS Own pages'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'language_editor') echo 'active'; ?>" href="index.php?page=language_editor"><?= __('Language editor'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'prefix_editor') echo 'active'; ?>" href="index.php?page=prefix_editor"><?= __('Prefix editor'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'maps') echo 'active'; ?>" href="index.php?page=maps"><?= __('World map'); ?></a></li>
                            </ul>
                        </li>

                        <!-- Family trees -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php if ($page == 'tree' || $page == 'thumbs' || $page == 'notes' || $page == 'check' || $page == 'latest_changes' || $page == 'cal_date' || $page == 'export' || $page == 'backup' || $page == 'statistics') echo 'active'; ?>" href="index.php?page=tree" data-bs-toggle="dropdown"><?= __('Family trees'); ?></a>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item <?php if ($page == 'tree') echo 'active'; ?>" href="index.php?page=tree"><?= __('Family trees'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'thumbs') echo 'active'; ?>" href="index.php?page=thumbs"><?= __('Pictures/ create thumbnails'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'notes') echo 'active'; ?>" href="index.php?page=notes"><?= __('Notes'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'check') echo 'active'; ?>" href="index.php?page=check"><?= __('Family tree data check'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'latest_changes') echo 'active'; ?>" href="index.php?page=check&amp;tab=changes"><?= __('View latest changes'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'cal_date') echo 'active'; ?>" href="index.php?page=cal_date"><?= __('Calculated birth date'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'export') echo 'active'; ?>" href="index.php?page=export"><?= __('Gedcom export'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'backup') echo 'active'; ?>" href="index.php?page=backup"><?= __('Database backup'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'statistics') echo 'active'; ?>" href="index.php?page=statistics"><?= __('Statistics'); ?></a></li>
                            </ul>
                        </li>
                    <?php } ?>

                    <!-- Editor -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php if ($page == 'editor' || $page == 'edit_sources' || $page == 'edit_repositories' || $page == 'edit_addresses' || $page == 'edit_places') echo 'active'; ?>" href="index.php?page=editor" data-bs-toggle="dropdown"><?= __('Editor'); ?></a>

                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php if ($page == 'editor') echo 'active'; ?>" href="index.php?page=editor"><?= __('Persons and families'); ?></a></li>
                            <li><a class="dropdown-item <?php if ($page == 'edit_sources') echo 'active'; ?>" href="index.php?page=edit_sources"><?= __('Sources'); ?></a></li>
                            <li><a class="dropdown-item <?php if ($page == 'edit_repositories') echo 'active'; ?>" href="index.php?page=edit_repositories"><?= __('Repositories'); ?></a></li>
                            <li><a class="dropdown-item <?php if ($page == 'edit_addresses') echo 'active'; ?>" href="index.php?page=edit_addresses"><?= __('Shared addresses'); ?></a></li>
                            <li><a class="dropdown-item <?php if ($page == 'edit_places') echo 'active'; ?>" href="index.php?page=edit_places"><?= __('Rename places'); ?></a></li>
                        </ul>
                    </li>

                    <!-- Users -->
                    <?php if ($group_administrator == 'j') {; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php if ($page == 'users' || $page == 'groups' || $page == 'log') echo 'active'; ?>" href="index.php?page=users" data-bs-toggle="dropdown"><?= __('Users'); ?></a>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item <?php if ($page == 'users') echo 'active'; ?>" href="index.php?page=users"><?= __('Users'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'groups') echo 'active'; ?>" href="index.php?page=groups"><?= __('User groups'); ?></a></li>
                                <li><a class="dropdown-item <?php if ($page == 'log') echo 'active'; ?>" href="index.php?page=log"><?= __('Log'); ?></a></li>
                            </ul>
                        </li>
                    <?php } ?>
                <?php } ?>

                <!-- Select language using country flags -->
                <li class="nav-item dropdown">
                    <?php include_once(__DIR__ . "/../../views/partial/select_language.php"); ?>
                    <?php /*
                    $language_path = $processLinks->get_link('', 'language', '', true);
                    */
                    ?>
                    <?php $language_path = 'index.php?'; ?>
                    <?= show_country_flags($selected_language, '../', 'language_choice', $language_path); ?>
                </li>

                <?php if ($main_admin['show_menu'] == true and $page != 'login') {; ?>
                    <div class="pt-2 ms-3">
                        <!-- Extra link: Add person -->
                        &nbsp;&nbsp;&nbsp;<a href="index.php?page=editor&amp;add_person=1">
                            <img src="images/person_connect.gif" border="0" title="<?= __('Add person'); ?>" alt="<?= __('Add person'); ?>"> <?= __('Add person'); ?></a>
                    </div>
                <?php } ?>
            </ul>

            <!-- Button to open the offcanvas sidebar -->
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#demo">
                <?= __('Open Sidebar'); ?>
            </button>

        </div>
    </nav>

<?php
}
