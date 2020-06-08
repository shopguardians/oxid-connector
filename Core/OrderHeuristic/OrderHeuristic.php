<?php

namespace ActiveValue\Shopguardians\Core\OrderHeuristic;

use ActiveValue\Shopguardians\Core\Events;
use ActiveValue\Shopguardians\Repositories\OrderRepository;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

/**
 * Class OrderHeuristic
 * @package ActiveValue\Shopguardians\Core\OrderHeuristic
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class OrderHeuristic
{
    /**
     * If standard deviation is greater than this percentage,
     * will do not count this as typical working hour
     */
    protected $standardDeviationTresholdPercent;

    /**
     * If there is no order in [avgMinutesBetweenOrders * this factor] minutes, we will raise an alert
     */
    protected $alertMinutesSafetyBufferFactor;

    /**
     * In case there is not a single order (on a fresh shop) we dont' have a distance,
     * we will need to re-seed later and start with that value
     */
    protected $averageOrderDistanceMinutesFallback = 120;

    /**
     * There should be an order in at least (this) days
     * so a payment method will be considered as actively used
     *
     * @var int
     */
    protected $paymentMethodActivitySpanDays = 120;

    /**
     * Cached first day of last month
     *
     * @var string
     */
    protected $startDayLastMonth;

    /**
     * Cached last day of last month
     *
     * @var string
     */
    protected $endDayLastMonth;

    /**
     * Load config values
     *
     * OrderHeuristic constructor.
     */
    public function __construct()
    {
        $this->standardDeviationTresholdPercent = Events::getSetting('OHS_DEVIATION_TRESHOLD') ?? 50;
        $this->alertMinutesSafetyBufferFactor   = Events::getSetting('OHS_SAFETY_BUFFER_FACTOR') ?? 3;
        $this->paymentMethodActivitySpanDays    = Events::getSetting('OHS_PAYMENTMETHOD_ACTIVITY_DAYS') ?? 120;


        $this->startDayLastMonth  = (new \DateTime( 'first day of last month' ))->format('Y-m-d');
        $this->endDayLastMonth    = (new \DateTime( 'last day of last month' ))->format('Y-m-d');
    }

    /**
     * Returns an array of hours in which most orders are happening
     *
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getUsualWorkingHours()
    {
        $avgOrdersPerHour   = OrderRepository::getAverageOrdersPerHour();
        $deviationTable     = OrderRepository::getStandardDeviationPerHour($avgOrdersPerHour);

        $workingHours = [];

        foreach ($deviationTable as $key=>$row) {
            if ($row['avgDiffPercent'] <= $this->standardDeviationTresholdPercent) {
                $workingHours[] = $row['HOUR(OXORDERDATE)'];
            }
        }

        return $workingHours;
    }

    /**
     * Returns the average distance between orders in minutes
     * for the last month
     *
     * @return false|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getAverageMinutesBetweenOrdersByWeekday()
    {
        return OrderRepository::getAvgMinutesBetweenOrdersInDateRangeByWeekday($this->startDayLastMonth, $this->endDayLastMonth);
    }

    /**
     * Returns the average order distance for a single payment method in minutes
     * for the last month
     *
     * @param $paymentType
     * @return false|int|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getAverageMinutesBetweenOrdersForPaymentTypeByWeekday($paymentType)
    {
        return OrderRepository::getAvgMinutesBetweenOrdersInDateRangeByWeekday($this->startDayLastMonth, $this->endDayLastMonth, $paymentType) ?? null;
    }

    /**
     * Returns the assumed timerange in minutes where a order should happen
     * otherwise alert would be raised
     *
     * @return false|float[]
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getOrderDistancesByWeekday()
    {
        $averagesByWeekday = $this->getAverageMinutesBetweenOrdersByWeekday();

        $distances = [];

        foreach ($averagesByWeekday as $oneAverage) {

            $oneAverage['avgMinutes'] = $oneAverage['avgMinutes'] > 0 ? $oneAverage['avgMinutes'] : $this->averageOrderDistanceMinutesFallback;

            $distances[] = [
                'weekday' => (int) $oneAverage['weekdayNumber'],
                'tresholdMinutes' => round($oneAverage['avgMinutes'] * $this->alertMinutesSafetyBufferFactor),
                'actualMinutes' => round($oneAverage['avgMinutes'])
            ];
        }

        return $distances;
    }

    /**
     * Returns the assumed timerange in minutes where a order should happen with the selected
     * payment method, otherwise alert would be rised
     *
     * e.g. credt card orders are assumed every 20 mins
     *
     * Returns array of key=paymentmethod, value=mins
     *
     * @param $paymentMethods
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getOrderDistancesByWeekdayAndPaymentMethods($paymentMethods)
    {
        $distances = [];

        if (empty($paymentMethods)) {
            return $distances;
        }

        foreach ($paymentMethods as $paymentMethod) {

            $averagesByWeekday = $this->getAverageMinutesBetweenOrdersForPaymentTypeByWeekday($paymentMethod['paymenttype']);

            foreach ($averagesByWeekday as &$oneAverage) {

                $oneAverage['avgMinutes'] = $oneAverage['avgMinutes'] > 0 ? $oneAverage['avgMinutes'] : $this->averageOrderDistanceMinutesFallback;

                $oneAverage['actualMinutes']    = round($oneAverage['avgMinutes']);
                $oneAverage['tresholdMinutes']  = round($oneAverage['avgMinutes'] * $this->alertMinutesSafetyBufferFactor);
                unset($oneAverage['avgMinutes']);
            }

            $distances[] = [
                'paymenttype' => $paymentMethod['paymenttype'],
                'description' => $paymentMethod['description'],
                'byWeekdays' => $averagesByWeekday

            ];
        }

        return $distances;
    }

    /**
     * Returns actively used payment methods
     *
     * @return array|null
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getRelevantPaymentMethods()
    {
        return OrderRepository::getActivelyUsedPaymentMethodsDaysAgo($this->paymentMethodActivitySpanDays);
    }

}