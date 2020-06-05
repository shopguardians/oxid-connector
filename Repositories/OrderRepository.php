<?php
namespace ActiveValue\Shopguardians\Repositories;

use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Class OrderRepository
 * @package ActiveValue\Shopguardians\Repositories
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class OrderRepository
{
    /**
     * Returns the average of orders per hour
     *
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public static function getAverageOrdersPerHour(): int
    {
        $query = "SELECT AVG(`count`)
FROM (
SELECT COUNT(OXID) AS `count`
FROM oxorder 
      GROUP BY HOUR(OXORDERDATE)
    ) nested;";

        $avgCount = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getOne($query, [1]);
        if (!$avgCount) {
            return 0;
        }

        return intval($avgCount);
    }

    /**
     * Returns the distribution of orders for each hour including standard deviation abs and percent
     *
     * @param $avgOrdersPerHour
     * @return array
     */
    public static function getStandardDeviationPerHour($avgOrdersPerHour)
    {
        $avgOrdersPerHour = intval($avgOrdersPerHour);

        $query = "SELECT COUNT(OXID) AS count, ($avgOrdersPerHour-COUNT(OXID)) AS avgDiff, ( (($avgOrdersPerHour-COUNT(OXID))/$avgOrdersPerHour) *100 ) AS avgDiffPercent, HOUR(OXORDERDATE) FROM oxorder 
GROUP BY HOUR(OXORDERDATE)";

        $deviationTable = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($query);

        return $deviationTable;
    }

    /**
     * Get average distance in minutes between to orders
     * Can optionally specify an OXPAYMENTTYPE
     *
     * @param $fromDate
     * @param $toDate
     * @param null $paymentMethod (one of o.OXPAYMENTTYPE / oxpayments.OXID)
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public static function getAverageMinutesBetweenOrdersInDateRange($fromDate, $toDate, $paymentMethod=null)
    {
        $query = "SELECT TIMESTAMPDIFF(MINUTE, MIN(oxorderdate), MAX(oxorderdate) ) 
       /
       (COUNT(DISTINCT(oxorderdate)) -1) 
FROM oxorder
WHERE OXORDERDATE >= ? AND OXORDERDATE <= ?";

        $params = [$fromDate, $toDate];

        if ($paymentMethod !== null) {
            $query .= ' AND OXPAYMENTTYPE = ?';
            $params[] = $paymentMethod;
        }

        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getOne($query, $params);
    }

    /**
     * Returns datetime of the newest order
     *
     * @return
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public static function getNewestOrderDateTime()
    {
        $query = "SELECT MAX(OXORDERDATE) FROM oxorder";

        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getOne($query) ?? null;
    }

    /**
     * Returns datetime of the newest order for given payment method
     *
     * @param $paymentMethod
     * @return false|string|null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public static function getNewestOrderDateTimeForPaymentMethod($paymentMethod)
    {
        $query = "SELECT MAX(OXORDERDATE) FROM oxorder WHERE oxpaymenttype = ?";

        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getOne($query, [$paymentMethod]) ?? null;
    }

    /**
     * Returns an array of paymentmethod + last used date
     *
     * @return array|null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function getNewestOrderDatesByPaymentMethods()
    {
        $query = "SELECT oxpaymenttype AS paymentMethod, MAX(OXORDERDATE) AS orderDate FROM oxorder 
GROUP BY oxpaymenttype";

        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($query) ?? null;
    }

    /**
     * Returns an array of payment method usage counts in last XXX days
     *
     * @param $lastDays Number of days
     * @return array|null
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function getActivelyUsedPaymentMethodsDaysAgo($lastDays)
    {
        $query = "SELECT o.oxpaymenttype AS paymenttype,p.OXDESC AS description from oxorder o

left join oxpayments p ON p.OXID=o.oxpaymenttype
where p.OXACTIVE = 1 AND o.OXORDERDATE >= NOW() - INTERVAL 120 DAY
group by o.oxpaymenttype";

        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($query, [$lastDays]) ?? null;
    }


}