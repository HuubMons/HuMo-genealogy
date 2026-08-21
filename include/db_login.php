<?php
// *** To make HuMo-genealogy work, fill these lines properly! ***
// *** Om HuMo-genealogy werkend te krijgen onderstaande regels GOED invullen! ***
define("DATABASE_HOST",     'mysql');
define("DATABASE_USERNAME", 'root');
define("DATABASE_PASSWORD", '');
define("DATABASE_NAME",     'humo-gen');


/** 
 * Check if PDO driver is available 
 * @todo will be on install only
 */
if (!defined('PDO::ATTR_DRIVER_NAME')) { ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
        <title>Humo-genealogy | Error</title>
    </head>

    <body>
        <h1 class="text-danger">ERROR: The PDO driver is unavailable!</h1>
        <p>
            Possible solutions:<br><br>
            1) Update PHP to 7.x<br>
            2) Check if the PDO driver is enabled.<br>
            3) If the PDO driver is enabled but you receive this message from the Admin screen, try adding these three lines to the admin/php.ini file:<br>
            extension=pdo.so<br>
            extension=pdo_sqlite.so<br>
            extension=pdo_mysql.so<br>
        </p>
        <p>
            If it\'s not possible to use PDO, you have to downgrade to HuMo-genealogy 4.9.4!<br>
            Download HuMo-genealogy 4.9.4 at: <a href="https://sourceforge.net/projects/humo-gen/files">Sourceforge</a>
        </p>
    </body>

    </html>

    <?php exit();
}


try {

    $dsn = 'mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_NAME . ';charset=utf8';
    $dbh = new \PDO($dsn, DATABASE_USERNAME, DATABASE_PASSWORD);

    /** @todo improve genealogical dates in database to remove those 2 lines. */
    $dbh->query("SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''));");
    $dbh->query("SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''),'NO_ZERO_IN_DATE',''));");

    $database_check = 1;

} catch (PDOException $e) {

    unset($database_check);

    if (!isset($ADMIN)) { ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
            <title>Humo-genealogy | Error</title>
        </head>

        <body>
            <div class="container">
                <h1 class="text-danger">ERROR: Database is not yet installed!</h1>
                <p>
                    Possible problems:<br>
                    - Login file not yet configured.<br>
                    - Database not yet installed.<br>
                    Go to the <a href="admin">administration area</a> to solve this problem.
                </p>
            </div>
        </body>

        </html>
    <?php exit();
    }
}
