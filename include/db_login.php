<?php
// *** To make HuMo-genealogy work, fill these lines properly! ***
// *** Om HuMo-genealogy werkend te krijgen onderstaande regels GOED invullen! ***
define("DATABASE_HOST",     'mysql');
define("DATABASE_USERNAME", 'root');
define("DATABASE_PASSWORD", '');
define("DATABASE_NAME",     'humo-gen');

// *** Needed for Docker ***
$DATABASE_HOST = '';
$DATABASE_USERNAME = '';
$DATABASE_PASSWORD = '';
$DATABASE_NAME = '';
$USE_ENV_FOR_DB = true;

// *** DON'T CHANGE ANYTHING BELOW THIS LINE! ***
// *** HIERONDER NIETS WIJZIGEN! ***

// *** Check if PDO driver is available ***
if (!defined('PDO::ATTR_DRIVER_NAME')) {
?>
    <html>

    <head>
        <title>ERROR</title>
    </head>

    <body>
        <h2>ERROR: The PDO driver is unavailable!</h2>
        Possible solutions:<br><br>
        1) Update PHP to 7.x<br>
        2) Check if the PDO driver is enabled.<br>
        3) If the PDO driver is enabled but you receive this message from the Admin screen, try adding these three lines to the admin/php.ini file:<br>
        extension=pdo.so<br>
        extension=pdo_sqlite.so<br>
        extension=pdo_mysql.so<br>

        <p>If it\'s not possible to use PDO, you have to downgrade to HuMo-genealogy 4.9.4!<br>
            1) Download HuMo-genealogy 4.9.4 at: <a href="https://sourceforge.net/projects/humo-gen/files">Sourceforge</a><br>
            2) Follow <a href="https://sourceforge.net/projects/humo-gen/files/HuMo-gen_Manual/">HuMo-gen installation</a> instructions.
    </body>

    </html>
    <?php
    exit();
}

// *** Override the database connection values with environment variables ***
$temp_db_value = getenv("MYSQL_DATABASE", true);
if ($USE_ENV_FOR_DB && $temp_db_value != false && $temp_db_value != '') {
    $DATABASE_NAME = $temp_db_value;

    //$temp_db_value = getenv("MYSQL_HOST", true);
    //if ($USE_ENV_FOR_DB && $temp_db_value != false && $temp_db_value != '') {
    //	$DATABASE_HOST=$temp_db_value;
    $DATABASE_HOST = 'mariadb';

    $temp_db_value = getenv("MYSQL_USER", true);
    if ($USE_ENV_FOR_DB && $temp_db_value != false && $temp_db_value != '') {
        $DATABASE_USERNAME = $temp_db_value;
    }

    $temp_db_value = getenv("MYSQL_PASSWORD", true);
    if ($USE_ENV_FOR_DB && $temp_db_value != false && $temp_db_value != '') {
        $DATABASE_PASSWORD = $temp_db_value;
    }

    // *** Open database using PDO **
    $conn = 'mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8';
    try {
        $dbh = new PDO($conn, $DATABASE_USERNAME, $DATABASE_PASSWORD);
        $database_check = 1;
    } catch (PDOException $e) {
        unset($database_check);
        if (!isset($ADMIN)) {
    ?>
            <br>
            <font color=red>
                <b>
                    Database is not yet installed! Possible problems:<br>
                    - Login file not yet configured.<br>
                    - Database not yet installed.<br>
                    Go to the <a href="admin">administration area</a> to solve this problem.
                    <p>De database is nog niet bereikbaar! Mogelijke oorzaken:<br>
                        - Het login bestand is niet goed ingevuld.<br>
                        - De database is nog niet gemaakt.<br>
                        Ga naar het <a href="admin">administratie scherm</a> om dit probleem op te lossen.
                </b>
            </font>
        <?php
            exit();
        }
    }
} else {
    // *** Open database using PDO **
    $conn = 'mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_NAME . ';charset=utf8';
    try {
        $dbh = new PDO($conn, DATABASE_USERNAME, DATABASE_PASSWORD);
        $database_check = 1;
    } catch (PDOException $e) {
        unset($database_check);
        //echo $e->getMessage() . "<br/>";
        if (!isset($ADMIN)) {
        ?>
            <br>
            <font color=red>
                <b>
                    Database is not yet installed! Possible problems:<br>
                    - Login file not yet configured.<br>
                    - Database not yet installed.<br>
                    Go to the <a href="admin">administration area</a> to solve this problem.
                    <p>De database is nog niet bereikbaar! Mogelijke oorzaken:<br>
                        - Het login bestand is niet goed ingevuld.<br>
                        - De database is nog niet gemaakt.<br>
                        Ga naar het <a href="admin">administratie scherm</a> om dit probleem op te lossen.
                </b>
            </font>
        <?php
            exit();
        }
    }
}

if (isset($database_check) && $database_check == 1) {
    // TODO improve genealogical dates in database, then remove this code.
    // *** Added in mar. 2022: disable NO_ZERO_DATE and NO_ZERO_IN_DATE. To solve sorting problems in genealogical dates. ***
    $dbh->query("SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''));");

    // TODO improve queries, then remove this code.
    // *** Added in mar. 2023. To prevent double results in search results ***
    $dbh->query("SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''),'NO_ZERO_IN_DATE',''));");
}

// *** Show a message at frontpage at NEW installation when table installation isn't completed yet ***
if (!isset($ADMIN)) {
    try {
        $result = $dbh->query("SELECT COUNT(*) FROM humo_settings");
    } catch (PDOException $e) {
        ?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>HuMo-genealogy</title>
        </head>

        <body>
            Installation of HuMo-genealogy is not yet completed.<br>
            Installatie van HuMo-genealogy is nog niet voltooid.
        </body>

        </html>
<?php
        exit();
    }
}
