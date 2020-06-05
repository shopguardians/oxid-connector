<?php

namespace ActiveValue\Shopguardians\Core;

use OxidEsales\Eshop\Core\Registry;

class Events
{
    /**
     * Module activation script.
     */
    public static function onActivate()
    {
        self::setRandomApiKey();
    }

    /**
     * Module deactivation script.
     */
    public static function onDeactivate()
    {

    }

    /**
     * Get module setting value.
     *
     * @param string  $sModuleSettingName Module setting parameter name (key).
     * @param boolean $blUseModulePrefix  If True - adds the module settings prefix, if False - not.
     *
     * @return mixed
     */
    public static function getSetting($sModuleSettingName, $blUseModulePrefix = true)
    {
        if ($blUseModulePrefix) {
            $sModuleSettingName = 'AVSHOPGUARDIANS_' . (string) $sModuleSettingName;
        }

        return Registry::getConfig()->getConfigParam((string) $sModuleSettingName);
    }

    /**
     * Sets random key in module configuration
     *
     * TODO: Find alternative method for systems not having openSSL extension installed
     */
    public static function setRandomApiKey()
    {
        if (!empty(self::getSetting('API_KEY'))) {
            return;
        }

        $randomKey = bin2hex(openssl_random_pseudo_bytes(16));

        Registry::getConfig()->saveShopConfVar('str', 'AVSHOPGUARDIANS_API_KEY', $randomKey, null, 'module:AvShopguardians');
    }
}