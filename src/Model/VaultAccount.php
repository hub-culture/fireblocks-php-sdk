<?php
/**
 * @author        Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @copyright (c) 2021 by HubCulture Ltd.
 */

namespace Hub\FireBlocksSdk\Model;

/**
 * Class VaultAccount
 * @package Hub\FireBlocksSdk\Model
 */
class VaultAccount
{
    /**
     * The ID of the Vault Account
     *
     * @var string
     */
    private $id;

    /**
     * Name of the Vault Account
     *
     * @var string
     */
    private $name;

    /**
     * Specifies whether this vault account is visible in the web console or not
     *
     * @var bool
     */
    private $hiddenOnUI;

    /**
     * Specifies whether this account's Ethereum address is auto fueled by the Gas Station or not
     *
     * @var bool
     */
    private $autoFuel;

    /**
     * List of assets under this Vault Account
     *
     * @var array
     */
    private $assets;

    /**
     * VaultAccount constructor.
     *
     * @param string $id         The ID of the Vault Account
     * @param string $name       Name of the Vault Account
     * @param bool   $hiddenOnUI Specifies whether this vault account is visible in the web console or not
     * @param bool   $autoFuel   Specifies whether this account's Ethereum address is auto fueled by the Gas Station or
     *                           not
     * @param array  $assets     List of assets under this Vault Account
     */
    public function __construct($id, $name, $hiddenOnUI = false, $autoFuel = false, array $assets = array())
    {
        $this->id = $id;
        $this->name = $name;
        $this->hiddenOnUI = $hiddenOnUI;
        $this->autoFuel = $autoFuel;
        $this->assets = $assets;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isHiddenOnUI()
    {
        return $this->hiddenOnUI;
    }

    /**
     * @param bool $hiddenOnUI
     */
    public function setHiddenOnUI($hiddenOnUI)
    {
        $this->hiddenOnUI = $hiddenOnUI;
    }

    /**
     * @return bool
     */
    public function isAutoFuel()
    {
        return $this->autoFuel;
    }

    /**
     * @param bool $autoFuel
     */
    public function setAutoFuel($autoFuel)
    {
        $this->autoFuel = $autoFuel;
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param array $assets
     */
    public function setAssets(array $assets)
    {
        $this->assets = $assets;
    }
}
