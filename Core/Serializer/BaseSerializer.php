<?php

namespace ActiveValue\Shopguardians\Core\Serializer;

use OxidEsales\Eshop\Core\Registry;

/**
 * Class BaseSerializer
 * @package ActiveValue\Shopguardians\Core\Serializer
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
abstract class BaseSerializer
{
    /**
     * Returns absolute url to object
     *
     * @param $object
     * @return string|null
     */
    public static function getDetailUrl($object)
    {
        if (empty($object['seoLink'])) return null;
        $sFullUrl =  Registry::getConfig()->getShopUrl() . $object['seoLink'];

        return \OxidEsales\Eshop\Core\Registry::getUtilsUrl()->processSeoUrl($sFullUrl);
    }
}