<?php

namespace Genealogy\Include;

class EditorEventSelection
{
    function event_selection($event_gedcom)
    {
        global $humo_option;
?>
        <optgroup label="<?= __('Nickname'); ?>">
            <option value="NICK" <?= $event_gedcom == 'NICK' ? 'selected' : ''; ?>>NICK <?= __('Nickname'); ?></option>
        </optgroup>

        <optgroup label="<?= __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>">
            <option value="NPFX"><?= __('Prefix') . ': ' . __('e.g. Lt. Cmndr.'); ?></option>
            <option value="NSFX" <?= $event_gedcom == 'NSFX' ? 'selected' : ''; ?>><?= __('Suffix'); ?>: <?= __('e.g. Jr.'); ?></option>
            <option value="nobility" <?= $event_gedcom == 'nobility' ? 'selected' : ''; ?>><?= __('Title of Nobility') . ': ' . __('e.g. Jhr., Jkvr.'); ?></option>
            <option value="title" <?= $event_gedcom == 'title' ? 'selected' : ''; ?>><?= __('Title') . ': ' . __('e.g. Prof., Dr.'); ?></option>
            <option value="lordship" <?= $event_gedcom == 'lordship' ? 'selected' : ''; ?>><?= __('Title of Lordship') . ': ' . __('e.g. Lord of Amsterdam'); ?></option>
        </optgroup>

        <optgroup label="<?= __('Name'); ?>">
            <option value="_AKAN" <?= $event_gedcom == '_AKAN' ? 'selected' : ''; ?>><?= '_AKAN ' . __('Also known as'); ?></option>
            <option value="_ALIA" <?= $event_gedcom == '_ALIA' ? 'selected' : ''; ?>><?= '_ALIA ' . __('alias name'); ?></option>
            <option value="_SHON" <?= $event_gedcom == '_SHON' ? 'selected' : ''; ?>>_SHON <?= __('Short name (for reports)'); ?></option>
            <option value="_ADPN" <?= $event_gedcom == '_ADPN' ? 'selected' : ''; ?>>_ADPN <?= __('Adopted name'); ?></option>

            <!--- display here if user didn't set to be displayed in main name section -->
            <?php if ($humo_option['admin_hebname'] != "y") { ?>
                <option value="_HEBN" <?= $event_gedcom == '_HEBN' ? 'selected' : ''; ?>>_HEBN <?= __('Hebrew name'); ?></option>
            <?php } ?>

            <option value="_CENN" <?= $event_gedcom == '_CENN' ? 'selected' : ''; ?>>_CENN <?= __('Census name'); ?></option>
            <option value="_MARN" <?= $event_gedcom == '_MARN' ? 'selected' : ''; ?>>_MARN <?= __('Married name'); ?></option>
            <option value="_GERN" <?= $event_gedcom == '_GERN' ? 'selected' : ''; ?>>_GERN <?= __('Given name'); ?></option>
            <option value="_FARN" <?= $event_gedcom == '_FARN' ? 'selected' : ''; ?>>_FARN <?= __('Farm name'); ?></option>
            <option value="_BIRN" <?= $event_gedcom == '_BIRN' ? 'selected' : ''; ?>>_BIRN <?= __('Birth name'); ?></option>
            <option value="_INDN" <?= $event_gedcom == '_INDN' ? 'selected' : ''; ?>>_INDN <?= __('Indian name'); ?></option>
            <option value="_FKAN" <?= $event_gedcom == '_FKAN' ? 'selected' : ''; ?>>_FKAN <?= __('Formal name'); ?></option>
            <option value="_CURN" <?= $event_gedcom == '_CURN' ? 'selected' : ''; ?>>_CURN <?= __('Current name'); ?></option>
            <option value="_SLDN" <?= $event_gedcom == '_SLDN' ? 'selected' : ''; ?>>_SLDN <?= __('Soldier name'); ?></option>
            <option value="_RELN" <?= $event_gedcom == '_RELN' ? 'selected' : ''; ?>>_RELN <?= __('Religious name'); ?></option>
            <option value="_OTHN" <?= $event_gedcom == '_OTHN' ? 'selected' : ''; ?>>_OTHN <?= __('Other name'); ?></option>
            <option value="_FRKA" <?= $event_gedcom == '_FRKA' ? 'selected' : ''; ?>>_FRKA <?= __('Formerly known as'); ?></option>
            <option value="_RUFN" <?= $event_gedcom == '_RUFN' ? 'selected' : ''; ?>>_RUFN <?= __('German Rufname'); ?></option>
        </optgroup>
<?php
    }
}
