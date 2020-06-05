<?php
/**
 * @author Hans Wellenschritt hans.wellenschritt@active-value.de
 * @copyright active value GmbH
 * Date: 30.04.20
 * Time: 09:23
 */

namespace ActiveValue\Shopguardians\Repositories;


use OxidEsales\Eshop\Core\DatabaseProvider;

class ArticleRepository
{

    /**
     * @param $blacklistQuery string
     * @param $perPage int
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public static function getCountForArticlesWithoutCategory($blacklistQuery)
    {
        $query = "SELECT count(*)
FROM oxarticles a
LEFT JOIN oxobject2category o2c ON o2c.`OXOBJECTID` = a.OXID
LEFT JOIN oxartextends ae ON ae.OXID = a.OXID
WHERE o2c.OXOBJECTID IS NULL
AND a.OXACTIVE = ? AND a.OXPARENTID = '' $blacklistQuery";
        $totalCount = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getOne($query, [1]);
        if (!$totalCount) {
            return 0;
        }
        return intval($totalCount);
    }

}