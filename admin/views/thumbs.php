<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$showMedia = new \Genealogy\Include\ShowMedia();
$resizePicture = new \Genealogy\Include\ResizePicture();

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();
$personLink = new \Genealogy\Include\PersonLink();

$prefx = '../'; // to get out of the admin map

$stmt = $dbh->prepare("SELECT * FROM humo_trees WHERE tree_id = :tree_id");
$stmt->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
$stmt->execute();
$data2Db = $stmt->fetch(PDO::FETCH_OBJ);
?>

<ul class="nav nav-tabs mt-1">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_settings') echo 'active'; ?>" href="index.php?page=<?= $page; ?>"><?= __('Picture settings'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_thumbnails') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=picture_thumbnails"><?= __('Create thumbnails'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_show') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=picture_show"><?= __('Show thumbnails'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($thumbs['menu_tab'] == 'picture_categories') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=picture_categories"><?= __('Photo album categories'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">
    <?php if ($thumbs['menu_tab'] == 'picture_settings' || $thumbs['menu_tab'] == 'picture_thumbnails' || $thumbs['menu_tab'] == 'picture_show') { ?>
        <div class="p-3 m-2 genealogy_search">

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="tree" class="col-form-label"><?= __('Choose family tree'); ?></label>
                </div>

                <div class="col-md-7">
                    <?= $selectTree->select_tree($dbh, $page, $tree_id, $thumbs['menu_tab']); ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="picture_path" class="col-form-label"><?= __('Path to the pictures'); ?></label>
                </div>

                <!-- Set path to pictures -->
                <div class="col-md-8">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="page" value="thumbs">
                        <input type="hidden" name="menu_tab" value="<?= $thumbs['menu_tab']; ?>">
                        <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="yes" name="default_path" id="default_path" <?= $thumbs['default_path'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="default_path">
                                <?= __('Use default picture path:'); ?> <b>media/</b>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="no" name="default_path" id="default_path" <?= !$thumbs['default_path'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="default_path">
                                <input type="text" name="tree_pict_path" value="<?= $thumbs['own_pict_path']; ?>" size="40" placeholder="../pictures/" class="form-control form-control-sm">
                            </label>
                        </div>

                        <?php printf(__('Example of picture path:<br>
www.myhomepage.nl/humo-gen/ => folder for %s files.<br>
www.myhomepage.nl/pictures/ => folder for pictures.<br>
Use a relative path, exactly as shown here: <b>../pictures/</b>'), 'HuMo-genealogy'); ?><br><br>

                        <input type="submit" name="change_tree_data" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"><br>
                    </form>
                </div>
            </div>

            <?php
            // *** Show subdirectories ***
            function get_media_files($first, $prefx, $path)
            {
                $ignore = array('cms', 'slideshow', 'thumbs', '.', '..');
                $dh = opendir($prefx . $path);
                while (false !== ($filename = readdir($dh))) {
                    if (!in_array($filename, $ignore) && is_dir($prefx . $path . $filename)) {
                        if ($first == false) {
                            echo ' ' . __('Subdirectories:');
                            $first = true;
                        }
                        echo '<br>' . $path . $filename . '/';
                        get_media_files($first, $prefx, $path . $filename . '/');
                    }
                }
                closedir($dh);
            }
            ?>

            <div class="row mb-2">
                <div class="col-md-4"><?= __('Status of picture path'); ?></div>

                <div class="col-md-7">
                    <?php if ($thumbs['tree_pict_path'] != '' && file_exists($prefx . $thumbs['tree_pict_path'])) { ?>
                        <span class="bg-success-subtle"><?= __('Picture path exists.'); ?></span>

                    <?php
                        // *** Show subdirectories ***
                        $first = false;
                        get_media_files($first, $prefx, $thumbs['tree_pict_path']);
                    } else {
                        echo '<span class="bg-warning-subtle"><b>' . __('Picture path doesn\'t exist!') . '</b></span>';
                    }
                    ?>
                </div>
            </div>

            <!-- Create thumbnails -->
            <?php
            if ($thumbs['menu_tab'] == 'picture_thumbnails') {
                $thumb_height = 120; // *** Standard thumb height ***
            ?>
                <div class="row mb-2">
                    <div class="col-md-4"><?= __('Create thumbnails'); ?></div>

                    <div class="col-md-7">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="page" value="thumbs">
                            <input type="hidden" name="menu_tab" value="picture_thumbnails">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <input type="submit" name="thumbnail" value="<?= __('Create thumbnails'); ?>" class="btn btn-sm btn-success">
                        </form>
                    </div>
                </div>
            <?php } ?>

            <!-- Show thumbnails -->
            <?php if ($thumbs['menu_tab'] == 'picture_show') { ?>
                <div class="row mb-2">
                    <div class="col-md-4"><?= __('Show thumbnails'); ?></div>

                    <div class="col-md-7">
                        <form method="POST" action="index.php">
                            <input type="hidden" name="page" value="thumbs">
                            <input type="hidden" name="menu_tab" value="picture_show">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <input type="submit" name="change_filename" value="<?= __('Show thumbnails'); ?>" class="btn btn-sm btn-success">
                            <?= ' ' . __('You can change filenames here.'); ?>
                        </form>
                    </div>
                </div>
            <?php } ?>

        </div>

        <?php if ($thumbs['menu_tab'] == 'picture_settings') { ?>
            <?php
            // TODO refactor
            $is_thumblib = false;
            $no_windows = (strtolower(substr(PHP_OS, 0, 3)) !== 'win');
            if ($no_windows || extension_loaded('gd')) {
                $is_thumblib = true;
            }

            // Auto create thumbnails
            if (isset($_POST["thumbnail_auto_create"]) && ($_POST["thumbnail_auto_create"] == 'y' || $_POST["thumbnail_auto_create"] == 'n')) {
                $db_functions->update_settings('thumbnail_auto_create', $_POST["thumbnail_auto_create"]);
                $humo_option["thumbnail_auto_create"] = $_POST["thumbnail_auto_create"];
            }
            ?>

            <div class="p-3 m-2 genealogy_search">
                <h4><?= __('General picture settings'); ?></h4>

                <?= __('To show pictures, also check the user-group settings: '); ?> <a href="index.php?page=groups"><?= __('User groups'); ?></a><br><br>

                <div class="row mb-2">
                    <div class="col-md-7">
                        <?= __('Imagick (images)'); ?>
                    </div>
                    <div class="col-md-auto">
                        <b><?= extension_loaded('imagick') ? strtolower(__('Yes')) : strtolower(__('No')); ?></b>
                    </div>
                </div>

                <?php if (extension_loaded('imagick') && $no_windows) { ?>
                    <div class="row mb-2">
                        <div class="col-md-7">
                            <?= __('Ghostscript (PDF support)'); ?>
                        </div>
                        <div class="col-md-auto">
                            <b><?= (trim(shell_exec('type -P gs'))) ? strtolower(__('Yes')) . '<br>' : strtolower(__('No')); ?></b>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-7">
                            <?= __('ffmpeg (movie support)'); ?>
                        </div>
                        <div class="col-md-auto">
                            <b><?= (trim(shell_exec('type -P ffmpeg'))) ? strtolower(__('Yes')) . '<br>' : strtolower(__('No')); ?></b>
                        </div>
                    </div>
                <?php } ?>

                <div class="row mb-2">
                    <div class="col-md-7">
                        <?= __('GD (images)'); ?>
                    </div>
                    <div class="col-md-auto">
                        <b><?= extension_loaded('gd') ? strtolower(__('Yes')) : strtolower(__('No')); ?></b>
                    </div>
                </div>

                <?php if (!$is_thumblib) { ?>
                    <?= __('No Thumbnail library available'); ?><br>
                <?php } ?>

                <!-- Automatically create thumbnails -->
                <form method="POST" action="index.php?page=thumbs">
                    <div class="row mb-2">
                        <div class="col-md-7">
                            <?= __('Automatically create thumbnails?'); ?>
                        </div>
                        <div class="col-md-auto">
                            <select size="1" name="thumbnail_auto_create" onChange="this.form.submit();" class="form-select form-select-sm">
                                <option value="n"><?= __('No'); ?></option>
                                <option value="y" <?= $humo_option["thumbnail_auto_create"] == 'y' ? 'selected' : ''; ?>><?= __('Yes'); ?></option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Media privacy mode -->
                <!-- TODO Refactor -->
                <!-- TODO add translations using sprintf('Some text %s etc.',$variable); -->
                <?php if (isset($_POST["media_privacy_mode"]) && ($_POST["media_privacy_mode"] == 'y' || $_POST["media_privacy_mode"] == 'n')) {
                    //I'm putting the code related to the "media privacy mode" option here because I don't see a better place. If I'm wrong, this should be moved.
                    //when media privacy mode is enabled/disabled and media dir is under root dir and this is apache we must update .htaccess content. Below we make validation and give detailed info to user

                    //function takes absolute directory path and relative path - we can change this variables names to be more clear
                    function checkMediaPrivacySupport($testDir, $path)
                    {
                        global $htaccess_support;
                        //some log arrays
                        $messages = [];
                        $errors = [];

                        $htaccessFilePath = $testDir . DIRECTORY_SEPARATOR . '.htaccess';
                        $testFileJpg = 'HuMo_genealogy_test_file.jpg';
                        $testFileJpgPath = $testDir . DIRECTORY_SEPARATOR . $testFileJpg;
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                        $host = $_SERVER['HTTP_HOST'];
                        $serverAddress = $protocol . $host;
                        $testUrlJpg = $serverAddress . DIRECTORY_SEPARATOR . $path . $testFileJpg;
                        $testUrlDirIndex = $serverAddress . DIRECTORY_SEPARATOR . $path;
                        $htaccess_ok = false;
                        $mediaDirUnderRoot = null;

                        try {
                            if ($testDir) {
                                $messages[] = '✅ ' . __('This media directory exists.') . '<br>';
                            } else {
                                //if there is no dir we don't check more
                                $exception = '❌ ' . __('I can\'t find this directory. Check if it\'s created.') . '<br>';
                                throw new Exception($exception);
                            }

                            $realDirectory = realpath($testDir);
                            $documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);

                            // Check if the directory is under the document root
                            if ($realDirectory && $documentRoot && strpos($realDirectory, $documentRoot) === 0) {
                                $msg = '✅ ' . __('This directory is under the document root.');
                                $mediaDirUnderRoot = true;
                                if ($htaccess_support) {
                                    $msg .= ' ' . __('On Apache we can operate both modes (media privacy on and off).');
                                }
                                $messages[] = $msg . '<br>';
                            } else {
                                $messages[] = 'ℹ️ ' . __('This directory is outside the document root so media privacy mode is only one working for this directory.') . '<br>';
                                if ($_POST["media_privacy_mode"] === 'y') {
                                    $messages[] = '✅ ' . __('In privacy mode your files will be safe and will be displayed.') . '<br>';
                                }
                                if ($_POST["media_privacy_mode"] === 'n') {
                                    $messages[] = '❌ <span style="color: red;">' . __('In privacy mode disabled your files will still be safe but will not be displayed at all.') . '</span><br>';
                                }
                                $mediaDirUnderRoot = false;
                            }
                            //we create jpg file with one pixel for server testing purpouse
                            $jpegHexString = 'FFD8FFE000104A46494600010101000100010000'
                                . 'FFDB004300100B0C0E0C0A100E0D0E1211101318'
                                . '281A181616183123251D283A333D3C3933383740'
                                . '485C4E404457453738506D51575F626768673E4D'
                                . '71797064785C656763'
                                . 'FFC00011080001000103011100021101031101'
                                . 'FFC4001F000001050101010101010000000000'
                                . '00000102030405060708090A0B'
                                . 'FFC400B5100002010303020403050504040000'
                                . '017D0102030004110512213106411351610722'
                                . '7114328191A1082342B1C11552D1F024336272'
                                . '2A347835A445456476768798A2A3A4A5A6A7A8A9'
                                . 'AAB2B3B4B5B6B7B8B9BAC2C3C4C5C6C7C8C9CA'
                                . 'D2D3D4D5D6D7D8D9DAE1E2E3E4E5E6E7E8E9EA'
                                . 'F1F2F3F4F5F6F7F8F9FA'
                                . 'FFDA000C03010002110311003F00FDFCF8FFD9';
                            $jpgContent = hex2bin($jpegHexString);

                            // create test file
                            if (@file_put_contents($testFileJpgPath, $jpgContent) === false) {
                                $exception = '❌ ' . sprintf(__('Couldn\'t create "%s" in this directory. You will be unable to upload files. Check if directory has write permissions.'), $testFileJpg) . '<br>';

                                throw new Exception($exception);
                            } else {
                                $messages[] = '✅ ' . sprintf(__('Created "%s" in this directory. Upload to directory is possible.'), $testFileJpg) . '<br>';
                            }

                            if ($mediaDirUnderRoot && $htaccess_support) {
                                $htaccessContent = "Deny from all\n";
                                $messages[] = '<br><b>' . __('Checking if we can use .htaccess on Apache compatible servers:') . '</b><br>';
                                if (@file_put_contents($htaccessFilePath, $htaccessContent) === false) {
                                    $exception = '❌ ' . __('Couldn\'t create .htaccess in this directory. Check if directory has write permissions.') . '<br>';
                                    throw new Exception($exception);
                                } else {
                                    $messages[] = '✅ ' . __('Created .htaccess in this directory.') . '<br>';
                                }

                                $response = @file_get_contents($testUrlJpg);
                                if ($response == $jpgContent) {
                                    $messages[] = ("❌ I could read '$testFileJpgPath' ('$testUrlJpg') which is wrong. Despite creating a .htaccess file that forbids reading the file, I can access it. You probably don't have a misconfigured <a href='https://httpd.apache.org/docs/2.4/mod/core.html#allowoverride' target='_new'>AllowOverride directive </a>in your Apache configuration files. In privacy mode media will be served and visible but anyone with link can display it.<br>");
                                } else {
                                    $messages[] = '✅ ' . sprintf(__('Couldn\'t read %s which means test .htaccess protects files in this directory.'), $testFileJpgPath . ' (' . $testUrlJpg . ')') . '<br>';
                                    $htaccess_ok = true;
                                }

                                if (@unlink($htaccessFilePath)) {
                                    $messages[] = '✅ ' . __('Test .htaccess file deleted.') . '<br>';
                                } else {
                                    $messages[] = '❌ ' . __('Couldn\'t delete htaccess test file.') . '<br>';
                                }

                                // if htaccess works
                                if ($htaccess_ok) {
                                    $filePath = $testDir . '/.htaccess';
                                    if ($_POST["media_privacy_mode"] === 'y') {
                                        // .htaccess content with directive to not allow to get file by static link - file will be possible to get only by query url
                                        $htaccessContent = "Deny from all\n";
                                        if (@file_put_contents($filePath, $htaccessContent) !== false) {
                                            $messages[] = '✅ <span style="color: green;">' . __('File .htaccess permanently modified in this directory protecting your files.') . '</span><br>';
                                        } else {
                                            throw new Exception("❌ Check permissions. I couldn't modify .htaccess in '$testDir'.<br>");
                                        }
                                    } elseif ($_POST["media_privacy_mode"] === 'n') {
                                        $htaccessContent = '';
                                        if (@file_put_contents($filePath, $htaccessContent) !== false) {
                                            $messages[] = "❌ <span style='color: red;'>File .htaccess permanently modified in '$testDir' allowing direct access to files for anyone.</span><br>";
                                        } else {
                                            throw new Exception("❌ Check permissions. I couldn't modify .htaccess in '$testDir'.<br>");
                                        }
                                    }
                                }
                            } else {
                                //for non under root and no htacces support we also check if we can get testfile
                                $response = @file_get_contents($testUrlJpg);
                                if ($response == $jpgContent) {
                                    $messages[] = ("❌ <span style='color: red;'>I could read '$testFileJpgPath' ('$testUrlJpg'). In privacy mode media will be served and visible but anyone with link can display it.</span><br>");
                                } else {
                                    $messages[] = '✅ ' . sprintf(__('Couldn\'t read %s which means this directory is protected from direct access.'), $testFileJpgPath . ' (' . $testUrlJpg . ')') . '<br>';
                                    $htaccess_ok = true;
                                }
                            }
                            //not needed anymore so we can delete
                            if (@unlink($testFileJpgPath)) {
                                $messages[] = '✅ ' . sprintf(__('Test file "%s" deleted.'), $testFileJpg) . '<br>';
                            } else {
                                $messages[] = "❌ Couldn't delete test file ($testFileJpg).<br>";
                            }
                            // we also check if directory index is visible making the most threat to files
                            $headers = get_headers($testUrlDirIndex, 1);
                            // Check if the response is 200 OK or a directory listing
                            if (strpos($headers[0], '200 OK') !== false) {
                                $msg = "❌ <span style='color: red;'>Directory index for '$testUrlDirIndex' is publicly accessible! Everyone can get the media files list and display them!";
                                $msg .= "You can disable acces to directory index by: ";
                                if ($htaccess_ok) {
                                    $msg .= "enabling privacy mode on or ";
                                }
                                $msg .= "changing Your server configuration.";
                                if ($htaccess_support) {
                                    $msg .= "For Apache servers You can add <a href='https://httpd.apache.org/docs/2.4/mod/core.html#options'>Options -Indexes</a> for directory.";
                                }
                                $msg .= "</span><br>";
                                $messages[] = $msg;
                            } else {
                                $messages[] = '✅ <span style="color: green;">' . sprintf(__('Directory index for "%s" is not publicly accessible or indexing is disabled.'), $testUrlDirIndex) . '</span><br>';
                            }
                            $messagesArr['status'] = true;
                        } catch (Exception $e) {
                            // delete files on exceptions
                            $errors[] = $e->getMessage();
                            $messagesArr['status'] = false;
                        } finally {
                            $messagesArr['messages'] = $messages;
                            $messagesArr['errors'] = $errors;
                            return $messagesArr;
                        }
                    }
                    $htaccess_support = false;
                    //text which will be concatenated and use as info at the end
                    $text = '';
                    //first we will check what server soft user uses
                    $text .= __('Checking server:') . '<br>';
                    $serverName = $_SERVER['SERVER_SOFTWARE'];
                    //for simulating other options - delete after
                    // $serverName = 'Nginx';
                    if (strpos($serverName, 'Apache') !== false) {
                        $server_soft = 'Apache';
                        $htaccess_support = true;
                        $text .= '✅ ' . __('Apache. Media privacy mode is fully compatible with Apache. It can operate both modes.') . '<br>';
                    } elseif (strpos($serverName, 'LiteSpeed') !== false) {
                        $server_soft = 'LiteSpeed';
                        $htaccess_support = true;
                        $text .= "✅ LiteSpeed. It's Apache compatible server. Media privacy mode should be fully compatible with LiteSpeed. It can operate both modes.<br> ";
                    } elseif (strpos($serverName, 'Nginx') !== false) {
                        $server_soft = 'Nginx';
                        $text .= "❌ Nginx. Media privacy mode is not yet fully compatible with Nginx. You can achieve it by manually configuring your server or placing media directory outside root directory (but then normal mode will not work).<br>";
                    } else {
                        $server_soft = '❌ Unknown. We don&apos;t know if we can secure media directory.You can achieve it by manually configuring your server or placing media directory outside root directory (but then normal mode will not work).<br>';
                    }
                    $text .= "<hr>";


                    //we take tree paths and tree names to combine them
                    $tree_qry = "SELECT 
                    t.tree_id,
                    t.tree_pict_path,
                    tt.treetext_tree_id,
                    tt.treetext_name
                     FROM 
                    humo_trees t
                     JOIN 
                    humo_tree_texts tt
                     ON 
                    t.tree_id = tt.treetext_tree_id
                      WHERE 
                    t.tree_pict_path != 'EMPTY'";
                    $datasql = $dbh->query($tree_qry);
                    $rowCount = $datasql->rowCount();
                    $treepaths = [];
                    $tree_names = [];
                    for ($i = 0; $i < $rowCount; $i++) {
                        $tree_db = $datasql->fetch(PDO::FETCH_OBJ);
                        $tree_pict_path = $tree_db->tree_pict_path;
                        $tree_name = $tree_db->treetext_name;
                        if (substr($tree_pict_path, 0, 1) === '|') {
                            $tree_pict_path = 'media/';
                        }
                        if (!in_array($tree_pict_path, $treepaths)) $treepaths[] = $tree_pict_path;
                        $tree_names[$tree_pict_path][] = $tree_name;
                    }
                    // prepare arr databases for displaying
                    foreach ($treepaths as $key => $path) {
                        $testDirectory = realpath(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . $path);
                        $text .= '<h5>';
                        if ($testDirectory) {
                            $text .= __('Check media path:') . ' <b>' . $testDirectory . '</b>';
                        } else {
                            //this else is only to give output when path not exists
                            $text .= __('Check media path:') . ' <b>' . $path . '</b>';
                        }
                        $text .= '</h5>';

                        $text .= __('In use for family trees:') . '<br>';
                        // we are giving trees names for paths too - if the same path is used in many trees names are agregated for path
                        foreach ($tree_names as $key2 => $value2) {
                            if ($key2 === $path) {
                                $text .= implode(", ", array_slice($tree_names[$key2], 0, -1));
                                if (count($tree_names[$key2]) > 1) {
                                    $text .= ', ';
                                }
                                $text .= end($tree_names[$key2]) . '<br>';
                            }
                        }
                        $text .= '<br>';

                        // Get the realpath of the directory and the document root
                        $realDirectory = realpath($testDirectory);
                        $documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
                        try {
                            $check = checkMediaPrivacySupport($testDirectory, $path);
                            foreach ($check['messages'] as $message) {
                                $text .= "$message\n";
                            }
                            foreach ($check['errors'] as $error) {
                                $text .= "$error\n";
                            }
                            //imho not needed anymore - comment for a while than delete if no problems - all ported to function itself
                            if ($check['status'] === true) {
                                $htaccess_ok = true;
                            }
                        } catch (Exception $e) {
                            $text .= $e->getMessage();
                            //imho not needed anymore - comment for a while than delete if no problems - all ported to function itself
                            $htaccess_ok = false;
                        }
                        $text .= '<hr>';
                    }

                    $db_functions->update_settings('media_privacy_mode', $_POST["media_privacy_mode"]);
                    $humo_option["media_privacy_mode"] = $_POST["media_privacy_mode"];
                ?>

                    <div class="alert <?= $htaccess_support ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                        <?= $text; ?>
                        <!-- TODO: make function for automatic checks when directories are changed in tree options -->
                        <p class='alert alert-danger'>ℹ️ <?= __('If You create or change media path for any of your trees You must reenable, redisable this option.'); ?></p>
                    </div>
                <?php } ?>

                <form method="POST" action="index.php?page=thumbs">
                    <div class="row mb-2">
                        <div class="col-md-7">
                            <?= __('Secure media folder for direct access?'); ?><br>
                        </div>
                        <div class="col-md-auto">
                            <select size="1" name="media_privacy_mode" onChange="this.form.submit();" class="form-select form-select-sm">
                                <option value="n"><?= __('No'); ?></option>
                                <option value="y" <?= $humo_option["media_privacy_mode"] == 'y' ? 'selected' : ''; ?>><?= __('Yes'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-auto">
                            <?= __('If enabled, media files will be served by using an URL, otherwise media files will be served directly.'); ?><br>
                            <?= __('Only use this option if showing of media is disabled for visitors.'); ?>
                        </div>
                    </div>

                </form>

            </div>

        <?php
        }

        // *** Create picture thumbnails ***
        if ($thumbs['menu_tab'] == 'picture_thumbnails') {
        ?>
            <?= __('- Creating thumbnails<br>
- ATTENTION: it may be necessary to (temporarily) change access to the folder with the pictures (rwxrwxrwx)<br>
- Sometimes the php.ini has to be changed slightly, remove the ; before the line with:'); ?>
            <i>extension=php.gd2.dll</i>
        <?php }
    }


    // *** Picture categories ***
    if ($thumbs['menu_tab'] == 'picture_categories') {
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_photocat'");
        if (!$temp->rowCount()) {
            // no category database table exists - so create it
            // It has 4 columns:
            //     1. id
            //     2. name of category prefix- 2 letters and underscore chosen by admin (ws_   bp_)
            //     3. language for name of category
            //     4. name of category

            $albumtbl = "CREATE TABLE humo_photocat (
                photocat_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                photocat_order MEDIUMINT(6),
                photocat_prefix VARCHAR(30) CHARACTER SET utf8,
                photocat_language VARCHAR(10) CHARACTER SET utf8,
                photocat_name VARCHAR(50) CHARACTER SET utf8
        ) DEFAULT CHARSET=utf8";
            $dbh->query($albumtbl);
            // Enter the default category with default name that can be changed by admin afterwards
            $stmt = $dbh->prepare("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES (:prefix, :order, :language, :name)");
            $stmt->execute([
                ':prefix' => 'none',
                ':order' => 1,
                ':language' => 'default',
                ':name' => __('Photos')
            ]);
        }

        //echo '<h1 align=center>'.__('Photo album categories').'</h1>';

        $language_tree = $selected_language; // Default language
        if (isset($_GET['language_tree'])) {
            $language_tree = $_GET['language_tree'];
        }
        if (isset($_POST['language_tree'])) {
            $language_tree = $_POST['language_tree'];
        }

        if (isset($_GET['cat_drop2']) && $_GET['cat_drop2'] == 1 && !isset($_POST['save_cat'])) {
            // delete category and make sure that the order sequence is restored
            $stmt1 = $dbh->prepare("UPDATE humo_photocat SET photocat_order = (photocat_order-1) WHERE photocat_order > :cat_order");
            $stmt1->execute([':cat_order' => $_GET['cat_order']]);

            $stmt2 = $dbh->prepare("DELETE FROM humo_photocat WHERE photocat_prefix = :cat_prefix");
            $stmt2->execute([':cat_prefix' => $_GET['cat_prefix']]);
        }
        if (isset($_GET['cat_up']) && !isset($_POST['save_cat'])) {
            // move category up
            // Use prepared statements for safety
            $stmt1 = $dbh->prepare("UPDATE humo_photocat SET photocat_order = :temp_order WHERE photocat_order = :current_order");
            $stmt1->execute([
                ':temp_order' => 999,
                ':current_order' => $_GET['cat_up']
            ]);

            $stmt2 = $dbh->prepare("UPDATE humo_photocat SET photocat_order = :new_order WHERE photocat_order = :above_order");
            $stmt2->execute([
                ':new_order' => $_GET['cat_up'],
                ':above_order' => $_GET['cat_up'] - 1
            ]);

            $stmt3 = $dbh->prepare("UPDATE humo_photocat SET photocat_order = :final_order WHERE photocat_order = :temp_order");
            $stmt3->execute([
                ':final_order' => $_GET['cat_up'] - 1,
                ':temp_order' => 999
            ]);
        }
        if (isset($_GET['cat_down']) && !isset($_POST['save_cat'])) {
            // move category down
            // Use prepared statements for safety
            $stmt1 = $dbh->prepare("UPDATE humo_photocat SET photocat_order = :temp_order WHERE photocat_order = :current_order");
            $stmt1->execute([
                ':temp_order' => 999,
                ':current_order' => $_GET['cat_down']
            ]);

            $stmt2 = $dbh->prepare("UPDATE humo_photocat SET photocat_order = :new_order WHERE photocat_order = :below_order");
            $stmt2->execute([
                ':new_order' => $_GET['cat_down'],
                ':below_order' => $_GET['cat_down'] + 1
            ]);

            $stmt3 = $dbh->prepare("UPDATE humo_photocat SET photocat_order = :final_order WHERE photocat_order = :temp_order");
            $stmt3->execute([
                ':final_order' => $_GET['cat_down'] + 1,
                ':temp_order' => 999
            ]);
        }

        if (isset($_POST['save_cat'])) {  // the user decided to add a new category and/or save changes to names
            // save names of existing categories in case some were altered. There is at least always one name (for default category)

            //$qry = "SELECT * FROM humo_photocat GROUP BY photocat_prefix";
            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $qry = "SELECT photocat_prefix, photocat_order FROM humo_photocat GROUP BY photocat_prefix, photocat_order";
            $result = $dbh->query($qry);

            while ($resultDb = $result->fetch(PDO::FETCH_OBJ)) {
                if (isset($_POST[$resultDb->photocat_prefix])) {
                    if ($language_tree != "default") {
                        // only update names for the chosen language
                        $check_lang_stmt = $dbh->prepare("SELECT * FROM humo_photocat WHERE photocat_prefix = :prefix AND photocat_language = :language");
                        $check_lang_stmt->execute([
                            ':prefix' => $resultDb->photocat_prefix,
                            ':language' => $language_tree
                        ]);
                        if ($check_lang->rowCount() != 0) { // this language already has a name for this category - update it
                            $update_stmt = $dbh->prepare("UPDATE humo_photocat SET photocat_name = :cat_name WHERE photocat_prefix = :cat_prefix AND photocat_language = :cat_language");
                            $update_stmt->execute([
                                ':cat_name' => $_POST[$resultDb->photocat_prefix],
                                ':cat_prefix' => $resultDb->photocat_prefix,
                                ':cat_language' => $language_tree
                            ]);
                        } else {
                            // this language doesn't yet have a name for this category - create it
                            $insert_stmt = $dbh->prepare("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES (:cat_prefix, :cat_order, :cat_language, :cat_name)");
                            $insert_stmt->execute([
                                ':cat_prefix' => $resultDb->photocat_prefix,
                                ':cat_order' => $resultDb->photocat_order,
                                ':cat_language' => $language_tree,
                                ':cat_name' => $_POST[$resultDb->photocat_prefix]
                            ]);
                        }
                    } else {
                        // update entered names for all languages 
                        $check_default = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix = '" . $resultDb->photocat_prefix . "' AND photocat_language='default'");
                        if ($check_default->rowCount() != 0) {    // there is a default name for this language - update it
                            $update_stmt = $dbh->prepare("UPDATE humo_photocat SET photocat_name = :cat_name WHERE photocat_prefix = :cat_prefix AND photocat_language = 'default'");
                            $update_stmt->execute([
                                ':cat_name' => $_POST[$resultDb->photocat_prefix],
                                ':cat_prefix' => $resultDb->photocat_prefix
                            ]);
                        } else {
                            // no default name yet for this category - create it
                            $stmt_insert = $dbh->prepare("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES (:cat_prefix, :cat_order, 'default', :cat_name)");
                            $stmt_insert->execute([
                                ':cat_prefix' => $resultDb->photocat_prefix,
                                ':cat_order' => $resultDb->photocat_order,
                                ':cat_name' => $_POST[$resultDb->photocat_prefix]
                            ]);
                        }
                    }
                }
            }

            // save new category
            if (isset($_POST['new_cat_prefix']) and isset($_POST['new_cat_name'])) {
                if ($_POST['new_cat_prefix'] != "") {
                    $new_cat_prefix = $_POST['new_cat_prefix'];
                    $new_cat_name = $_POST['new_cat_name'];
                    $warning_prefix = '';
                    $warning_invalid_prefix = '';
                    if (preg_match('/^[a-z][a-z]_$/', $_POST['new_cat_prefix']) !== 1) {
                        $warning_invalid_prefix = __('Prefix has to be 2 letters and _');
                        $warning_prefix = $_POST['new_cat_prefix'];
                    } else {
                        $warning_exist_prefix = '';
                        $check_exist_stmt = $dbh->prepare("SELECT * FROM humo_photocat WHERE photocat_prefix = :cat_prefix");
                        $check_exist_stmt->execute([':cat_prefix' => $new_cat_prefix]);
                        $check_exist = $check_exist_stmt;
                        if ($check_exist->rowCount() == 0) {
                            if ($_POST['new_cat_name'] == "") {
                                $warning_noname = __('When creating a category you have to give it a name');
                                $warning_prefix = $_POST['new_cat_prefix'];
                            } else {
                                $highest_order = $dbh->query("SELECT MAX(photocat_order) AS maxorder FROM humo_photocat");
                                $orderDb = $highest_order->fetch(PDO::FETCH_ASSOC);
                                $order = $orderDb['maxorder'];
                                $order++;
                                $stmt_insert = $dbh->prepare("INSERT INTO humo_photocat (photocat_prefix, photocat_order, photocat_language, photocat_name) VALUES (:cat_prefix, :cat_order, :cat_language, :cat_name)");
                                $stmt_insert->execute([
                                    ':cat_prefix' => $new_cat_prefix,
                                    ':cat_order' => $order,
                                    ':cat_language' => $language_tree,
                                    ':cat_name' => $new_cat_name
                                ]);
                            }
                        } else {   // this category prefix already exists!
                            $warning_exist_prefix = __('A category with this prefix already exists!');
                            $warning_prefix = $_POST['new_cat_prefix'];
                        }
                    }
                }
            }
        }
        ?>

        <form method="post" action="index.php?page=thumbs" style="display : inline;">
            <input type="hidden" name="menu_tab" value="picture_categories">
            <input type="hidden" name="language_tree" value="<?= $language_tree; ?>">

            <div class="p-3 m-2 genealogy_search">

                <div class="row mb-2">
                    <div class="col-md-11">
                        <h3><?= __('Create categories for your photo albums'); ?></h3>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-11">
                        <li><?= __('Here you can create categories for all your photo albums.</li><li><b>A category will not be displayed in the photobook menu unless there is at least one picture for it.</b></li><li>Click "Default" to create one default name in all languages. Choose a language from the list to set a specific name for that language.<br><b>TIP:</b> First set an English name as default for all languages, then create specific names for those languages that you know. That way no tabs will display without a name in any language. In any case, setting a default name will not overwrite names for specific languages that you have already set.</li><li>The category prefix has to be made up of two letters and an underscore (like: <b>sp_</b> or <b>ws_</b>).</li><li>Pictures that you want to appear in a specific category have to be named with that prefix like: <b>sp_</b>John Smith.jpg</li><li>Pictures that you want to be displayed in the default photo category don\'t need a prefix.'); ?>
                        <li><?= __('A (sub)directory could also be a category. Example: category prefix = ab_, the directory name = ab.'); ?></li>
                    </div>
                </div>

                <table class="table">
                    <tr class="table-primary">
                        <td style="border-bottom:0px;"></td>
                        <td style="font-size:120%;border-bottom:0px;width:25%" white-space:nowrap;"><b><?= __('Category prefix'); ?></b></td>
                        <td style="font-size:120%;border-bottom:0px;width:60%"><b><?= __('Category name'); ?></b></td>
                    </tr>

                    <?php
                    $add = '';
                    if (isset($_POST['add_new_cat'])) {
                        $add = "&amp;add_new_cat=1";
                    }

                    // *** Language choice ***
                    $language_tree2 = $language_tree;
                    if ($language_tree == 'default') {
                        $language_tree2 = $selected_language;
                    }
                    include(__DIR__ . '/../../languages/' . $language_tree2 . '/language_data.php');
                    $select_top = '';
                    ?>

                    <tr>
                        <td style="border-top:0px"></td>
                        <td style="border-top:0px"></td>
                        <td style="border-top:0px;text-align:center">
                            <div class="row mb-2">
                                <div class="col-md-auto">
                                    <?= __('Language'); ?>:
                                </div>

                                <div class="col-md-auto">
                                    <?php include_once(__DIR__ . "/../../views/partial/select_language.php"); ?>
                                    <?php $language_path = 'index.php?page=thumbs&amp;menu_tab=picture_categories&amp;'; ?>
                                    <?= show_country_flags($language_tree2, '../', 'language_tree', $language_path); ?>
                                </div>

                                <div class="col-md-auto">
                                    <?= __('or'); ?>
                                    <a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;language_tree=default<?= $add; ?>"><?= __('Default'); ?></a>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <?php
                    // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
                    $qry = "SELECT photocat_prefix, photocat_order FROM humo_photocat GROUP BY photocat_prefix, photocat_order ORDER BY photocat_order";
                    $cat_result = $dbh->query($qry);
                    $number = 1;  // number on list

                    while ($catDb = $cat_result->fetch(PDO::FETCH_OBJ)) {
                        $stmt = $dbh->prepare("SELECT * FROM humo_photocat WHERE photocat_prefix = :prefix AND photocat_language = :language");
                        $stmt->execute([
                            ':prefix' => $catDb->photocat_prefix,
                            ':language' => $language_tree
                        ]);
                        $name = $stmt;
                        if ($name->rowCount()) {  // there is a name for this language
                            $nameDb = $name->fetch(PDO::FETCH_OBJ);
                            $catname = $nameDb->photocat_name;
                        } else {  // maybe a default is set
                            $name = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix='" . $catDb->photocat_prefix . "' AND photocat_language = 'default'");
                            if ($name->rowCount()) {  // there is a default name for this category
                                $nameDb = $name->fetch(PDO::FETCH_OBJ);
                                $catname = $nameDb->photocat_name;
                            } else {  // no name at all
                                $catname = '';
                            }
                        }

                        // arrows
                        $order_sequence = $dbh->query("SELECT MAX(photocat_order) AS maxorder, MIN(photocat_order) AS minorder FROM humo_photocat");
                        $orderDb = $order_sequence->fetch(PDO::FETCH_ASSOC);
                        $maxorder = $orderDb['maxorder'];
                        $minorder = $orderDb['minorder'];

                        $prefname = $catDb->photocat_prefix;
                        if ($catDb->photocat_prefix == 'none') {
                            $prefname = __('default - without prefix');
                        }  // display default in the display language, so it is clear to everyone
                    ?>
                        <tr>
                            <td>
                                <div style="width:25px;" class="d-inline-block">
                                    <?php
                                    if ($catDb->photocat_prefix != 'none') {
                                        echo '<a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_order=' . $catDb->photocat_order . '&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_drop=1"><img src="images/button_drop.png"></a>';
                                    }
                                    ?>
                                </div>

                                <div style="width:20px;" class="d-inline-block">
                                    <?php
                                    if ($catDb->photocat_order != $minorder) {
                                        echo '<a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_up=' . $catDb->photocat_order . '"><img src="images/arrow_up.gif"></a>';
                                    }
                                    ?>
                                </div>

                                <div style="width:20px;" class="d-inline-block">
                                    <?php
                                    if ($catDb->photocat_order != $maxorder) {
                                        echo '<a href="index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_prefix=' . $catDb->photocat_prefix . '&amp;cat_down=' . $catDb->photocat_order . '"><img src="images/arrow_down.gif"></a>';
                                    }
                                    ?>
                                </div>
                            </td>

                            <td style="white-space:nowrap;"><?= $prefname; ?></td>

                            <td><input type="text" name="<?= $catDb->photocat_prefix; ?>" value="<?= $catname; ?>" size="30" class="form-control form-control-sm"></td>
                        </tr>
                    <?php
                    }

                    $content = '';
                    if (isset($warning_prefix)) {
                        $content = $warning_prefix;
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td style="white-space:nowrap;"><input type="text" name="new_cat_prefix" value="<?= $content; ?>" size="6" class="form-control form-control-sm">
                            <?php if (isset($warning_invalid_prefix)) { ?>
                                <br><span style="color:red"><?= $warning_invalid_prefix; ?></span>
                            <?php
                            }
                            if (isset($warning_exist_prefix)) {
                            ?>
                                <br><span style="color:red"><?= $warning_exist_prefix; ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <input type="text" name="new_cat_name" value="" size="30" class="form-control form-control-sm">
                            <?php if (isset($warning_noname)) { ?>
                                <br><span style="color:red"><?= $warning_noname; ?></span>
                            <?php } ?>
                        </td>
                    </tr>

                    <?php if (isset($_GET['cat_drop']) && $_GET['cat_drop'] == 1) { ?>
                        <tr>
                            <td colspan="3" style="color:red;font-weight:bold;font-size:120%">
                                <?= __('Do you really want to delete category:'); ?>&nbsp;<?= $_GET['cat_prefix']; ?>&nbsp;?
                                &nbsp;&nbsp;&nbsp;<input type="button" style="color:red;font-weight:bold" onclick="location.href='index.php?page=thumbs&amp;menu_tab=picture_categories&amp;cat_order=<?= $_GET['cat_order']; ?>&amp;cat_prefix=<?= $_GET['cat_prefix']; ?>&amp;cat_drop2=1';" value="<?= __('Yes'); ?>">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" style="color:green;font-weight:bold" onclick="location.href='index.php?page=thumbs&amp;menu_tab=picture_categories';" value="<?= __('No'); ?>">
                            </td>
                        </tr>
                    <?php } ?>
                </table><br>
                <div style="margin-left:auto; margin-right:auto; text-align:center;"><input type="submit" name="save_cat" value="<?= __('Save changes'); ?>" class="btn btn-sm btn-success"></div>
            </div>

        </form>
        <?php
    }

    // *** Change filename ***
    if (isset($_POST['filename'])) {
        $picture_path_old = $_POST['picture_path'];
        $picture_path_new = $_POST['picture_path'];
        // *** If filename has a category AND a sub category directory exists, use it ***
        if (substr($_POST['filename'], 0, 2) !== substr($_POST['filename_old'], 0, 2) && ($_POST['filename'][2] == '_' || $_POST['filename_old'][2] == '_')) { // we only have to do this if something changed in a prefix
            if ($_POST['filename'][2] == '_') {
                if (preg_match('!.+/[a-z][a-z]/$!', $picture_path_new) == 1) {   // original path had subfolder
                    if (is_dir(substr($picture_path_new, 0, -3) . substr($_POST['filename'], 0, 2))) {   // subtract subfolder and add new subfolder
                        $picture_path_new = substr($picture_path_new, 0, -3) . substr($_POST['filename'], 0, 2) . "/"; // move from subfolder to other subfolder
                    } else {
                        $picture_path_new = substr($picture_path_new, 0, -3); // move file with prefix that has no folder to main folder
                    }
                } elseif (is_dir($_POST['picture_path'] . substr($_POST['filename'], 0, 2))) {
                    $picture_path_new .= substr($_POST['filename'], 0, 2) . '/';   // move from main folder to subfolder
                }
            } elseif (preg_match('!.+/[a-z][a-z]/$!', $picture_path_new) == 1) {    // regular file, just check if original path had subfolder
                $picture_path_new = substr($picture_path_new, 0, -3);  // move from subfolder to main folder
            }
        }
        // remove thumb old naming system
        if (file_exists($picture_path_old . 'thumb_' . $_POST['filename_old'])) {
            unlink($picture_path_old . 'thumb_' . $_POST['filename_old']);
        }
        // remove old thumb new system
        if (file_exists($picture_path_old . 'thumb_' . $_POST['filename_old'] . '.jpg')) {
            unlink($picture_path_old . 'thumb_' . $_POST['filename_old'] . '.jpg');
        }
        // rename and create new thumbnail       
        if (file_exists($picture_path_old . $_POST['filename_old'])) {
            rename($picture_path_old . $_POST['filename_old'], $picture_path_new . $_POST['filename']);
            echo '<b>' . __('Changed filename:') . ' </b>' . $picture_path_old .  $_POST['filename_old'] . ' <b>' . __('into filename:') . '</b> ' . $picture_path_new .  $_POST['filename'] . '<br>';
            if ($resizePicture->check_media_type($picture_path_new, $_POST['filename']) && $resizePicture->create_thumbnail($picture_path_new, $_POST['filename'])) {
                echo '<b>' . __('Changed filename:') . ' ' . __('into filename:') . '</b> ' . $picture_path_new . 'thumb_' . $_POST['filename'] . '.jpg<br>';
            }
        }

        $sql = "UPDATE humo_events SET event_event = :new_event WHERE event_event = :old_event";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':new_event' => $_POST['filename'],
            ':old_event' => $_POST['filename_old']
        ]);
    }


    // *** Create thumbnails ***
    $counter = 0;
    if (isset($_POST["thumbnail"]) || isset($_POST['change_filename'])) {
        $pict_path = $data2Db->tree_pict_path;
        if (substr($pict_path, 0, 1) === '|') {
            $pict_path = 'media/';
        }

        //$selected_picture_folder=$prefx.$pict_path;
        $array_picture_folder[] = $prefx . $pict_path;

        // *** Extra safety check if folder exists ***
        //if (file_exists($selected_picture_folder)){
        if (file_exists($array_picture_folder[0])) {
            // *** Get all subdirectories ***
            function get_dirs($prefx, $path)
            {
                global $array_picture_folder;
                $ignore = array('cms', 'slideshow', 'thumbs', '.', '..');
                $dh = opendir($prefx . $path);
                while (false !== ($filename = readdir($dh))) {
                    if (!in_array($filename, $ignore) && is_dir($prefx . $path . $filename)) {
                        $array_picture_folder[] = $prefx . $path . $filename . '/';
                        get_dirs($prefx, $path . $filename . '/');
                    }
                }
                closedir($dh);
            }

            get_dirs($prefx, $pict_path);

            foreach ($array_picture_folder as $selected_picture_folder) {
                echo '<br style="clear: both">';
                echo '<h3>' . $selected_picture_folder . '</h3>';

                $files = preg_grep('/^([^.])/', scandir($selected_picture_folder));
                foreach ($files as $filename) {

                    if (
                        substr($filename, 0, 5) !== 'thumb' &&
                        isset($_POST["thumbnail"]) &&
                        !is_dir($selected_picture_folder . $filename)  &&
                        $resizePicture->check_media_type($selected_picture_folder, $filename)
                    ) {

                        if (
                            !is_file($selected_picture_folder . '.' . $filename . '.no_thumb') && // don't create thumb on corrupt file
                            empty($showMedia->thumbnail_exists($selected_picture_folder, $filename))
                        ) {    // don't create thumb if one exists
                            $resizePicture->create_thumbnail($selected_picture_folder, $filename);
                        }
                    }

                    // *** Show thumbnails ***
                    if (
                        substr($filename, 0, 5) !== 'thumb' &&
                        $resizePicture->check_media_type($selected_picture_folder, $filename) &&
                        !is_dir($selected_picture_folder . $filename)
                    ) {
        ?>
                        <div class="photobook">
                            <?= $showMedia->print_thumbnail($selected_picture_folder, $filename); ?>
                            <?php
                            // *** Show name of connected persons ***
                            $picture_text = '';
                            $sql = "SELECT * FROM humo_events WHERE event_tree_id = :tree_id
                                AND event_connect_kind = 'person' AND event_kind = 'picture'
                                AND LOWER(event_event) = :filename";
                            $afbqry = $dbh->prepare($sql);
                            $afbqry->execute([
                                ':tree_id' => $tree_id,
                                ':filename' => strtolower($filename)
                            ]);
                            $picture_privacy = false;
                            while ($afbDb = $afbqry->fetch(PDO::FETCH_OBJ)) {
                                $db_functions->set_tree_id($tree_id);
                                $personDb = $db_functions->get_person($afbDb->event_connect_id);
                                $privacy = $personPrivacy->get_privacy($personDb);
                                $name = $personName->get_person_name($personDb, $privacy);

                                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                                $url = $personLink->get_person_link($personDb, '../');
                                $picture_text .= '<br><a href="' . $url . '">' . $name["standard_name"] . '</a><br>';
                            }
                            echo $picture_text;

                            if (isset($_POST['change_filename'])) {
                            ?>
                                <form method="POST" action="index.php">
                                    <input type="hidden" name="page" value="thumbs">
                                    <input type="hidden" name="menu_tab" value="picture_show">
                                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                                    <input type="hidden" name="picture_path" value="<?= $selected_picture_folder; ?>">
                                    <input type="hidden" name="filename_old" value="<?= $filename; ?>">
                                    <input type="text" name="filename" value="<?= $filename; ?>" size="20">
                                    <input type="submit" name="change_filename" value="<?= __('Change filename'); ?>">
                                </form>
                            <?php } else { ?>
                                <div class="photobooktext"><?= $filename; ?></div>
                            <?php } ?>
                        </div>
    <?php
                    }
                }
            }
        }
    }
    ?>
</div>