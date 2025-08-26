<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011 Huub: translated all variables to english by.
 * July 2024 Huub: removed doublescroll and html2canvas. Just use browser to print and scroll.
 */

$ancestorBox = new \Genealogy\Include\AncestorBox();

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

echo $data["ancestor_header"];

//Width of the chart. For 6 generations 1000px is right.
//If we ever make the anc chart have optionally more generations, the width and length will have to be generated as in report_descendant
//$divlen = 1000;

$top = 50;

$column1_left = 10;
$column1_top = $top + 520;

$column2_left = 50;
$column2_top = $top + 320;

$column3_left = 80;
$column3_top = $top + 199;

$column4_left = 300;
$column4_top = $top - 290;

$column5_left = 520;
$column5_top = $top - 110;

$column6_left = 740;
$column6_top = $top - 20;
?>

<div class="container-xl" style="height: 1000px; width:1000px;">
    <!-- First column name -->
    <!-- No _ character allowed in name of CSS class because of javascript -->
    <div class="ancestorName <?= $data["sexe"][1] == 'M' ? 'box_man' : 'box_woman'; ?>" align="left" style="top: <?= $column1_top; ?>px; left: <?= $column1_left; ?>px; height: 80px; width:200px;">
        <?= $ancestorBox->ancestorBox('1', 'large'); ?>
    </div>

    <!-- Second column split -->
    <div class="ancestor_split" style="top: <?= $column2_top; ?>px; left: <?= $column2_left; ?>px; height: 199px"></div>
    <div class="ancestor_split" style="top: <?= ($column2_top + 281); ?>px; left: <?= $column2_left; ?>px; height: 199px"></div>
    <!-- Second column names -->
    <?php for ($i = 1; $i < 3; $i++) { ?>
        <div class="ancestorName <?= $data["sexe"][$i + 1] == 'M' ? 'box_man' : 'box_woman'; ?>" style="top: <?= (($column2_top - 520) + ($i * 480)); ?>px; left: <?= ($column2_left + 8); ?>px; height: 80px; width:200px;">
            <?= $ancestorBox->ancestorBox($i + 1, 'large'); ?>
        </div>
    <?php } ?>

    <!-- Third column split -->
    <div class="ancestor_split" style="top: <?= $column3_top; ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
    <div class="ancestor_split" style="top: <?= ($column3_top + 162); ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
    <div class="ancestor_split" style="top: <?= ($column3_top + 480); ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
    <div class="ancestor_split" style="top: <?= ($column3_top + 642); ?>px; left: <?= ($column3_left + 32); ?>px; height: 80px;"></div>
    <!-- Third column names -->
    <?php for ($i = 1; $i < 5; $i++) { ?>
        <div class="ancestorName <?= $data["sexe"][$i + 3] == 'M' ? 'box_man' : 'box_woman'; ?>" style="top: <?= (($column3_top - 279) + ($i * 240)); ?>px; left: <?= ($column3_left + 40); ?>px; height: 80px; width:200px;">
            <?= $ancestorBox->ancestorBox($i + 3, 'large'); ?>
        </div>
    <?php
    }

    // *** Fourth column line ***
    for ($i = 1; $i < 3; $i++) {
        echo '<div class="ancestor_line" style="top: ' . ($column4_top + ($i * 485)) . 'px; left: ' . ($column4_left + 24) . 'px; height: 240px;"></div>';
    }
    // *** Fourth column split ***
    for ($i = 1; $i < 5; $i++) {
        echo '<div class="ancestor_split" style="top: ' . (($column4_top + 185) + ($i * 240)) . 'px; left: ' . ($column4_left + 32) . 'px; height: 120px;"></div>';
    }
    // *** Fourth column names ***
    for ($i = 1; $i < 9; $i++) {
    ?>
        <div class="ancestorName <?= $data["sexe"][$i + 7] == 'M' ? 'box_man' : 'box_woman'; ?>" style="top: <?= (($column4_top + 265) + ($i * 120)); ?>px; left: <?= ($column4_left + 40); ?>px; height: 80px; width:200px;">
            <?= $ancestorBox->ancestorBox($i + 7, 'large'); ?>
        </div>
    <?php
    }

    // *** Fifth column line ***
    for ($i = 1; $i < 5; $i++) {
        echo '<div class="ancestor_line" style="top: ' . ($column5_top + ($i * 240)) . 'px; left: ' . ($column5_left + 24) . 'px; height: 120px;"></div>';
    }
    // *** Fifth column split ***
    for ($i = 1; $i < 9; $i++) {
        echo '<div class="ancestor_split" style="top: ' . (($column5_top + 90) + ($i * 120)) . 'px; left: ' . ($column5_left + 32) . 'px; height: 60px;"></div>';
    }
    // *** Fifth column names ***
    for ($i = 1; $i < 17; $i++) {
    ?>
        <div class="ancestorName <?= $data["sexe"][$i + 15] == 'M' ? 'box_man' : 'box_woman'; ?>" style="top: <?= (($column5_top + 125) + ($i * 60)); ?>px; left: <?= ($column5_left + 40); ?>px; height: 50px; width:200px;">
            <?= $ancestorBox->ancestorBox($i + 15, 'medium'); ?>
        </div>
    <?php
    }

    // *** Last column line ***
    for ($i = 1; $i < 9; $i++) {
        echo '<div class="ancestor_line" style="top: ' . ($column6_top + ($i * 120)) . 'px; left: ' . ($column6_left + 24) . 'px; height: 60px;"></div>';
    }
    // *** Last column split ***
    for ($i = 1; $i < 17; $i++) {
        echo '<div class="ancestor_split" style="top: ' . (($column6_top + 45) + ($i * 60)) . 'px; left: ' . ($column6_left + 32) . 'px; height: 30px;"></div>';
    }
    // *** Last column names ***
    for ($i = 1; $i < 33; $i++) {
    ?>
        <div class="ancestorName <?= $data["sexe"][$i + 31] == 'M' ? 'box_man' : 'box_woman'; ?>" style="top: <?= (($column6_top + 66) + ($i * 30)); ?>px; left: <?= ($column6_left + 40); ?>px; height:16px; width:200px;">
            <?= $ancestorBox->ancestorBox($i + 31, 'small'); ?>
        </div>
    <?php } ?>
</div>


<?php /*
<!-- TODO: Test 1 new layout -->
<div class="container-xl my-4">
    <?php
    // Example: $generations = array of arrays, each subarray is a generation with person IDs
    // You need to build $generations from your $data array
    $generations = [
        [1],           // Generation 1 (root)
        [2, 3],         // Generation 2
        [4, 5, 6, 7],     // Generation 3
        [8, 9, 10, 11, 12, 13, 14, 15] // Generation 4
    ];
    foreach ($generations as $gen) {
        $colWidth = intval(12 / count($gen)); // Bootstrap columns per ancestor
    ?>
        <div class="row justify-content-center mb-4">
            <?php
            foreach ($gen as $id) {
                $sexClass = isset($data["sexe"][$id]) && $data["sexe"][$id] == 'M' ? 'box_man' : 'box_woman';
            ?>
                <div class="col-<?php echo $colWidth; ?> d-flex justify-content-center">
                    <div class="card p-2 text-center <?php echo $sexClass; ?>" style="min-width:180px; max-width:220px;">
                        <?php echo ancestor_chart_person($id, 'large'); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
*/ ?>


<?php /*
<!-- TEST 2, including SVG lines -->
<style>
    .ancestor-box {
        width: 120px;
        height: 60px;
        margin: 0 10px;
        z-index: 3;
    }
</style>

<div class="container position-relative" style="min-height: 500px;">
    <!-- SVG overlay for lines -->
    <svg class="ancestor-lines position-absolute top-0 start-0" width="100%" height="100%" style="pointer-events:none; z-index:2;">
        <!-- Lines will be drawn here by JS -->
    </svg>

    <!-- Generation 1 -->
    <div class="row justify-content-center" style="margin-top: 40px;">
        <div class="col-4 d-flex justify-content-center">
            <div class="card ancestor-box text-center" id="ancestor-1">Ancestor 1</div>
        </div>
    </div>

    <!-- Generation 2 -->
    <div class="row justify-content-center" style="margin-top: 80px;">
        <div class="col-2 d-flex justify-content-center">
            <div class="card ancestor-box text-center" id="ancestor-2">Ancestor 2</div>
        </div>
        <div class="col-2 d-flex justify-content-center">
            <div class="card ancestor-box text-center" id="ancestor-3">Ancestor 3</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function getCenter(el) {
            const rect = el.getBoundingClientRect();
            const parentRect = el.offsetParent.getBoundingClientRect();
            return {
                x: rect.left - parentRect.left + rect.width / 2,
                y: rect.top - parentRect.top + rect.height / 2
            };
        }

        const svg = document.querySelector('.ancestor-lines');
        svg.innerHTML = ''; // Clear previous lines

        // Get elements
        const parent = document.getElementById('ancestor-1');
        const child1 = document.getElementById('ancestor-2');
        const child2 = document.getElementById('ancestor-3');

        // Get centers
        const p = getCenter(parent);
        const c1 = getCenter(child1);
        const c2 = getCenter(child2);

        // Draw lines
        function drawLine(x1, y1, x2, y2) {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', x1);
            line.setAttribute('y1', y1);
            line.setAttribute('x2', x2);
            line.setAttribute('y2', y2);
            line.setAttribute('stroke', '#888');
            line.setAttribute('stroke-width', 2);
            svg.appendChild(line);
        }

        drawLine(p.x, p.y, c1.x, c1.y);
        drawLine(p.x, p.y, c2.x, c2.y);
    });
</script>
*/ ?>