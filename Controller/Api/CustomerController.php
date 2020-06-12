<?php

namespace ActiveValue\Shopguardians\Controller\Api;

use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;

/**
 * Class CustomerController
 * @package ActiveValue\Shopguardians\Controller\Api
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class CustomerController extends BaseController
{
    /**
     * Returns Key Performance Indicators for orders
     * @TODO: Subshop support
     *
     * @return array
     * @throws SystemComponentException
     */
    public function getKPI()
    {
        // Returns number of orders, revenue, avg cart value e.g.
        $oUser = oxNew(User::class);

        $kpi = [
            'customers_total'       => $oUser->getUserCountTotal(),
            'customers_today'       => $oUser->getNewUserCount(),
            'guests_total'          => $oUser->getGuestUserCount(),
            'guests_today'          => $oUser->getNewGuestUserCount(),
            'newsletter_total'      => $oUser->getNewsletterSubscriberCount(),
            'newsletter_today'      => $oUser->getNewsletterSubscriberCountToday(),
        ];

        return $this->renderJson($kpi);


    }
}