<?php

namespace ActiveValue\Shopguardians\Controller\Api;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\Facts\Facts;

/**
 * Class SecurityController
 * @package ActiveValue\Shopguardians\Controller\Api
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class SecurityController extends BaseController
{

    /**
     * Returns server and shop software version numbers
     *
     * @return array
     * @throws \Exception
     */
    public function getVersions()
    {

        $versions = [
            'shop'                  => [
                'version' =>            Registry::get(ShopVersion::class)->getVersion(),
                'edition' =>            Registry::get(Facts::class)->getEdition()
            ],

            'server' => [
                'php'                   => PHP_VERSION_ID,
                'software'              => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null,
                'signature'             => isset($_SERVER['SERVER_SIGNATURE']) ? $_SERVER['SERVER_SIGNATURE'] : null,

                'database' => [
                    'type' => Registry::getConfig()->getConfigParam('dbType')

                ]
            ],
        ];

        if (function_exists('apache_get_version')) {
            $versions['server']['apache'] = apache_get_version();
        }

        try {
            $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
            $versions['server']['database']['version']  = $oDb->getOne('SELECT VERSION()');

        } catch (\Exception $e) {

        }

        $this->renderJson($versions);
    }
}