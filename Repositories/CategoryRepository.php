<?php

namespace ActiveValue\Shopguardians\Repositories;

use ActiveValue\Shopguardians\Core\Utils\PaginationData;
use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Class CategoryRepository
 * @package ActiveValue\Shopguardians\Repositories
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class CategoryRepository
{
    /**
     * @param PaginationData $pagination
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function getPoorlyMaintainedCategories(PaginationData $pagination)
    {
        $query = "SELECT OXID,OXPARENTID,OXACTIVE,OXTITLE,OXDESC,OXTHUMB,CHAR_LENGTH(OXLONGDESC),
 (
    SELECT s.OXSEOURL FROM oxseo s
    WHERE s.oxobjectid = OXID
    AND s.OXLANG = 0
    ORDER BY OXTIMESTAMP DESC
    LIMIT 1
 ) as seoLink FROM oxcategories 
WHERE OXDESC = '' OR OXLONGDESC = '' OR OXTHUMB = ''
ORDER BY OXACTIVE DESC, OXTITLE
LIMIT {$pagination->getOffset()},{$pagination->getPerPage()}";

        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($query);

    }

    /**
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function getPoorlyMaintainedCategoriesCount()
    {
        $query = "SELECT COUNT(OXID) FROM oxcategories 
WHERE OXDESC = '' OR OXLONGDESC = '' OR OXTHUMB = ''";

        $totalCount = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getOne($query);
        if (!$totalCount) {
            return 0;
        }

        return intval($totalCount);

    }

}