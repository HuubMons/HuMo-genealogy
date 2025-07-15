<?php
$showTreeDate = new \Genealogy\Include\ShowTreeDate();
?>

<div class="row my-3">
    <div class="col-md-8">
        <table class="table">
            <thead class="table-primary">
                <tr>
                    <th><?= __('Item'); ?></th>
                    <th><br></th>
                    <th><br></th>
                </tr>
            </thead>

            <!-- Latest family tree update -->
            <tr>
                <td><?= __('Latest update'); ?></td>
                <td align="center"><i><?= $showTreeDate->show_tree_date($selectedFamilyTree->tree_date); ?></i></td>
                <td><br></td>
            </tr>

            <tr>
                <td colspan="3"><br></td>
            </tr>

            <!-- Nr. of families in family tree -->
            <tr>
                <td><?= __('No. of families'); ?></td>
                <td align="center"><i><?= $selectedFamilyTree->tree_families; ?></i></td>
                <td><br></td>
            </tr>

            <tr>
                <td><?= __('Most children in family'); ?></td>
                <td align='center'><i><?= $statistics['nr_children']; ?></i></td>
                <td align="center">
                    <?php if ($statistics['nr_children'] != "0") { ?>
                        <a href="<?= $statistics['url']; ?>"><i><b><?= $statistics['man']; ?> <?= __('and'); ?> <?= $statistics['woman']; ?></b></i></a>
                    <?php } ?>
                </td>
            </tr>

            <!-- Nr. of persons in family tree -->
            <tr>
                <td><?= __('No. of persons'); ?></td>
                <td align='center'><i><?= $selectedFamilyTree->tree_persons; ?></i></td>
                <td><br></td>
            </tr>
        </table>
    </div>
</div>