<?php

namespace ActiveValue\Shopguardians\Controller\Api;

use ActiveValue\Shopguardians\Core\OrderHeuristic\OrderHeuristic;
use ActiveValue\Shopguardians\Core\ResponseHelper;
use ActiveValue\Shopguardians\Repositories\OrderRepository;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class OrderController
 * @package ActiveValue\Shopguardians\Controller\Api
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class OrderController extends BaseController
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
        $oOrder = oxNew(Order::class);

        /** @var Order $oOrder */

        $kpi = [
            'orders_total' => $oOrder->getOrderCnt(),
            'orders_today' => $oOrder->getOrderCnt(true),
            'revenue_total' => $oOrder->getOrderSum(),
            'revenue_today' => $oOrder->getOrderSum(true)
        ];

        return $this->renderJson($kpi);
    }

    /**
     * Returns the seed data to setup order heuristic check
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getOrderHeuristicSeedData()
    {
        $orderHeuristic = oxNew(OrderHeuristic::class);

        $paymentMethods = $orderHeuristic->getRelevantPaymentMethods();

        return $this->renderJson([
            'usualWorkingHours' => $orderHeuristic->getUsualWorkingHours(),
            'averageOrderDistancesByWeekday' => $orderHeuristic->getOrderDistancesByWeekday(),
            'averageOrderDistancesByWeekdayAndPaymentMethods' => $orderHeuristic->getOrderDistancesByWeekdayAndPaymentMethods($paymentMethods)
        ]);
    }

    /**
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getNewestOrderDate()
    {
        return $this->renderJson(OrderRepository::getNewestOrderDateTime());
    }

    /**
     * Returns newest order date for given payment method
     * ?paymentMethod=[oxid]
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getNewestOrderDateForPaymentMethod()
    {
        $paymentMethod = Registry::getRequest()->getRequestEscapedParameter('paymentMethod') ?? null;
        if (empty($paymentMethod)) {
            ResponseHelper::internalServerError('Parameter paymentMethod must not be empty');
        }

        return $this->renderJson(OrderRepository::getNewestOrderDateTimeForPaymentMethod($paymentMethod));
    }

    /**
     * Returns an array of payment method + last time used
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function getNewestOrderDatesByPaymentMethods()
    {
        return $this->renderJson(OrderRepository::getNewestOrderDatesByPaymentMethods());
    }


}