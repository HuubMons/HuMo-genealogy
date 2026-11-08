<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011 Huub: translated all variables to english.
 * July 2024 Huub: removed doublescroll and html2canvas. Just use browser to print and scroll.
 */

$ancestorBox = new \Genealogy\Include\AncestorBox();

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

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

include __DIR__ . '/partial/ancestor_header.php';
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




<!-- Test 2 use SVG, first do a full test in this script -->
<?php /*
<style>
  .person { fill: #fff; stroke: #333; stroke-width: 2; }
  .line   { stroke: #666; stroke-width: 2; }
</style>
*/
?>
<?php
$showTEST = false; // Set to false to disable test

if ($showTEST) {
    $personId = $data["main_person"]; // GEDCOM number of the main person (e.g., I23)
    $maxGen = 6; // Number of generations to display

    // Disable test here.
    $tree = buildAncestorTree($personId, $maxGen);
    $layout = calculateAncestorLayout($tree);

    // Store SVG output
    $ancestorSvg = renderAncestorSvg($layout);

    // Test: output SVG length
    error_log('SVG length: ' . strlen($ancestorSvg));
}

// Output zoom controls and SVG
?>


<?php if ($showTEST) { ?>
    <style>
        #ancestor-svg-container {
            border: 1px solid #ddd;
            margin: 10px 0;
            overflow: auto;
            background: #f9f9f9;
            padding: 10px;
            max-width: 100%;
            max-height: 80vh;
        }

        #ancestor-svg-wrapper {
            display: inline-block;
            transform-origin: 0 0;
            transition: transform 0.2s ease;
        }

        #ancestor-svg-wrapper svg {
            display: block;
            width: 2000px;
            height: auto;
            max-width: none;
        }

        .zoom-controls {
            margin-bottom: 10px;
        }

        .zoom-controls button {
            padding: 8px 15px;
            margin-right: 5px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .zoom-controls button:hover {
            background: #45a049;
        }

        .zoom-level {
            display: inline-block;
            margin-left: 15px;
            font-size: 14px;
            color: #333;
        }
    </style>

    <div class="zoom-controls">
        <button onclick="zoomOut()">🔍− Zoom Out</button>
        <button onclick="zoomReset()">Reset</button>
        <button onclick="zoomIn()">🔍+ Zoom In</button>
        <span class="zoom-level">Zoom: <span id="zoomPercent">100</span>%</span>
    </div>

    <div id="ancestor-svg-container">
        <div id="ancestor-svg-wrapper">
            <?php echo $ancestorSvg; ?>
        </div>
    </div>

    <script>
        let currentZoom = 1;
        const zoomStep = 0.2;
        const minZoom = 0.2;
        const maxZoom = 5;

        function updateZoom() {
            const wrapper = document.getElementById('ancestor-svg-wrapper');
            wrapper.style.transform = `scale(${currentZoom})`;
            document.getElementById('zoomPercent').textContent = Math.round(currentZoom * 100);
        }

        function zoomIn() {
            if (currentZoom < maxZoom) {
                currentZoom = Math.min(currentZoom + zoomStep, maxZoom);
                updateZoom();
            }
        }

        function zoomOut() {
            if (currentZoom > minZoom) {
                currentZoom = Math.max(currentZoom - zoomStep, minZoom);
                updateZoom();
            }
        }

        function zoomReset() {
            currentZoom = 1;
            updateZoom();
        }

        // Allow keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey || event.metaKey) {
                if (event.key === '+' || event.key === '=') {
                    event.preventDefault();
                    zoomIn();
                } else if (event.key === '-') {
                    event.preventDefault();
                    zoomOut();
                } else if (event.key === '0') {
                    event.preventDefault();
                    zoomReset();
                }
            }
        });

        // Fit chart to container width on load
        document.addEventListener('DOMContentLoaded', () => {
            const svg = document.querySelector('#ancestor-svg-wrapper svg');
            const container = document.getElementById('ancestor-svg-container');
            if (svg && container) {
                const vb = svg.viewBox && svg.viewBox.baseVal ? svg.viewBox.baseVal : null;
                const viewBoxWidth = vb && vb.width ? vb.width : 2000;
                const availableW = container.clientWidth || viewBoxWidth;

                // Fit to width, gentle boost for readability
                const fitW = availableW / viewBoxWidth;
                const boosted = fitW * 1.2;

                currentZoom = Math.max(minZoom, Math.min(boosted, maxZoom));
                updateZoom();
            }
        });
    </script>

<?php } ?>

<?php
function buildAncestorTree($personGedcom, $maxGen)
{
    // Build a nested array representing the ancestor tree from real data
    // Minimal viable implementation: uses parent relations to fetch parents up to $maxGen generations
    global $db_functions;

    $personDb = $db_functions->get_person($personGedcom);
    if (!$personDb) {
        return [];
    }

    // Helper to format display name with privacy
    $personPrivacy = new \Genealogy\Include\PersonPrivacy();
    $personName = new \Genealogy\Include\PersonName();

    $root_priv = $personPrivacy->get_privacy($personDb);
    $root_name = $personName->get_person_name($personDb, $root_priv);

    // Build recursively
    $buildParents = function ($personDb, $gen, $y) use ($db_functions, $maxGen, $personPrivacy, $personName, &$buildParents) {
        $node = [
            'id' => $personDb->pers_gedcomnumber ?? '',
            'gen' => $gen,
            'x' => 0,
            'y' => $y,
            'name' => $personName->get_person_name($personDb, $personPrivacy->get_privacy($personDb))["standard_name"] ?? '',
            'sex' => $personDb->pers_sexe ?? 'U',
            'birth_date' => $personDb->pers_birth_date ?? '',
            'death_date' => $personDb->pers_death_date ?? '',
            'picture' => '' // Will be filled below
        ];

        // Try to get first picture and prefix with tree media path
        $picture_qry = $db_functions->get_events_connect('person', $personDb->pers_gedcomnumber, 'picture');
        if (isset($picture_qry[0])) {
            $pictureFile = $picture_qry[0]->event_event ?? '';

            // Derive media base path as AncestorBox does
            $tree_pict_path = $selectedFamilyTree->tree_pict_path ?? 'media/';
            if (substr($tree_pict_path, 0, 1) === '|') {
                $tree_pict_path = 'media/';
            }
            // Ensure trailing slash
            if ($tree_pict_path && substr($tree_pict_path, -1) !== '/' && substr($tree_pict_path, -1) !== '\\') {
                $tree_pict_path .= '/';
            }

            // Build full relative URL for the image
            $node['picture'] = $tree_pict_path . $pictureFile;
        }

        if ($gen >= $maxGen) {
            $node['children'] = [];
            return $node;
        }

        // Find family of parents (either via relation id or relation gedcomnumber)
        $familyDb = null;
        if (isset($personDb->parent_relation_id) && $personDb->parent_relation_id) {
            // get_family_partners returns both partner1 and partner2
            $familyDb = $db_functions->get_family_partners($personDb->parent_relation_id);
        } elseif (isset($personDb->parent_relation_gedcomnumber) && $personDb->parent_relation_gedcomnumber) {
            $familyDb = $db_functions->get_family($personDb->parent_relation_gedcomnumber);
        }

        $children = [];
        if ($familyDb) {
            // Partner1 = father (usually), Partner2 = mother — sex can be unknown, we only link
            // Spread parents vertically more aggressively to keep lines from converging in older generations
            $yOffset = pow(2, max(0, ($maxGen - $gen)));

            if (!empty($familyDb->partner1_gedcomnumber)) {
                $fatherDb = $db_functions->get_person($familyDb->partner1_gedcomnumber);
                if ($fatherDb) {
                    // Place father above current node
                    $children[] = $buildParents($fatherDb, $gen + 1, $y - $yOffset);
                }
            }
            if (!empty($familyDb->partner2_gedcomnumber)) {
                $motherDb = $db_functions->get_person($familyDb->partner2_gedcomnumber);
                if ($motherDb) {
                    // Place mother below current node
                    $children[] = $buildParents($motherDb, $gen + 1, $y + $yOffset);
                }
            }
        }

        $node['children'] = $children;
        return $node;
    };

    return $buildParents($personDb, 1, 0);
}

function calculateAncestorLayout($tree)
{
    // Calculate positions for each ancestor box and collect parent-child edges
    $nodes = []; // map id => node info
    $edges = []; // list of ['parent' => id, 'child' => id]

    function traverse($node, $parent, &$nodes, &$edges)
    {
        $nodes[$node['id']] = [
            'id' => $node['id'],
            'gen' => $node['gen'],
            'x' => $node['x'],
            'y' => $node['y'],
            'name' => $node['name'],
            'sex' => $node['sex'] ?? 'U',
            'birth_date' => $node['birth_date'] ?? '',
            'death_date' => $node['death_date'] ?? '',
            'picture' => $node['picture'] ?? ''
        ];
        if ($parent !== null) {
            $edges[] = ['parent' => $parent['id'], 'child' => $node['id']];
        }
        foreach ($node['children'] as $child) {
            traverse($child, $node, $nodes, $edges);
        }
    }

    traverse($tree, null, $nodes, $edges);
    return ['nodes' => $nodes, 'edges' => $edges];
}

function renderAncestorSvg($layout)
{
    // Render the SVG with boxes and elbow lines based on the layout
    // Moderate viewBox so boxes render larger by default
    //$svg = '<svg viewBox="0 0 2000 1300" xmlns="http://www.w3.org/2000/svg">';
    $svg = '<svg viewBox="0 0 4000 40000" xmlns="http://www.w3.org/2000/svg">';

    // Constants for box size (match rects below)
    $BOX_W = 380;
    $BOX_H = 110;

    // Find actual generation depth
    $maxActualGen = 1;
    if (isset($layout['nodes'])) {
        foreach ($layout['nodes'] as $n) {
            if ($n['gen'] > $maxActualGen) {
                $maxActualGen = $n['gen'];
            }
        }
    }

    // Scale y-spacing based on actual depth: fewer gens = smaller spacing
    // If only 2-3 gens, use 60; if 4-5, use 85; if 6+, use 110
    if ($maxActualGen <= 3) {
        $ySpacing = 60;
    } elseif ($maxActualGen <= 5) {
        $ySpacing = 85;
    } else {
        $ySpacing = 110;
    }

    // Compute vertical offset so the minimum Y is visible
    $minY = PHP_FLOAT_MAX;
    $maxY = -PHP_FLOAT_MAX;
    if (isset($layout['nodes'])) {
        foreach ($layout['nodes'] as $n) {
            $baseY = $n['y'] * $ySpacing;
            if ($baseY < $minY) $minY = $baseY;
            if ($baseY > $maxY) $maxY = $baseY;
        }
    }
    // Add 20px top margin
    $offsetY = ($minY === PHP_FLOAT_MAX) ? 0 : (0 - $minY + 20);

    // Draw connections first (behind boxes)
    if (isset($layout['edges']) && isset($layout['nodes'])) {
        foreach ($layout['edges'] as $edge) {
            $parent = $layout['nodes'][$edge['parent']];
            $child = $layout['nodes'][$edge['child']];

            // Compute top-left positions from gen/y (increased spacing)
            $px0 = $parent['gen'] * 450;
            $py0 = ($parent['y'] * $ySpacing) + $offsetY;
            $cx0 = $child['gen'] * 450;
            $cy0 = ($child['y'] * $ySpacing) + $offsetY;

            // Centers of boxes
            $px = $px0 + ($BOX_W / 2);
            $py = $py0 + ($BOX_H / 2);
            $cx = $cx0 + ($BOX_W / 2);
            $cy = $cy0 + ($BOX_H / 2);

            // Midpoint x for elbow path
            $mx = ($cx + $px) / 2;

            // Draw elbow path from child to parent
            $svg .= "<path d='M $cx $cy L $mx $cy L $mx $py L $px $py' fill='none' stroke='#555' stroke-width='2'/>";
        }
    }

    // Draw boxes and labels
    if (isset($layout['nodes'])) {
        foreach ($layout['nodes'] as $node) {
            $x = $node['gen'] * 450;
            $y = ($node['y'] * $ySpacing) + $offsetY;

            // Determine fill color based on sex (matching box_man and box_woman CSS)
            $fillColor = '#ffffff'; // default
            if (isset($node['sex'])) {
                if ($node['sex'] == 'M') {
                    $fillColor = '#e0f0ff'; // box_man color
                } elseif ($node['sex'] == 'F') {
                    $fillColor = '#ffe5e0'; // box_woman color
                }
            }

            // Box with gender-based color and border
            $svg .= "<rect x='$x' y='$y' width='$BOX_W' height='$BOX_H' fill='$fillColor' stroke='#AAA' stroke-width='1' rx='4' ry='4'/>";

            // Add picture if available (left side, 60px tall)
            $textStartX = $x + 10;
            if (!empty($node['picture'])) {
                $imgPath = htmlspecialchars($node['picture']);
                $svg .= "<image x='" . ($x + 5) . "' y='" . ($y + 5) . "' width='60' height='60' href='$imgPath' preserveAspectRatio='xMidYMid slice' clip-path='url(#roundClip)'/>";
                $textStartX = $x + 75;
            }

            // Text with wrapping and centering - break into two lines if needed
            // Decode any incoming HTML entities (e.g., &quot;) before escaping for SVG
            $name = htmlspecialchars(html_entity_decode($node['name'], ENT_QUOTES | ENT_HTML5));
            $words = explode(' ', $name);

            // Simple wrap: if more than 2 words or very long, split after first part
            $line1 = '';
            $line2 = '';
            if (strlen($name) > 20) {
                // Break at space if possible, near the middle
                $midpoint = intval(strlen($name) / 2);
                $spacePos = strpos($name, ' ', $midpoint);
                if ($spacePos !== false) {
                    $line1 = substr($name, 0, $spacePos);
                    $line2 = substr($name, $spacePos + 1);
                } else {
                    $line1 = $name;
                }
            } else {
                $line1 = $name;
            }

            // Birth/Death dates
            $birthYear = '';
            $deathYear = '';
            if (!empty($node['birth_date'])) {
                // Extract year from date (format: YYYY or YYYY-MM-DD)
                $birthYear = preg_replace('/^(\d{4}).*/', '$1', $node['birth_date']);
            }
            if (!empty($node['death_date'])) {
                $deathYear = preg_replace('/^(\d{4}).*/', '$1', $node['death_date']);
            }
            $dateStr = '';
            if ($birthYear && $deathYear) {
                $dateStr = "* $birthYear — † $deathYear";
            } elseif ($birthYear) {
                $dateStr = "* $birthYear";
            } elseif ($deathYear) {
                $dateStr = "† $deathYear";
            }

            // Semi-transparent background for text (for readability)
            $textBoxHeight = $line2 ? 55 : 35;
            $svg .= "<rect x='" . ($textStartX - 2) . "' y='" . ($y + 5) . "' width='" . ($BOX_W - $textStartX + $x - 2) . "' height='$textBoxHeight' fill='white' opacity='0.8' rx='2'/>";

            // Name text
            $svg .= "<text x='$textStartX' y='" . ($y + 20) . "' fill='#000' font-family='Arial' font-size='14' font-weight='bold'>";
            $svg .= "<tspan x='$textStartX' dy='0'>$line1</tspan>";
            if ($line2) {
                $svg .= "<tspan x='$textStartX' dy='16'>$line2</tspan>";
            }
            $svg .= "</text>";

            // Date text (smaller)
            if ($dateStr) {
                $svg .= "<text x='$textStartX' y='" . ($y + 65) . "' fill='#666' font-family='Arial' font-size='11'>";
                $svg .= htmlspecialchars($dateStr);
                $svg .= "</text>";
            }
        }
    }

    $svg .= '</svg>';
    return $svg;
}
