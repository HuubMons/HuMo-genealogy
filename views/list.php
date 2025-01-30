<?php

// *** Extra reset needed for "search in all family trees" ***
if ($list["index_list"] != 'search' && $list["index_list"] != 'quicksearch') {
    unset($_SESSION["save_select_trees"]);
}

// *** Save selected "search" family tree (can be used to erase search values if tree is changed) ***
$_SESSION["save_search_tree_prefix"] = safe_text_db($_SESSION['tree_prefix']);

$order = $list["order"];
// *** Search in 1 or more family trees ***
$select_trees = $list["select_trees"];
$selection = $list["selection"];
$start = $list["start"];


$list_var = $link_cls->get_link($uri_path, 'list', $tree_id, false);
$list_var2 = $link_cls->get_link($uri_path, 'list', $tree_id, true);

if ($list["index_list"] == 'places') {
?>
    <!--  Search places -->
    <form method="post" action="<?= $list_var; ?>">
        <input type="hidden" name="index_list" value="places">

        <div class="p-2 me-sm-2 genealogy_search">
            <div class="row mb-2">
                <div class="col-2">
                    <div class="form-check">
                        <input type="Checkbox" name="select_birth" id="select_birth" value="1" <?php if ($list["select_birth"] == '1') echo ' checked'; ?> class="form-check-input">

                        <label class="form-check-label" for="select_birth">
                            <span class="place_index_selected" style="float:none;"><?= __('*'); ?></span>
                            <?= __('birth pl.'); ?>
                        </label>

                    </div>
                </div>

                <div class="col-2">
                    <div class="form-check">
                        <input type="Checkbox" name="select_bapt" value="1" <?php if ($list["select_bapt"] == '1') echo ' checked'; ?> class="form-check-input">
                        <span class="place_index_selected" style="float:none;"><?= __('~'); ?></span>
                        <?= __('bapt pl.'); ?>
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-check">
                        <input type="Checkbox" name="select_place" value="1" <?php if ($list["select_place"] == '1') echo ' checked'; ?> class="form-check-input">
                        <span class="place_index_selected" style="float:none;"><?= __('^'); ?></span>
                        <?= __('residence'); ?>
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-check">
                        <input type="Checkbox" name="select_death" value="1" <?php if ($list["select_death"] == '1') echo 'checked'; ?> class="form-check-input">
                        <span class="place_index_selected" style="float:none;"><?= __('&#134;'); ?></span>
                        <?= __('death pl.'); ?>
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-check">
                        <input type="Checkbox" name="select_buried" value="1" <?php if ($list["select_buried"] == '1') echo 'checked'; ?> class="form-check-input">
                        <span class="place_index_selected" style="float:none;"><?= __('[]'); ?></span>
                        <?= __('bur pl.'); ?>
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-check">
                        <input type="Checkbox" name="select_event" value="1" <?php if ($list["select_event"] == '1') echo ' checked'; ?> class="form-check-input">
                        <span class="place_index_selected" style="float:none;"><?= substr(__('Events'), 0, 1); ?></span>
                        <?= __('Events'); ?>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-1">
                    <label for="find_place" class="col-form-label">
                        <?= __('Find place'); ?>:
                    </label>
                </div>
                <div class="col-2">
                    <select name="part_place_name" class="form-select form-select-sm">
                        <option value="contains"><?= __('Contains'); ?></option>
                        <option value="equals" <?php if ($list["part_place_name"] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                        <option value="starts_with" <?php if ($list["part_place_name"] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                    </select>
                </div>

                <div class="col-2">
                    <input type="text" name="place_name" value="<?= safe_text_show($list["place_name"]); ?>" size="15" class="form-control form-control-sm">
                </div>

                <input type="submit" value="<?= __('Search'); ?>" name="B1" class="col-sm-1 btn btn-sm btn-success">
            </div>
        </div>
    </form>
<?php
}

// *** Search fields ***
if ($list["index_list"] == 'standard' || $list["index_list"] == 'search' || $list["index_list"] == 'quicksearch') {
    $datasql2 = $dbh->query("SELECT * FROM humo_trees");
    $num_rows2 = $datasql2->rowCount();
?>
    <!-- Standard and advanced search box -->
    <br>
    <form method="post" action="<?= $list_var; ?>">
        <div class="py-2 me-sm-3 genealogy_search">

            <!-- Standard search box -->
            <?php if ($list["adv_search"] == false) { ?>

                <div class="row">
                    <div class="col-sm-2"></div>

                    <div class="col-sm-5">
                        <?php if ($humo_option['one_name_study'] != 'y') { ?>
                            <?= __('Enter name or part of name'); ?><br>
                            <span style="font-size:12px;"><?= __('"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"'); ?></span>
                        <?php } else { ?>
                            <?= __('Enter private name'); ?>
                        <?php } ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2"></div>

                    <div class="col-sm-3">
                        <input type="hidden" name="index_list" value="quicksearch">
                        <?php
                        if ($humo_option['min_search_chars'] == 1) {
                            $pattern = "";
                            $min_chars = " 1 ";
                        } else {
                            $pattern = 'pattern=".{' . $humo_option['min_search_chars'] . ',}"';
                            $min_chars = " " . $humo_option['min_search_chars'] . " ";
                        }
                        ?>
                        <input type="text" class="form-control form-control-sm" name="quicksearch" value="<?= $list["quicksearch"]; ?>" placeholder="<?= __('Name'); ?>" size="30" <?= $pattern; ?> title="<?= __('Minimum:') . $min_chars . __('characters'); ?>">
                    </div>

                    <?php if ($num_rows2 > 1 && $humo_option['one_name_study'] == 'n') { ?>
                        <div class="col-sm-2">
                            <!-- <?= __('Family tree'); ?> -->
                            <select name="select_trees" class="form-select form-select-sm">
                                <option value="tree_selected" <?php if ($select_trees == "tree_selected") echo 'selected'; ?>><?= __('Selected family tree'); ?></option>
                                <option value="all_trees" <?php if ($select_trees == "all_trees") echo 'selected'; ?>><?= __('All family trees'); ?></option>
                                <option value="all_but_this" <?php if ($select_trees == "all_but_this") echo 'selected'; ?>><?= __('All but selected tree'); ?></option>
                            </select>
                        </div>
                    <?php } elseif ($num_rows2 > 1 && $humo_option['one_name_study'] == 'y') { ?>
                        <input type="hidden" name="select_trees" value="all_trees">
                    <?php } ?>

                    <input type="submit" class="col-sm-1 btn btn-sm btn-success" name="send_mail" value="<?= __('Search'); ?>">

                    <div class="col-sm-2">
                        <a href="<?= $list_var2; ?>adv_search=1&index_list=search"><?= __('Advanced search'); ?></a>
                    </div>
                </div>
            <?php } ?>

            <!-- Advanced search box -->
            <?php if ($list["adv_search"] == true) { ?>
                <div class="row ms-md-1">
                    <div class="col-sm-3">
                        <?= __('First name'); ?>:

                        <div class="input-group mb-3">
                            <select size="1" name="part_firstname" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_firstname'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_firstname'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="pers_firstname" value="<?= safe_text_show($selection['pers_firstname']); ?>" size="15" placeholder="<?= __('First name'); ?>">

                            <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="<?= __('Also searches for special names like nickname, alias, birth name, soldier name, etc.'); ?>">?</button>
                        </div>
                    </div>

                    <?php
                    if ($humo_option['one_name_study'] != 'y') {
                        $pers_prefix = $selection['pers_prefix'];
                        if ($pers_prefix == 'EMPTY') {
                            $pers_prefix = '';
                        }
                    ?>

                        <div class="col-sm-auto">
                            <?= ucfirst(__('prefix')); ?>:
                            <div class="input-group mb-3">
                                <div class="input-group-text">
                                    <!-- Optional search for prefix -->
                                    <input class="form-check-input mt-0" type="checkbox" name="use_pers_prefix" value="" <?= $selection['use_pers_prefix'] == 'USED' ? 'checked' : ''; ?>>
                                </div>
                                <input type="text" class="form-control form-control-sm" name="pers_prefix" value="<?= safe_text_show($pers_prefix); ?>" size="8" placeholder="<?= ucfirst(__('prefix')); ?>">
                            </div>
                        </div>

                        <div class="col-sm-auto">
                            <?= __('Last name'); ?>:
                            <div class="input-group mb-3">
                                <?php /*
                                <input type="text" class="form-control form-control-sm" name="pers_prefix" value="<?= safe_text_show($pers_prefix); ?>" size="8" placeholder="<?= ucfirst(__('prefix')); ?>">
                                */ ?>

                                <!--  Lastname -->
                                <select size="1" name="part_lastname" class="form-select form-select-sm">
                                    <option value="contains"><?= __('Contains'); ?></option>
                                    <option value="equals" <?php if ($selection['part_lastname'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                    <option value="starts_with" <?php if ($selection['part_lastname'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                                </select>
                                <input type="text" class="form-control form-control-sm" name="pers_lastname" value="<?= safe_text_show($selection['pers_lastname']); ?>" size="15" placeholder="<?= __('Last name'); ?>">
                            </div>
                        </div>
                    <?php } else { ?>
                        <?= __('Last name'); ?>:
                        <span style="text-align:center; font-weight:bold"><?= $humo_option['one_name_thename']; ?></span>
                        <input type="hidden" name="pers_lastname" value="<?= $humo_option['one_name_thename']; ?>">
                        <input type="hidden" name="part_lastname" value="equals">
                    <?php } ?>

                    <!-- GEDCOM number -->
                    <div class="col-sm-4">
                        <?= ucfirst(__('gedcomnumber (ID)')); ?>:
                        <div class="input-group mb-3">
                            <select class="form-select form-select-sm" name="part_gednr" id="inputGroupGedcomnumber">
                                <option value="equals"><?= __('Equals'); ?></option>
                                <option value="contains" <?php if ($selection['part_gednr'] == 'contains') echo ' selected'; ?>><?= __('Contains'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_gednr'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="gednr" value="<?= safe_text_show($selection['gednr']); ?>" size="15" placeholder="<?= ucfirst(__('gedcomnumber (ID)')); ?>">
                        </div>
                    </div>
                </div>

                <div class="row ms-md-1">
                    <div class="col-sm-3">
                        <?= ucfirst(__('born')) . '/ ' . ucfirst(__('baptised')); ?>:
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-sm" name="birth_year" value="<?= safe_text_show($selection['birth_year']); ?>" size="4" placeholder="<?= __('Date'); ?>">
                            <input type="text" class="form-control form-control-sm" name="birth_year_end" value="<?= safe_text_show($selection['birth_year_end']); ?>" size="4" placeholder="<?= __('Date untill'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <?= ucfirst(__('born')) . '/ ' . ucfirst(__('baptised')); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_birth_place" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_birth_place'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_birth_place'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="birth_place" value="<?= safe_text_show($selection['birth_place']); ?>" size="15" placeholder="<?= __('Place'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <?= ucfirst(__('died')) . '/ ' . ucfirst(__('buried')); ?>:
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-sm" name="death_year" value="<?= safe_text_show($selection['death_year']); ?>" size="4" placeholder="<?= __('Date'); ?>">
                            <input type="text" class="form-control form-control-sm" name="death_year_end" value="<?= safe_text_show($selection['death_year_end']); ?>" size="4" placeholder="<?= __('Date untill'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <?= ucfirst(__('died')) . '/ ' . ucfirst(__('buried')); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_death_place" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_death_place'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_death_place'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="death_place" value="<?= safe_text_show($selection['death_place']); ?>" size="15" placeholder="<?= __('Place'); ?>">
                        </div>
                    </div>
                </div>

                <!--
                <div class="accordion mx-4 mb-2" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                <?= __('Advanced search'); ?>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse">
                            <div class="accordion-body genealogy_search">
                    -->

                <div class="row ms-md-1">
                    <!-- Research status -->
                    <div class="col-sm-3">
                        <?= __('Research status:'); ?>
                        <select name="parent_status" class="form-select form-select-sm">
                            <option value="noparents" <?php if ($selection['parent_status'] == "noparents") echo 'selected'; ?>><?= __('parents unknown'); ?></option>
                            <option value="motheronly" <?php if ($selection['parent_status'] == "motheronly") echo 'selected'; ?>><?= __('father unknown'); ?></option>
                            <option value="fatheronly" <?php if ($selection['parent_status'] == "fatheronly") echo 'selected'; ?>><?= __('mother unknown'); ?></option>
                            <option value="allpersons" <?php if ($selection['parent_status'] == "" or $selection['parent_status'] == 'allpersons') echo 'selected'; ?>><?= __('All'); ?></option>
                        </select>
                    </div>

                    <!-- Text -->
                    <div class="col-sm-3">
                        <?= __('Text'); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_text" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                echo '<option value="equals" <?php if ($selection['part_text'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_text'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="text" value="<?= safe_text_show($selection['text']); ?>" size="15" placeholder="<?= __('Text'); ?>">
                        </div>
                    </div>

                    <!-- Profession -->
                    <div class="col-sm-3">
                        <?= __('Profession'); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_profession" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_profession'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_profession'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="pers_profession" value="<?= safe_text_show($selection['pers_profession']); ?>" size="15" placeholder="<?= __('Profession'); ?>">
                        </div>
                    </div>

                    <!-- Own code -->
                    <div class="col-sm-2">
                        <?= __('Own code'); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_own_code" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_own_code'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_own_code'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="own_code" value="<?= safe_text_show($selection['own_code']); ?>" size="15" placeholder="<?= __('Own code'); ?>">
                        </div>
                    </div>
                </div>

                <div class="row ms-md-1">
                    <div class="col-sm-3">
                        <?= __('Choose sex:'); ?>
                        <select size="1" name="sexe" class="form-select form-select-sm">
                            <option value="both"><?= __('All'); ?></option>
                            <option value="M" <?php if ($selection['sexe'] == 'M') echo ' selected'; ?>><?= __('Male'); ?></option>
                            <option value="F" <?php if ($selection['sexe'] == 'F') echo ' selected'; ?>><?= __('Female'); ?></option>
                            <option value="Unknown" <?php if ($selection['sexe'] == 'Unknown') echo ' selected'; ?>><?= __('Unknown'); ?></option>
                        </select>
                    </div>

                    <!-- Living place -->
                    <div class="col-sm-3">
                        <?= __('Place'); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_place" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_place'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_place'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="pers_place" value="<?= safe_text_show($selection['pers_place']); ?>" size="15" placeholder="<?= __('Place'); ?>">
                        </div>
                    </div>

                    <!-- Zip code -->
                    <div class="col-sm-3">
                        <?= __('Zip code'); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_zip_code" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_zip_code'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_zip_code'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="zip_code" value="<?= safe_text_show($selection['zip_code']); ?>" size="15" placeholder="<?= __('Zip code'); ?>">
                        </div>
                    </div>

                </div>

                <div class="row ms-md-1">
                    <!-- Witness -->
                    <div class="col-sm-3">
                        <?= ucfirst(__('witness')); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_witness" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_witness'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_witness'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="witness" value="<?= safe_text_show($selection['witness']); ?>" size="15" placeholder="<?= ucfirst(__('witness')); ?>">
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <?= __('Partner firstname'); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_spouse_firstname" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_spouse_firstname'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_spouse_firstname'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="spouse_firstname" value="<?= safe_text_show($selection['spouse_firstname']); ?>" size="15" placeholder="<?= __('First name'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <?= __('Partner lastname'); ?>:
                        <div class="input-group mb-3">
                            <select size="1" name="part_spouse_lastname" class="form-select form-select-sm">
                                <option value="contains"><?= __('Contains'); ?></option>
                                <option value="equals" <?php if ($selection['part_spouse_lastname'] == 'equals') echo ' selected'; ?>><?= __('Equals'); ?></option>
                                <option value="starts_with" <?php if ($selection['part_spouse_lastname'] == 'starts_with') echo ' selected'; ?>><?= __('Starts with'); ?></option>
                            </select>
                            <input type="text" class="form-control form-control-sm" name="spouse_lastname" value="<?= safe_text_show($selection['spouse_lastname']); ?>" size="15" placeholder="<?= __('Last name'); ?>">
                        </div>
                    </div>

                </div>

                <!--
                            </div>
                        </div>
                    </div>
                </div>
                    -->


                <div class="row mb-3 ms-md-1">
                    <?php if ($num_rows2 > 1 && $humo_option['one_name_study'] == 'n') { ?>
                        <div class="col-sm-3">
                            <!-- <?= __('Family tree'); ?> -->
                            <select name="select_trees" class="form-select form-select-sm">
                                <option value="tree_selected" <?php if ($select_trees == "tree_selected") echo 'selected'; ?>><?= __('Selected family tree'); ?></option>
                                <option value="all_trees" <?php if ($select_trees == "all_trees") echo 'selected'; ?>><?= __('All family trees'); ?></option>
                                <option value="all_but_this" <?php if ($select_trees == "all_but_this") echo 'selected'; ?>><?= __('All but selected tree'); ?></option>
                            </select>
                        </div>
                    <?php } elseif ($num_rows2 > 1 && $humo_option['one_name_study'] == 'y') { ?>
                        <input type="hidden" name="select_trees" value="all_trees">
                    <?php } ?>

                    <input type="submit" class="col-sm-1 btn btn-sm btn-success" name="send_mail" value="<?= __('Search'); ?>">

                    <input type="hidden" name="adv_search" value="1">
                    &nbsp;<input type="submit" name="reset_all" class="col-sm-1 btn btn-sm btn-info" value="<?= __('Clear fields'); ?>">

                    <!-- Help popup. Remark: Bootstrap popover javascript in layout script. -->
                    <style>
                        .popover {
                            max-width: 500px;
                        }

                        .popover-body {
                            height: 300px;
                            overflow-y: auto;
                        }
                    </style>
                    <?php $popup_text = '<b>' . __('Wildcards:') . '</b><br>' .
                        __('_ = 1 character') . '<br>' .
                        __('% = >1 character') . '<br><br>' .

                        '<b>' . __('Tip') . ':</b><br>' .
                        __('With Advanced Search you can easily create lists like: all persons with surname <b>Schaap</b> who were born <b>between 1820 and 1840</b> in <b>Amsterdam</b><br>You can also search without a name: all persons who <b>died in 1901</b> in <b>Amstelveen.</b>') . '<br><br>'; ?>
                    <?php $popup_text = str_replace('"', "'", $popup_text); ?>
                    <div class="col-sm-auto">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-html="true" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="<?= $popup_text; ?>">
                            <?= __('Help'); ?>
                        </button>
                    </div>

                    <div class="col-sm-2">
                        <a href="<?= $list_var2; ?>adv_search=0&reset=1"><?= __('Standard search'); ?></a>
                    </div>

                </div>
            <?php } ?>
        </div>
    </form>
<?php
}

$uri_path_string = $link_cls->get_link($uri_path, 'list', $tree_id, true);

// *** Check for search results ***
if ($list["person_result"]->rowCount() > 0) {
    // "<="
    $data["previous_link"] = '';
    $data["previous_status"] = '';
    if ($start > 1) {
        $start2 = $start - 20;
        $calculated = ($start - 2) * $list["nr_persons"];
        $data["previous_link"] = $uri_path_string . "index_list=" . $list["index_list"] . "&amp;start=" . $start2 . "&amp;item=" . $calculated;
    }
    if ($start <= 0) {
        $start = 1;
    }
    if ($start == '1') {
        $data["previous_status"] = 'disabled';
    }

    // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
    for ($i = $start; $i <= $start + 19; $i++) {
        $calculated = ($i - 1) * $list["nr_persons"];
        if ($calculated < $list["count_persons"]) {
            $data["page_nr"][] = $i;
            if ($list["item"] == $calculated) {
                $data["page_link"][$i] = '';
                $data["page_status"][$i] = 'active';
            } else {
                $data["page_link"][$i] = $uri_path_string . "index_list=" . $list["index_list"] . "&amp;start=" . $start . "&amp;item=" . $calculated;
                $data["page_status"][$i] = '';
            }
        }
    }

    // "=>" 
    $data["next_link"] = '';
    $data["next_status"] = '';
    $calculated = ($i - 1) * $list["nr_persons"];
    if ($calculated < $list["count_persons"]) {
        $data["next_link"] = $uri_path_string . "index_list=" . $list["index_list"] . "&amp;start=" . $i . "&amp;item=" . $calculated;
    } else {
        $data["next_status"] = 'disabled';
    }
}
?>

<div class="index_list1">
    <?php
    // *** Don't use this code if search is done with partner or for people with only mother or only father***
    if (!$selection['spouse_firstname'] && !$selection['spouse_lastname'] && $selection['parent_status'] != "motheronly" && $selection['parent_status'] != "fatheronly") {
        echo $list["count_persons"] . __(' persons found.');
    } else {
        echo '<div id="found_div">&nbsp;</div>';
    }

    // *** Normal or expanded list ***
    if (isset($_POST['list_expanded'])) {
        $_SESSION['save_list_expanded'] = $_POST['list_expanded'] == '0' ? '0' : '1';
    }
    $list_expanded = true; // *** Default value ***
    if (isset($_SESSION['save_list_expanded'])) {
        if ($_SESSION['save_list_expanded'] == '1') {
            $list_expanded = true;
        } else {
            $list_expanded = false;
        }
    }

    // *** Button: normal or expanded list ***
    $button_line = "item=" . $list["item"];   // the ? or & is already included in the $uri_path_string created above
    if (isset($_GET['start'])) {
        $button_line .= "&amp;start=" . $_GET['start'];
    } else {
        $button_line .= "&amp;start=1";
    }
    $button_line .=  "&amp;index_list=" . $list["index_list"];
    ?>

    <form method="POST" action="<?= $uri_path_string . $button_line; ?>" style="display : inline;">
        <?php
        if ($list_expanded == true) {
            echo '<input type="hidden" name="list_expanded" value="0">';
            echo '<input type="submit" name="submit" value="' . __('Concise view') . '" class="btn btn-sm btn-secondary">';
        } else {
            echo '<input type="hidden" name="list_expanded" value="1">';
            echo '<input type="submit" name="submit" value="' . __('Expanded view') . '" class="btn btn-sm btn-secondary">';
        }
        ?>
    </form>

    <?php
    // *** Don't use code if search is done with partner or for people with only mother or only father***
    if (isset($data["page_nr"]) && !$selection['spouse_firstname'] && !$selection['spouse_lastname'] && $selection['parent_status'] != "motheronly" && $selection['parent_status'] != "fatheronly") {
    ?>
        <br><br>
    <?php
        include __DIR__ . '/partial/pagination.php';
    }

    // *** No results ***
    if ($list["person_result"]->rowCount() == 0) {
        echo '<br><div class="center">' . __('No names found.') . '</div>';
    }
    ?>
</div>

<?php
$dir = "";
if ($language["dir"] == "rtl") {
    $dir = "rtl"; // loads the proper CSS for rtl display (rtlindex_list2):
}

// with extra sort date column, set smaller left margin
$listnr = "2";      // default 20% margin
//if($list["index_list"] != "places" AND ($list["order_select"]=='sort_birthdate' OR $list["order_select"]=='sort_deathdate' OR $list["order_select"]=='sort_baptdate' OR $list["order_select"]=='sort_burieddate')) {
//	$listnr="3";   // 5% margin
//}
//echo '<div class="'.$dir.'index_list'.$listnr.'">';

//*** Show persons ******************************************************************
$privcount = 0; // *** Count privacy persons ***

$selected_place = "";

// TODO Allready added in model. But needed for spouse in this script for now...
// *** Search for (part of) first or lastname ***
function name_qry($search_name, $search_part)
{
    $text = "LIKE '%" . safe_text_db($search_name) . "%'"; // *** Default value: "contains" ***
    if ($search_part == 'equals') {
        $text = "='" . safe_text_db($search_name) . "'";
    }
    if ($search_part == 'starts_with') {
        $text = "LIKE '" . safe_text_db($search_name) . "%'";
    }
    return $text;
}
?>

<table class="table table-sm">
    <thead class="table-primary">
        <tr>
            <?php if ($list["index_list"] == 'places') { ?>
                <th><?= __('Places'); ?></th>
            <?php } ?>
            <th colspan="2"><?= __('Name'); ?></th>
            <th colspan="2" width="250px"><?= ucfirst(__('born')) . '/ ' . ucfirst(__('baptised')); ?></th>
            <th colspan="2" width="250px"><?= ucfirst(__('died')) . '/ ' . ucfirst(__('buried')); ?></th>
            <?php if ($select_trees == 'all_trees' or $select_trees == 'all_but_this') {
                echo '<th>' . __('Family tree') . '</th>';
            } ?>
        </tr>
    </thead>

    <?php
    if ($list["index_list"] != 'places') {
        $link = $link_cls->get_link($uri_path, 'list', $tree_id, true);
    ?>
        <thead class="table-primary">
            <tr>
                <?php
                $style = '';
                $sort_reverse = $order;
                $img = '';
                if ($list["order_select"] == "sort_firstname") {
                    $style = ' style="background-color:#ffffa0"';
                    $sort_reverse = '1';
                    if ($order == '1') {
                        $sort_reverse = '0';
                        $img = 'up';
                    }
                }
                ?>
                <th colspan="2">
                    <?= __('Sort by:'); ?> <a href="<?= $link; ?>index_list=<?= $list["index_list"]; ?>&start=1&item=0&sort=sort_firstname&sort_desc=<?= $sort_reverse; ?>" <?= $style; ?>>
                        <?= ucfirst(__('firstname')); ?> <img src="images/button3<?= $img; ?>.png">
                    </a>

                    <?php
                    $style = '';
                    $sort_reverse = $order;
                    $img = '';
                    if ($list["order_select"] == "sort_lastname") {
                        $style = ' style="background-color:#ffffa0"';
                        $sort_reverse = '1';
                        if ($order == '1') {
                            $sort_reverse = '0';
                            $img = 'up';
                        }
                    }
                    ?>
                    <a href="<?= $link; ?>index_list=<?= $list["index_list"]; ?>&start=1&item=0&sort=sort_lastname&sort_desc=<?= $sort_reverse; ?>" <?= $style; ?>>
                        <?= ucfirst(__('lastname')); ?> <img src="images/button3<?= $img; ?>.png">
                    </a>
                </th>

                <?php
                $style = '';
                $sort_reverse = $order;
                $img = '';
                if ($list["order_select"] == "sort_birthdate") {
                    $style = ' style="background-color:#ffffa0"';
                    $sort_reverse = '1';
                    if ($order == '1') {
                        $sort_reverse = '0';
                        $img = 'up';
                    }
                }
                echo '<th><a href="' . $link . 'index_list=' . $list["index_list"] . '&start=1&item=0&sort=sort_birthdate&sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Date') . ' <img src="images/button3' . $img . '.png"></a></th>';

                $style = '';
                $sort_reverse = $order;
                $img = '';
                if ($list["order_select"] == "sort_birthplace") {
                    $style = ' style="background-color:#ffffa0"';
                    $sort_reverse = '1';
                    if ($order == '1') {
                        $sort_reverse = '0';
                        $img = 'up';
                    }
                }
                echo '<th><a href="' . $link . 'index_list=' . $list["index_list"] . '&start=1&item=0&sort=sort_birthplace&sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Place') . ' <img src="images/button3' . $img . '.png"></a></th>';

                $style = '';
                $sort_reverse = $order;
                $img = '';
                if ($list["order_select"] == "sort_deathdate") {
                    $style = ' style="background-color:#ffffa0"';
                    $sort_reverse = '1';
                    if ($order == '1') {
                        $sort_reverse = '0';
                        $img = 'up';
                    }
                }
                echo '<th><a href="' . $link . 'index_list=' . $list["index_list"] . '&start=1&item=0&sort=sort_deathdate&sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Date') . ' <img src="images/button3' . $img . '.png"></a></th>';

                $style = '';
                $sort_reverse = $order;
                $img = '';
                if ($list["order_select"] == "sort_deathplace") {
                    $style = ' style="background-color:#ffffa0"';
                    $sort_reverse = '1';
                    if ($order == '1') {
                        $sort_reverse = '0';
                        $img = 'up';
                    }
                }
                echo '<th><a href="' . $link . 'index_list=' . $list["index_list"] . '&start=1&item=0&sort=sort_deathplace&sort_desc=' . $sort_reverse . '"' . $style . '>' . __('Place') . ' <img src="images/button3' . $img . '.png"></a></th>';

                if ($select_trees == 'all_trees' or $select_trees == 'all_but_this') {
                    echo '<th><br></th>';
                }
                ?>
            </tr>
        </thead>
    <?php
    }
    $pers_counter = 0;

    /*
    if ($list["adv_search"] == true and $selection['parent_status'] != "allpersons" and $selection['parent_status'] != "noparents") {
        echo '<script>document.getElementById("found_div").innerHTML = "' . __('Loading...') . '";</script>';
    }
    */

    while ($personDb = $list["person_result"]->fetch(PDO::FETCH_OBJ)) {
        //while ($person1Db = $list["person_result"]->fetch(PDO::FETCH_OBJ)) {

        // *** Preparation for second query. Needed to solve GROUP BY problems ***
        //$personDb = $db_functions->get_person_with_id($person1Db->pers_id);

        $spouse_found = true;

        // *** Search name of spouse ***
        if ($selection['spouse_firstname'] || $selection['spouse_lastname']) {
            $spouse_found = false;
            $person_fams = explode(";", $personDb->pers_fams);
            // *** Search all persons with a spouse IN the same tree as the 1st person ***
            $counter = count($person_fams);
            for ($marriage_loop = 0; $marriage_loop < $counter; $marriage_loop++) {
                $famDb = $db_functions->get_family($person_fams[$marriage_loop], 'man-woman');

                // *** Search all persons with a spouse IN the same tree as the 1st person ***
                $spouse_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $personDb->pers_tree_id . "' AND";
                if ($user['group_kindindex'] == "j") {
                    $spouse_qry = "SELECT *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name
                        FROM humo_persons WHERE pers_tree_id='" . $personDb->pers_tree_id . "' AND";
                }

                if ($personDb->pers_gedcomnumber == $famDb->fam_man) {
                    $spouse_qry .= ' pers_gedcomnumber="' . safe_text_db($famDb->fam_woman) . '"';
                } else {
                    $spouse_qry .= ' pers_gedcomnumber="' . safe_text_db($famDb->fam_man) . '"';
                }

                if ($selection['spouse_lastname']) {
                    if ($selection['spouse_lastname'] == __('...')) {
                        $spouse_qry .= " AND pers_lastname=''";
                    } elseif ($user['group_kindindex'] == "j") {
                        $spouse_qry .= " AND CONCAT( REPLACE(pers_prefix,'_',' ') ,pers_lastname) " . name_qry($selection['spouse_lastname'], $selection['part_spouse_lastname']);
                    } else {
                        $spouse_qry .= " AND pers_lastname " . name_qry($selection['spouse_lastname'], $selection['part_spouse_lastname']);
                    }
                }
                //if ($selection['pers_prefix']){
                // $spouse_qry.=" AND pers_prefix='".$selection['pers_prefix']."'";
                //}
                if ($selection['spouse_firstname']) {
                    $spouse_qry .= " AND pers_firstname " . name_qry($selection['spouse_firstname'], $selection['part_spouse_firstname']);
                }
                $spouse_result = $dbh->query($spouse_qry);
                $spouseDb = $spouse_result->fetch(PDO::FETCH_OBJ);
                if (isset($spouseDb->pers_id)) {
                    $spouse_found = true;
                    break;
                }
            }
        } // End of spouse search

        // *** Search parent status (no parents, only mother, only father) ***
        $parent_status_found = '1';
        if ($list["adv_search"] == true && $selection['parent_status'] != "allpersons" && $selection['parent_status'] != "noparents") {
            $parent_status_found = '0';
            $par_famc = "";
            if (isset($personDb->pers_famc)) {
                $par_famc = $personDb->pers_famc;
            }
            if ($par_famc != "") {
                $parDb = $db_functions->get_family($par_famc, 'man-woman');

                if (
                    $selection['parent_status'] == "fatheronly" && substr($parDb->fam_man, 0, 1) === "I" && substr($parDb->fam_woman, 0, 1) !== "I"
                ) {
                    $parent_status_found = '1';
                } elseif (
                    $selection['parent_status'] == "motheronly" && substr($parDb->fam_man, 0, 1) !== "I" && substr($parDb->fam_woman, 0, 1) === "I"
                ) {
                    $parent_status_found = '1';
                }
            }
        }

        // *** Show search results ***
        if ($spouse_found == true && ($parent_status_found === '1' || $parent_status_found !== '1' && !isset($_POST['adv_search']))) {
            $pers_counter++; // needed for spouses search and mother/father only search
            $person_cls = new PersonCls($personDb);
            $privacy = $person_cls->privacy;

            if ($privacy and ($selection['birth_place'] != '' or $selection['birth_year'] != '' or $selection['death_place'] != '' or $selection['death_year'] != '')) {
                $privcount++;
            } else {
                // *** Extra privacy filter check for total_filter ***
                if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $personDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                    $privcount++;
                } else {
                    show_person($personDb);
                }
            }
        }
    }
    ?>

</table>

<?php if ($privcount) { ?>
    <br><?= $privcount . __(' persons are not shown due to privacy settings'); ?><br>
<?php }

//echo '</div>';

// *** Don't execute this code if spouse search is used or mother/father only persons***
if (isset($data["page_nr"]) && !$selection['spouse_firstname'] && !$selection['spouse_lastname'] && $selection['parent_status'] != "motheronly" && $selection['parent_status'] != "fatheronly") {
?>
    <br>
<?php
    include __DIR__ . '/partial/pagination.php';
}

//echo '</div>';

//TODO check this code. In some cases found_div isn't used.
/*
echo '<script> 
    document.getElementById("found_div").innerHTML = \'' . $pers_counter . __(' persons found.') . '\';
</script>';
*/

echo '<br>';
//for testing only:
//echo 'Query: <pre>'.$query."</pre> LIMIT ".safe_text_db($list["item"]).",".$list["nr_persons"].'<br>';
//echo 'Count qry: '.$count_qry.'<br>';
//echo '<p>index_list: '.$list["index_list"];
//echo '<br>nr. of persons: '.$list["count_persons"];


// *** show person ***
function show_person($personDb)
{
    global $dbh, $db_functions, $selected_place, $language, $user;
    global $bot_visit, $humo_option, $uri_path, $select_trees, $list_expanded;
    global $selected_language, $privacy, $dirmark1, $dirmark2, $rtlmarker;
    global $list;

    $db_functions->set_tree_id($personDb->pers_tree_id);

    // *** Person class used for name and person pop-up data ***
    $person_cls = new PersonCls($personDb);
    $name = $person_cls->person_name($personDb);

    // *** Show name ***
    $index_name = '';
    if ($name["show_name"] == false) {
        $index_name = __('Name filtered');
    } else {
        // *** If there is no lastname, show a - character. ***
        // Don't show a "-" by pers_patronymes
        if ($personDb->pers_lastname == "" && !isset($_GET['pers_patronym'])) {
            $index_name = "-&nbsp;&nbsp;";
        }
        $index_name .= $name["index_name_extended"] . $name["colour_mark"];
    }
?>
    <tr>
        <?php
        // *** Show extra columns before a person in index places ***
        if ($list["index_list"] == 'places') {
            if ($selected_place != $personDb->place_order) {
                echo '<td colspan="7"><b>' . $dirmark2 . $personDb->place_order . '</b></td></tr><tr>';
                //$list["show_place"] = $personDb->place_order;
            } else {
                //$list["show_place"] = '';
            }
            $selected_place = $personDb->place_order;
        ?>

            <td valign="top" style="white-space:nowrap;width:105px">
                <?php
                if ($list["select_birth"] == '1') {
                    if ($selected_place == $personDb->pers_birth_place) {
                        echo '<span class="place_index place_index_selected">' . __('*') . '</span>';
                    } else {
                        echo '<span class="place_index">&nbsp;</span>';
                    }
                }

                if ($list["select_bapt"] == '1') {
                    if ($selected_place == $personDb->pers_bapt_place) {
                        echo '<span class="place_index place_index_selected">' . __('~') . '</span>';
                    } else {
                        echo '<span class="place_index">&nbsp;</span>';
                    }
                }

                if ($list["select_place"] == '1') {
                    // *** Check if this is the living place of a person. Can't be checked using query variables... ***
                    $query = "SELECT address_place FROM humo_addresses, humo_connections
                        WHERE address_tree_id='" . $personDb->pers_tree_id . "' AND connect_tree_id='" . $personDb->pers_tree_id . "'
                        AND connect_connect_id='" . $personDb->pers_gedcomnumber . "'
                        AND connect_item_id=address_gedcomnr
                        AND address_place='" . safe_text_db($personDb->place_order) . "'";
                    $result = $dbh->query($query);
                    $resultDb = $result->fetch(PDO::FETCH_OBJ);

                    //if ($selected_place==$personDb->pers_place_index)
                    if ($resultDb && $resultDb->address_place == $personDb->place_order && $selected_place == $personDb->place_order) {
                        echo '<span class="place_index place_index_selected">' . __('^') . '</span>';
                    } else {
                        echo '<span class="place_index">&nbsp;</span>';
                    }
                }

                if ($list["select_death"] == '1') {
                    if ($selected_place == $personDb->pers_death_place) {
                        echo '<span class="place_index place_index_selected">' . __('&#134;') . '</span>';
                    } else {
                        echo '<span class="place_index">&nbsp;</span>';
                    }
                }

                if ($list["select_buried"] == '1') {
                    if ($selected_place == $personDb->pers_buried_place) {
                        echo '<span class="place_index place_index_selected">' . __('[]') . '</span>';
                    } else {
                        echo '<span class="place_index">&nbsp;</span>';
                    }
                }

                // *** Places by events like occupations etc. ***
                if ($list["select_event"] == '1') {
                    // *** Check if this is the living place of a person. Can't be checked using query variables... ***
                    $query = "SELECT event_place FROM humo_events
                        WHERE event_tree_id='" . $personDb->pers_tree_id . "' AND event_connect_id='" . $personDb->pers_gedcomnumber . "'
                        AND event_place='" . safe_text_db($personDb->place_order) . "'";
                    $result = $dbh->query($query);
                    $resultDb = $result->fetch(PDO::FETCH_OBJ);

                    if ($resultDb && $resultDb->event_place == $personDb->place_order && $selected_place == $personDb->place_order) {
                        echo '<span class="place_index place_index_selected">' . substr(__('Events'), 0, 1) . '</span>';
                    } else {
                        echo '<span class="place_index">&nbsp;</span>';
                    }
                }
                ?>
            </td>
        <?php } ?>

        <td valign="top" style="border-right:0px; white-space:nowrap;">
            <!-- Show person popup menu -->
            <?= $person_cls->person_popup_menu($personDb); ?>
            <?= $dirmark1; ?>

            <?php
            // *** Show picture man or wife ***
            if ($personDb->pers_sexe == "M") {
                echo ' <img src="images/man.gif" alt="man">';
            } elseif ($personDb->pers_sexe == "F") {
                echo ' <img src="images/woman.gif" alt="woman">';
            } else {
                echo ' <img src="images/unknown.gif" alt="unknown">';
            }

            if ($humo_option['david_stars'] == "y") {
                $camps = "Auschwitz|Oświęcim|Sobibor|Bergen-Belsen|Bergen Belsen|Treblinka|Holocaust|Shoah|Midden-Europa|Majdanek|Belzec|Chelmno|Dachau|Buchenwald|Sachsenhausen|Mauthausen|Theresienstadt|Birkenau|Kdo |Kamp Amersfoort|Gross-Rosen|Gross Rosen|Neuengamme|Ravensbrück|Kamp Westerbork|Kamp Vught|Kommando Sosnowice|Ellrich|Schöppenitz|Midden Europa|Lublin|Tröbitz|Kdo Bobrek|Golleschau|Blechhammer|Kdo Gleiwitz|Warschau|Szezdrzyk|Polen|Kamp Bobrek|Monowitz|Dorohucza|Seibersdorf|Babice|Fürstengrube|Janina|Jawischowitz|Katowice|Kaufering|Krenau|Langenstein|Lodz|Ludwigsdorf|Melk|Mühlenberg|Oranienburg|Sakrau|Schwarzheide|Spytkowice|Stutthof|Tschechowitz|Weimar|Wüstegiersdorf|Oberhausen|Minsk|Ghetto Riga|Ghetto Lodz|Flossenbürg|Malapane";

                if (
                    preg_match("/($camps)/i", $personDb->pers_death_place) !== 0 || preg_match("/($camps)/i", $personDb->pers_buried_place) !== 0 || stripos($personDb->pers_death_place, "oorlogsslachtoffer") !== FALSE
                ) {
                    echo '<img src="images/star.gif" alt="star">&nbsp;';
                }
            }

            // *** Add own icon by person, using a file name in own code ***
            if ($personDb->pers_own_code != '' and is_file("images/" . $personDb->pers_own_code . ".gif")) {
                if ($personDb->pers_own_code != 'foto') { // *** Remove photo.gif icon, new method is used to show photo icon ***
                    echo  $dirmark1 . '<img src="images/' . $personDb->pers_own_code . '.gif" alt="' . $personDb->pers_own_code . '">&nbsp;';
                }
            }

            // *** Show camera icon if there is a photo ***
            if ($user['group_pictures'] == 'j' && !$privacy) {
                global $dataDb;
                $tree_pict_path = $dataDb->tree_pict_path;
                if (substr($tree_pict_path, 0, 1) === '|') {
                    $tree_pict_path = 'media/';
                }
                $picture_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'picture');
                // *** Only check 1st picture ***
                if (isset($picture_qry[0])) {
                    echo  $dirmark1 . '<img src="images/photo.gif" alt="photo">&nbsp;';
                }
            }
            ?>
        </td>
        <td style="border-left:0px;">
            <?php
            // *** Show name of person ***
            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $start_url = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);

            //echo ' <a href="'.$start_url.'">'.trim($index_name).'</a>';
            // *** If child doesn't have own family, directly jump to child in familyscreen using #child_I1234 ***
            $direct_link = '';
            if ($personDb->pers_fams == '') {
                $direct_link = '#person_' . $personDb->pers_gedcomnumber;
            }
            echo ' <a href="' . $start_url . $direct_link . '">' . trim($index_name) . '</a>';

            //*** Show spouse/ partner ***
            if ($list_expanded == true && $personDb->pers_fams) {
                $marriage_array = explode(";", $personDb->pers_fams);
                $nr_marriages = count($marriage_array);
                for ($x = 0; $x <= $nr_marriages - 1; $x++) {
                    $fam_partnerDb = $db_functions->get_family($marriage_array[$x]);

                    // *** This check is better then a check like: $personDb->pers_sexe=='F', because of unknown sexe or homosexual relations. ***
                    if ($personDb->pers_gedcomnumber == $fam_partnerDb->fam_man) {
                        $partner_id = $fam_partnerDb->fam_woman;
                    } else {
                        $partner_id = $fam_partnerDb->fam_man;
                    }

                    $relation_short = __('&amp;');
                    if ($fam_partnerDb->fam_marr_date || $fam_partnerDb->fam_marr_place || $fam_partnerDb->fam_marr_church_date || $fam_partnerDb->fam_marr_church_place || $fam_partnerDb->fam_kind == 'civil') {
                        $relation_short = __('X');
                    }
                    if ($fam_partnerDb->fam_div_date || $fam_partnerDb->fam_div_place) {
                        $relation_short = __(') (');
                    }

                    if ($partner_id != '0' && $partner_id != '') {
                        $partnerDb = $db_functions->get_person($partner_id);
                        $partner_cls = new PersonCls;
                        $name = $partner_cls->person_name($partnerDb);
                    } else {
                        $name["standard_name"] = __('N.N.');
                    }

                    if ($nr_marriages > 1 && $x > 0) {
                        echo ',';
                    }
                    echo ' <span class="index_partner">';
                    if ($nr_marriages > 1) {
                        if ($x == 0) {
                            echo __('1st');
                        } elseif ($x == 1) {
                            echo __('2nd');
                        } elseif ($x == 2) {
                            echo __('3rd');
                        } elseif ($x > 2) {
                            echo ($x + 1) . __('th');
                        }
                    }
                    echo ' ' . $relation_short . ' ' . rtrim($name["standard_name"]) . '</span>';
                }
            }
            // *** End spouse/ partner ***
            ?>
        </td>
        <td style="white-space:nowrap;">
            <?php
            $info = "";
            if ($personDb->pers_bapt_date) {
                $info = __('~') . ' ' . date_place($personDb->pers_bapt_date, '');
            }
            if ($personDb->pers_birth_date) {
                $info = __('*') . ' ' . date_place($personDb->pers_birth_date, '');
            }
            if ($privacy && $info) {
                $info =  __('PRIVACY FILTER');
            }
            ?>
            <?= $info; ?>
        </td>
        <td>
            <?php
            $info = "";
            if ($personDb->pers_bapt_place) {
                $info = __('~') . ' ' . $personDb->pers_bapt_place;
            }
            if ($personDb->pers_birth_place) {
                $info = __('*') . ' ' . $personDb->pers_birth_place;
            }
            if ($privacy && $info) {
                $info =  __('PRIVACY FILTER');
            }
            ?>
            <?= $info; ?>
        </td>
        <td style="white-space:nowrap;">
            <?php
            $info = "";
            if ($personDb->pers_buried_date) {
                $info = __('[]') . ' ' . date_place($personDb->pers_buried_date, '');
            }
            if ($personDb->pers_death_date) {
                $info = __('&#134;') . ' ' . date_place($personDb->pers_death_date, '');
            }
            if ($privacy && $info) {
                $info =  __('PRIVACY FILTER');
            }
            ?>
            <?= $info; ?>
        </td>
        <td>
            <?php
            $info = "";
            if ($personDb->pers_buried_place) {
                $info = __('[]') . ' ' . $personDb->pers_buried_place;
            }
            if ($personDb->pers_death_place) {
                $info = __('&#134;') . ' ' . $personDb->pers_death_place;
            }
            if ($privacy && $info) {
                $info =  __('PRIVACY FILTER');
            }
            ?>
            <?= $info; ?>
        </td>

        <?php
        // *** Show name of family tree, if search in multiple family trees is used ***
        if ($select_trees == 'all_trees' || $select_trees == 'all_but_this') {
            $treetext = show_tree_text($personDb->pers_tree_id, $selected_language);
        ?>
            <td>
                <i>
                    <font size="-1"><?= $treetext['name']; ?></font>
                </i>
            </td>
        <?php } ?>
    </tr>

    <!-- TEST -->
    <?php
    /*
    <?php if ($list["show_place"]) { ?>
        <tr>
            <td colspan="7"><b><?= $dirmark2 . $list["show_place"]; ?></b></td>
        </tr>
    <?php } ?>

    <tr>
    <?php if ($list[places]){ ?>
        <td valign="top" style="white-space:nowrap;width:105px">

        echo '<span class="place_index place_index_selected">' . __('*') . '</span>';

    <?php } ?>
    </tr>
    */
    ?>

<?php
} // *** end function show person ***