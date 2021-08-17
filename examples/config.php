<?php
/**
 * @author        Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @copyright (c) 2021 by HubCulture Ltd.
 */

include __DIR__ . '/../vendor/autoload.php';

$config = array(
    'base_url' => 'https://api.fireblocks.io',
    'verify' => false,
    'private_key_file' => __DIR__ . '/../fireblocks_secret.key',
    'api_key' => '',
    'debug' => true,
);
