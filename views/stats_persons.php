<!-- <div class="table-responsive"> -->
<?php $HtmlEscaper = new \Genealogy\Include\HtmlEscaper(); ?>
<table class="table my-3">
    <thead class="table-primary">
        <tr>
            <th><?= __('Item'); ?></th>
            <th colspan="2"><?= __('Male'); ?></th>
            <th colspan="2"><?= __('Female'); ?></th>
        </tr>
    </thead>

    <tr>
        <td><?= __('No. of persons'); ?></td>
        <td><?= (int) $statistics['countman']; ?></td>
        <td><?= $HtmlEscaper->escHtml((string) $statistics['man_percentage']); ?>%</td>
        <td><?= (int) $statistics['countwoman']; ?></td>
        <td><?= $HtmlEscaper->escHtml((string) $statistics['woman_percentage']); ?>%</td>
    </tr>

    <tr>
        <td colspan="5"><br></td>
    </tr>

    <tr>
        <td><?= __('Oldest birth date'); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['oldest_man_bir_date']['date']); ?></td>
        <td><?= render_person_link($statistics['oldest_man_bir_date']); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['oldest_woman_bir_date']['date']); ?></td>
        <td><?= render_person_link($statistics['oldest_woman_bir_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Youngest birth date'); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['latest_man_bir_date']['date']); ?></td>
        <td><?= render_person_link($statistics['latest_man_bir_date']); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['latest_woman_bir_date']['date']); ?></td>
        <td><?= render_person_link($statistics['latest_woman_bir_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Oldest baptism date'); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['oldest_man_bap_date']['date']); ?></td>
        <td><?= render_person_link($statistics['oldest_man_bap_date']); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['oldest_woman_bap_date']['date']); ?></td>
        <td><?= render_person_link($statistics['oldest_woman_bap_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Youngest baptism date'); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['latest_man_bap_date']['date']); ?></td>
        <td><?= render_person_link($statistics['latest_man_bap_date']); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['latest_woman_bap_date']['date']); ?></td>
        <td><?= render_person_link($statistics['latest_woman_bap_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Oldest death date'); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['oldest_man_dea_date']['date']); ?></td>
        <td><?= render_person_link($statistics['oldest_man_dea_date']); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['oldest_woman_dea_date']['date']); ?></td>
        <td><?= render_person_link($statistics['oldest_woman_dea_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Youngest death date'); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['latest_man_dea_date']['date']); ?></td>
        <td><?= render_person_link($statistics['latest_man_dea_date']); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['latest_woman_dea_date']['date']); ?></td>
        <td><?= render_person_link($statistics['latest_woman_dea_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Longest living person'); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['longest_living_man']['date']); ?></td>
        <td><?= render_person_link($statistics['longest_living_man']); ?></td>
        <td><?= $HtmlEscaper->escHtmlDecoded($statistics['longest_living_woman']['date']); ?></td>
        <td><?= render_person_link($statistics['longest_living_woman']); ?></td>
    </tr>

    <tr>
        <td><?= __('Average age'); ?></td>
        <td>
            <?php if ($statistics['average_living_man'] != 0) { ?>
                <?= $HtmlEscaper->escHtml((string) round((float) $statistics['average_living_man'], 1)); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>

        <td>
            <?php if ($statistics['average_living_woman'] != 0) { ?>
                <?= $HtmlEscaper->escHtml((string) round((float) $statistics['average_living_woman'], 1)); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>
    </tr>

    <tr>
        <td><?= __('Average age married persons'); ?></td>
        <td>
            <?php if ($statistics['average_living_man_marr'] != 0) { ?>
                <?= $HtmlEscaper->escHtml((string) round((float) $statistics['average_living_man_marr'], 1)); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>

        <td>
            <?php if ($statistics['average_living_woman_marr'] != 0) { ?>
                <?= $HtmlEscaper->escHtml((string) round((float) $statistics['average_living_woman_marr'], 1)); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>
    </tr>

    <tr>
        <td><?= __('Lifespan range of married individuals'); ?></td>
        <td><?= $HtmlEscaper->escHtml((string) $statistics['shortest_living_man_marr']) . ' - ' . $HtmlEscaper->escHtml((string) $statistics['longest_living_man_marr']) . ' ' . __('years'); ?></td>
        <td>&nbsp;</td>
        <td><?= $HtmlEscaper->escHtml((string) $statistics['shortest_living_woman_marr']) . ' - ' . $HtmlEscaper->escHtml((string) $statistics['longest_living_woman_marr']) . ' ' . __('years'); ?></td>
        <td>&nbsp;</td>
    </tr>
</table>
<!-- </div> -->

<?php
function render_person_link($person_data)
{
    $HtmlEscaper = new \Genealogy\Include\HtmlEscaper();
    $name = $HtmlEscaper->escHtml((string) $person_data['name']);
    if ($person_data['url']) {
        return '<a href="' . $HtmlEscaper->escHtml((string) $person_data['url']) . '"><b>' . $name . '</b></a>';
    } else {
        return $name;
    }
}
?>