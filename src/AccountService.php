<?php
/**
 * @author        Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @copyright (c) 2021 by HubCulture Ltd.
 */

namespace Hub\FireBlocksSdk;

use Hub\FireBlocksSdk\Model\VaultAccount;

/**
 * Class AccountService
 * @package Hub\FireBlocksSdk
 */
class AccountService extends Service
{
    /**
     * Retrieves all Vault Accounts in the authenticated workspace.
     *
     * @return array array of VaultAccounts.
     */
    public function getAccounts()
    {
        return $this->get('/v1/vault/accounts');
    }

    /**
     * Creates a new Vault Account.
     *
     * @param string $accountName   The name of the new account (this can be renamed later)
     *
     * @param bool   $hiddenOnUI    [optional] Should be set to true if you wish this account will not appear in the
     *                              web console, false by default
     * @param string $customerRefId [optional] The ID for AML providers to associate the owner of funds with
     *                              transactions
     * @param bool   $autoFuel      [optional] In case the Gas Station service is enabled on your workspace, this flag
     *                              needs to be set to "true" if you wish to add this account's Ethereum address to be
     *                              monitored and fueled upon detected deposits of ERC20 tokens
     *
     * @return VaultAccount
     */
    public function createNewVaultAccount($accountName, $hiddenOnUI = false, $customerRefId = '', $autoFuel = false)
    {
        $payload = [
            'name' => $accountName,
            'customerRefId' => $customerRefId,
            'hiddenOnUI' => boolval($hiddenOnUI) === true,
            'autoFuel' => boolval($autoFuel) === true,
        ];
        $response = $this->postJson('/v1/vault/accounts', $payload);
        if (empty($response['id'])) {
            $this->log("createNewVaultAccount() : failed to create a new vault account");
            return null;
        }

        return new VaultAccount($response['id'], $response['name'], $response['hiddenOnUI']);
    }
}
