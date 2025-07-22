<?php

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// get IPv4 address
$ip = gethostbyname(get_host());
// get IPv6 address
$ipv6 = dns_get_record(get_host(), DNS_AAAA);

// Function to try every way to resolve domain IP.
// Is more accurate than good old: gethostbyname($_SERVER['SERVER_NAME']) or gethostbyname(gethostname()) ;
function get_host()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && ($host = $_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $elements = explode(',', $host);
        $host = trim(end($elements));
    } elseif (!$host = $_SERVER['HTTP_HOST']) {
        if (!$host = $_SERVER['SERVER_NAME']) {
            $host = empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'];
        }
    }
    // Remove port number from host
    $host = preg_replace('/:\d+$/', '', $host);
    return trim($host);
}
?>

<form action="index.php?page=maps" method="post">
    <div class="p-3 m-2 genealogy_search container-md">
        <div class="row mb-1 p-2 bg-primary-subtle"><?= __('World map administration'); ?></div>

        <div class="row mb-2">
            <div class="col-md-12">
                <input type="radio" name="use_world_map" value="Google" <?= $maps['use_world_map'] == 'Google' ? ' checked' : '' ?> onChange="this.form.submit();" class="form-check-input">
                <label class="form-check-label" for="google_maps"><?= __('Use Google Maps'); ?></label>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-12">
                <input type="radio" name="use_world_map" value="OpenStreetMap" <?= $maps['use_world_map'] == 'OpenStreetMap' ? ' checked' : '' ?> onChange="this.form.submit();" class="form-check-input">
                <label class="form-check-label" for="google_maps"><?= __('Use OpenStreetMap'); ?></label>
            </div>
        </div>
    </div>
</form>

<div class="p-3 m-2 genealogy_search container-md">
    <div class="row mb-1 p-2 bg-primary-subtle">
        <?php if ($maps['use_world_map'] == 'Google') {; ?>
            <?= __('Google Maps API Key'); ?>
        <?php } else { ?>
            <?= __('OpenStreetMap API Key'); ?>
        <?php } ?>
    </div>

    <div class="row mb-2">
        <div class="col-md-12">
            <?php if ($maps['use_world_map'] == 'Google') {; ?>

                <?php printf(__('To use the Google maps options, you need a Google account. Go to: %1$s create API key %2$s and follow the instructions.'), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">', '</a>'); ?><br>
                <?= __('The API key will work without restrictions but for security reasons set restrictions when API key is generated.'); ?><br><br>

                <ul>
                    <li>
                        <?= __('Set restriction to <strong>"HTTP referrers"</strong> and enter your website domain name.'); ?><br>
                        <?= __('If your domain looks like \'www.mydomain.com\', enter:'); ?><strong> *.mydomain.com/*</strong><br><?= __('If your domain looks like \'mydomain.com\', enter:'); ?> <strong>mydomain.com/*</strong>
                    </li><br>

                    <li><?= __('Or: set restriction to <strong>"IP addresses"</strong> and enter your server IP.'); ?> <?= __('Not your computer\'s IP!'); ?><br>
                        <?= __('Your server IP would seem to be:'); ?> <strong><?= $ip; ?></strong><br>
                        <?php
                        if (isset($ipv6[0]['ipv6'])) {
                            echo __('Your server also has an IPv6 address. If the above IP doesn\'t work, try the IPv6 which would seem to be:') . " <strong>" . $ipv6[0]['ipv6'] . "</strong><br>";
                        }
                        ?>
                        <?= __('If this doesn\'t work, contact your provider and try to obtain the proper IP address from them.'); ?><br>
                    </li>
                </ul>

            <?php } ?>

            <!-- OpenStreetMap -->
            <?php if ($maps['use_world_map'] == 'OpenStreetMap') {; ?>
                <?= __('To use OpenStreetMap we need geolocation data of all places. Go to <a href="https://geokeo.com" target="_blank">https://geokeo.com</a> and create an account to get the API key.'); ?><br>
            <?php } ?>
        </div>
    </div>

    <?php if ($maps['use_world_map'] == 'Google') {; ?>
        <form action="index.php?page=maps" method="post">
            <div class="row mb-2">
                <div class="col-md-auto">
                    <?= __('API key'); ?>
                </div>
                <div class="col-md-4">
                    <input type="text" id="api_1" name="api_1" value="<?= $maps['google_api1']; ?>" size="40" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <input type="submit" value="<?= __('Save'); ?>" name="api_save" class="btn btn-sm btn-secondary">
                </div>
            </div>
        </form>

        <?php
        /*
        <form action="index.php?page=maps" method="post">
            <div class="row mb-2">
                <div class="col-md-4">
                    <?= __('API key') . " 2 (restriction: <strong>IP addresses</strong>)"; ?>
                </div>
                <div class="col-md-4">
                    <input type="text" id="api_2" name="api_2" value="<?= $maps['google_api2']; ?>" size="40" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <input type="submit" value="<?= __('Save'); ?>" name="api2_save" class="btn btn-sm btn-secondary">
                </div>
            </div>
        </form>
        */
        ?>

    <?php } ?>

    <?php if ($maps['use_world_map'] == 'OpenStreetMap') {; ?>
        <form action="index.php?page=maps" method="post">
            <div class="row mb-2">
                <div class="col-md-auto">
                    <?= __('API key') . '  Geokeo'; ?>
                </div>
                <div class="col-md-4">
                    <input type="text" id="api_geokeo" name="api_geokeo" value="<?= $maps['geokeo_api']; ?>" size="40" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <input type="submit" value="<?= __('Save'); ?>" name="api_save" class="btn btn-sm btn-secondary">
                </div>
            </div>
        </form>
    <?php } ?>
</div>