<?php
/**
 * @author        Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @copyright (c) 2021 by HubCulture Ltd.
 */

include __DIR__ . '/config.php';

use Hub\FireBlocksSdk\AccountService;

$service = new AccountService($config);
$response = $service->getAccounts();
var_dump($response);
$response = $service->createNewVaultAccount("testaccount", true);
var_dump($response);
