<head>
    <!--
    THIS SCRIPT IS FOR BACKWARDS COMPATIBILITY AND GENDEX SITES ONLY!

    November 2024: this script is still needed for links from stamboomzoeker.nl.
    Example: http://127.0.0.1/humo-genealogy/gezin.php?database=humo2_&id=F59&hoofdpersoon=I151
    url_rewrite off: http://127.0.0.1/humo-genealogy/family.php?database=humo2_&id=F59&main_person=I151
    url_rewrite on:  http://127.0.0.1/humo-genealogy/family/3/F59?main_person=I151
    -->

    <?php
    $database = ''; // *** standard: show first family tree ***
    if (isset($_GET["database"])) {
        $database = $_GET["database"];
    }
    if (isset($_POST["database"])) {
        $database = $_POST["database"];
    }

    $id = 1; // *** standard: show first family ***
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
    }

    $main_person = ''; // *** Main person of family ***
    if (isset($_GET["hoofdpersoon"])) {
        $main_person = $_GET["hoofdpersoon"];
    }
    if (isset($_POST["hoofdpersoon"])) {
        $main_person = $_POST["hoofdpersoon"];
    }
    ?>

    <script>
        window.location = "family.php?database=<?= $database; ?>&id=<?= $id; ?><?= $main_person != '' ? '&main_person=' . $main_person : ''; ?>";
    </script>
</head>