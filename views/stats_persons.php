<!-- <div class="table-responsive"> -->
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
        <td><?= $statistics['countman']; ?></td>
        <td><?= $statistics['man_percentage']; ?>%</td>
        <td><?= $statistics['countwoman']; ?></td>
        <td><?= $statistics['woman_percentage']; ?>%</td>
    </tr>

    <tr>
        <td colspan="5"><br></td>
    </tr>

    <tr>
        <td><?= __('Oldest birth date'); ?></td>
        <td><?= $statistics['oldest_man_bir_date']['date']; ?></td>
        <td><?= render_person_link($statistics['oldest_man_bir_date']); ?></td>
        <td><?= $statistics['oldest_woman_bir_date']['date']; ?></td>
        <td><?= render_person_link($statistics['oldest_woman_bir_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Youngest birth date'); ?></td>
        <td><?= $statistics['latest_man_bir_date']['date']; ?></td>
        <td><?= render_person_link($statistics['latest_man_bir_date']); ?></td>
        <td><?= $statistics['latest_woman_bir_date']['date']; ?></td>
        <td><?= render_person_link($statistics['latest_woman_bir_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Oldest baptism date'); ?></td>
        <td><?= $statistics['oldest_man_bap_date']['date']; ?></td>
        <td><?= render_person_link($statistics['oldest_man_bap_date']); ?></td>
        <td><?= $statistics['oldest_woman_bap_date']['date']; ?></td>
        <td><?= render_person_link($statistics['oldest_woman_bap_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Youngest baptism date'); ?></td>
        <td><?= $statistics['latest_man_bap_date']['date']; ?></td>
        <td><?= render_person_link($statistics['latest_man_bap_date']); ?></td>
        <td><?= $statistics['latest_woman_bap_date']['date']; ?></td>
        <td><?= render_person_link($statistics['latest_woman_bap_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Oldest death date'); ?></td>
        <td><?= $statistics['oldest_man_dea_date']['date']; ?></td>
        <td><?= render_person_link($statistics['oldest_man_dea_date']); ?></td>
        <td><?= $statistics['oldest_woman_dea_date']['date']; ?></td>
        <td><?= render_person_link($statistics['oldest_woman_dea_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Youngest death date'); ?></td>
        <td><?= $statistics['latest_man_dea_date']['date']; ?></td>
        <td><?= render_person_link($statistics['latest_man_dea_date']); ?></td>
        <td><?= $statistics['latest_woman_dea_date']['date']; ?></td>
        <td><?= render_person_link($statistics['latest_woman_dea_date']); ?></td>
    </tr>

    <tr>
        <td><?= __('Longest living person'); ?></td>
        <td><?= $statistics['longest_living_man']['date']; ?></td>
        <td><?= render_person_link($statistics['longest_living_man']); ?></td>
        <td><?= $statistics['longest_living_woman']['date']; ?></td>
        <td><?= render_person_link($statistics['longest_living_woman']); ?></td>
    </tr>

    <tr>
        <td><?= __('Average age'); ?></td>
        <td>
            <?php if ($statistics['average_living_man'] != 0) { ?>
                <?= round($statistics['average_living_man'], 1); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>

        <td>
            <?php if ($statistics['average_living_woman'] != 0) { ?>
                <?= round($statistics['average_living_woman'], 1); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>
    </tr>

    <tr>
        <td><?= __('Average age married persons'); ?></td>
        <td>
            <?php if ($statistics['average_living_man_marr'] != 0) { ?>
                <?= round($statistics['average_living_man_marr'], 1); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>

        <td>
            <?php if ($statistics['average_living_woman_marr'] != 0) { ?>
                <?= round($statistics['average_living_woman_marr'], 1); ?> <?= __('years'); ?>
            <?php } ?>
        </td>
        <td></td>
    </tr>

    <tr>
        <td><?= __('Lifespan range of married individuals'); ?></td>
        <td><?= $statistics['shortest_living_man_marr'] . ' - ' . $statistics['longest_living_man_marr'] . ' ' . __('years'); ?></td>
        <td>&nbsp;</td>
        <td><?= $statistics['shortest_living_woman_marr'] . ' - ' . $statistics['longest_living_woman_marr'] . ' ' . __('years'); ?></td>
        <td>&nbsp;</td>
    </tr>
</table>
<!-- </div> -->

<?php
function render_person_link($person_data)
{
    if ($person_data['url']) {
        return '<a href="' . $person_data['url'] . '"><b>' . $person_data['name'] . '</b></a>';
    } else {
        return $person_data['name'];
    }
}
?>