<?php

namespace ActiveValue\Shopguardians\Model;

use Doctrine\DBAL\ConnectionException;
use OxidEsales\Eshop\Core\DatabaseProvider;

class User extends User_parent
{
    /**
     * Returns the amount of users matching specified criteria
     *
     * @return int
     */
    public function getUserCount($whereSql='')
    {
        $oDb = DatabaseProvider::getDb();
        $sQ = "select count(*) from oxuser where oxshopid = '" . $this->getConfig()->getShopId() . "' $whereSql";
        $iCnt = (int) $oDb->getOne($sQ);

        return $iCnt;
    }

    /**
     * Returns number of users that registered today
     *
     * @return int
     */
    public function getNewUserCount()
    {
        return $this->getUserCount("AND OXPASSWORD <> '' AND DATE(OXCREATE) = CURDATE()");
    }

    /**
     * Returns number of users that registered in total
     *
     * @return int
     */
    public function getUserCountTotal()
    {
        return $this->getUserCount("AND OXPASSWORD <> ''");
    }

    /**
     * Returns number of total guest users
     *
     * @return int
     */
    public function getNewGuestUserCount()
    {
        return $this->getUserCount("AND OXPASSWORD = '' AND DATE(OXCREATE) = CURDATE()");
    }

    /**
     * Returns number of total guest users
     *
     * @return int
     */
    public function getGuestUserCount()
    {
        return $this->getUserCount("AND OXPASSWORD = ''");
    }

    /**
     * Returns number of todays newsletter subscribers
     *
     * @return int
     * @throws ConnectionException
     */
    public function getNewsletterSubscriberCountToday()
    {
        $oDb = DatabaseProvider::getDb();
        $sQ = "select count(*) from oxnewssubscribed where oxshopid = '" . $this->getConfig()->getShopId() . "' AND DATE(OXSUBSCRIBED) = CURDATE() AND OXDBOPTIN = 1";
        $iCnt = (int) $oDb->getOne($sQ);

        return $iCnt;
    }

    /**
     * Returns number of total newsletter subscribers
     *
     * @return int
     * @throws ConnectionException
     */
    public function getNewsletterSubscriberCount()
    {
        $oDb = DatabaseProvider::getDb();
        $sQ = "select count(*) from oxnewssubscribed where oxshopid = '" . $this->getConfig()->getShopId() . "' AND OXDBOPTIN = 1";
        $iCnt = (int) $oDb->getOne($sQ);

        return $iCnt;
    }
}