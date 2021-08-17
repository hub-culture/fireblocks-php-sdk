<?php
/**
 * @author        Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @copyright (c) 2021 by HubCulture Ltd.
 */

namespace Hub\FireBlocksSdk;

/**
 * Class AccountService
 * @package Hub\FireBlocksSdk
 */
class AccountService extends Service
{
    /**
     * Retrieves all Vault Accounts in the authenticated workspace.
     *
     * @return array
     */
    public function getAccounts()
    {
        return $this->get('/v1/vault/accounts');
    }
}
