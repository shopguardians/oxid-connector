<?php

namespace ActiveValue\Shopguardians\Controller\Api;

use ActiveValue\Shopguardians\Core\Events;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class DiscoveryController
 * @package ActiveValue\Shopguardians\Controller\Api
 *
 * On project addition or on request seed backend with information about shop and plugin
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class DiscoveryController extends BaseController
{
    /**
     * Get initial data for adding shop to shopguardians
     */
    public function getInit()
    {
        $pluginVersion = Events::getModuleVersion();

        $languages =  Events::getActiveLanguages();

        $this->renderJson([
            'pluginVersion' => $pluginVersion,
            'languages' => $languages
        ]);
    }
}