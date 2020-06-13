<?php

namespace ActiveValue\Shopguardians\Controller\Api;

use ActiveValue\Shopguardians\Core\Events;
use ActiveValue\Shopguardians\Core\ResponseHelper;
use ActiveValue\Shopguardians\Core\Serializer\ArticleArraySerializer;
use ActiveValue\Shopguardians\Core\Serializer\ArticleListSerializer;
use ActiveValue\Shopguardians\Core\Serializer\ArticleSerializer;
use ActiveValue\Shopguardians\Core\Utils\PaginationUtils;
use ActiveValue\Shopguardians\Repositories\ArticleRepository;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\ArticleList;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class SecurityController
 * @package ActiveValue\Shopguardians\Controller\Api
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class ArticleController extends BaseController
{
    /**
     * Fetches a single article by OXID
     *
     * @return bool
     * @throws SystemComponentException
     */
    public function getArticle()
    {
        $oArticle = oxNew(Article::class);
        /** @var Article $oArticle */

        $articleOxid = Registry::getRequest()->getRequestEscapedParameter('oxid');

        if (!$oArticle->load($articleOxid)) {
            ResponseHelper::notFound("Article with $articleOxid could not be found");
        }

        // Call serializer
        return $this->renderJson(
            ArticleSerializer::transform($oArticle)
        );
    }

    /**
     * Returns all parent articles
     *
     */
    public function getArticlesForDataQuality()
    {
        $this->setPaginationParamsFromRequest();

        $articleList    = oxNew(ArticleList::class);
        $article        = oxNew(Article::class);

        $articleList->setSqlLimit($this->pagination->getOffset(), $this->pagination->getPerPage());
        $articleList->selectString($article->buildSelectString(['OXPARENTID' => '']));

        $articleListSerializer = oxNew(ArticleListSerializer::class);

        $output = ['result' => $articleListSerializer->transform($articleList)];

        if ($this->pagination) {
            $output['pagination'] = $this->pagination->getData();
        }

        return $this->renderJson($output);

    }

    /**
     * Returns articles without category association
     *
     * @return bool|mixed
     */
    public function getArticlesWithoutCategory()
    {
        $this->setPaginationParamsFromRequest();
        $blacklistQuery = $this->getBlacklistedArticleQuery();
        $totalCount = ArticleRepository::getCountForArticlesWithoutCategory($blacklistQuery);
        $this->pagination->setPagesCountFromTotalCount($totalCount);

        $sQ = "SELECT a.OXID,a.OXPARENTID,a.OXTITLE,a.OXARTNUM,a.OXSTOCK,a.OXPIC1,ae.OXLONGDESC,a.OXVARCOUNT,
    (
    SELECT s.OXSEOURL FROM oxseo s
    WHERE s.oxobjectid = a.OXID
    AND s.OXLANG = 0
    ORDER BY OXTIMESTAMP DESC
    LIMIT 1
    ) as seoLink
 FROM oxarticles a
LEFT JOIN oxobject2category o2c ON o2c.`OXOBJECTID` = a.OXID
LEFT JOIN oxartextends ae ON ae.OXID = a.OXID
WHERE o2c.OXOBJECTID IS NULL
AND a.OXACTIVE = ? AND a.OXPARENTID = '' $blacklistQuery
LIMIT {$this->pagination->getOffset()},{$this->pagination->getPerPage()}";

        return $this->getArticlesWhere($sQ, [1], true, true);
    }

    /**
     * Returns out of stock articles
     *
     * @return bool|mixed
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getOutOfStockArticles()
    {
        $this->setPaginationParamsFromRequest();
        $sQ = "a.OXSTOCK=0 AND a.OXACTIVE= 1 AND a.OXSTOCKFLAG <> 4";

        $skipVariantAggregation = false;

        if (Events::getSetting('IGNORE_PARENT_STOCK')) {
            $sQ = "((a.OXVARCOUNT > 0 AND a.OXVARSTOCK= 0) OR (a.OXVARCOUNT=0 AND a.OXSTOCK=0)) AND a.OXACTIVE= 1 AND a.OXSTOCKFLAG <> 4";
            $skipVariantAggregation = true;
        }

        $count = $this->getCountForConditionOnParentArticles($sQ, []);
        $this->pagination->setPagesCountFromTotalCount($count);

        return $this->getArticlesWhere($sQ, [], false, $skipVariantAggregation);
    }


    /**
     * Returns free articles, without a price
     *
     * @return bool|mixed
     */
    public function getFreeArticles()
    {
        $this->setPaginationParamsFromRequest();
        $sQ = "a.OXACTIVE= ? AND a.OXPRICE = ?";
        $queryParams = [1, 0];
        $count = $this->getCountForConditionOnParentArticles($sQ, $queryParams);
        $this->pagination->setPagesCountFromTotalCount($count);
        return $this->getArticlesWhere($sQ, $queryParams);
    }

    /**
     * Returns free articles, without a price
     * @return bool|mixed
     */
    public function getArticlesWithoutManufacturer()
    {
        $this->setPaginationParamsFromRequest();
        $sQ = "a.OXACTIVE= ? AND a.OXMANUFACTURERID = ''";
        $queryParams = [1];
        $count = $this->getCountForConditionOnParentArticles($sQ, $queryParams);
        $this->pagination->setPagesCountFromTotalCount($count);
        return $this->getArticlesWhere($sQ, $queryParams);
    }

    /**
     * Returns articles that are low on stock
     *
     * @return bool|mixed
     */
    public function getLowStockArticles()
    {
        $this->setPaginationParamsFromRequest();
        $sQ = "a.OXSTOCK <= ? AND a.OXACTIVE = ? AND a.OXSTOCKFLAG <> ?";

        if (Events::getSetting('IGNORE_PARENT_STOCK')) {
            $sQ = "a.OXVARSTOCK <= ? AND a.OXACTIVE= ? AND a.OXSTOCKFLAG <> ?";
        }

        $queryParams = [Registry::getConfig()->getConfigParam('sStockWarningLimit'), 1, 4];
        $count = $this->getCountForConditionOnParentArticles($sQ, $queryParams);
        $this->pagination->setPagesCountFromTotalCount($count);
        return $this->getArticlesWhere($sQ, [Registry::getConfig()->getConfigParam('sStockWarningLimit'), 1, 4]);
    }


    /**
     * Returns articles where any important information is missing
     *
     * @return bool|mixed
     */
    public function getArticlesWithoutPicture()
    {
        $this->setPaginationParamsFromRequest();
        $sQ = "a.OXACTIVE= ? AND a.OXPIC1 = '' ";
        $queryParams = [1];
        $count = $this->getCountForConditionOnParentArticles($sQ, $queryParams);
        $this->pagination->setPagesCountFromTotalCount($count);
        return $this->getArticlesWhere($sQ, [1]);
    }

    /**
     * Returns articles where any important information is missing
     *
     * @return bool|mixed
     */
    public function getArticlesWithoutLongDescription()
    {
        $this->setPaginationParamsFromRequest();
        $sQ = "a.OXACTIVE= ? AND REPLACE(ae.OXLONGDESC, '<p></p>','') = '' ";
        $queryParams = [1];
        $count = $this->getCountForConditionOnParentArticles($sQ, $queryParams);
        $this->pagination->setPagesCountFromTotalCount($count);
        return $this->getArticlesWhere($sQ, [1]);
    }

    /**
     * Returns articles where short description is missing
     *
     * @return bool|mixed
     */
    public function getArticlesWithoutAnyDescription()
    {
        $this->setPaginationParamsFromRequest();
        $sQ = "a.OXACTIVE= ? AND a.OXSHORTDESC = '' AND REPLACE(ae.OXLONGDESC, '<p></p>','') = ''";
        $queryParams = [1];
        $count = $this->getCountForConditionOnParentArticles($sQ, $queryParams);
        $this->pagination->setPagesCountFromTotalCount($count);
        return $this->getArticlesWhere($sQ, $queryParams);
    }

    /**
     * Generic article getter function
     *
     * We're using raw queries as it would be eating too much ram,
     * using OXIDs OM
     *
     * @param string $sWhereQuery
     * @param array $params
     * @param bool $isRawQuery
     * @param bool $skipVariantAggregation
     * @return bool|mixed
     */
    protected function getArticlesWhere($sWhereQuery, array $params, $isRawQuery=false, $skipVariantAggregation=false)
    {
        try {
            $parentQuery = $this->getParentQuery($sWhereQuery, $params);

            if ($isRawQuery) {
                $parentQuery = $sWhereQuery;
                $sWhereQuery = 1;
            }

            $aParentArticles = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($parentQuery, $params);
            $aVariantArticles = [];

            if ($skipVariantAggregation === false) {
                $parentOxids = implode("','", array_column($aParentArticles, 'OXID'));

                $variantQuery = $this->getVariantQuery($sWhereQuery, $parentOxids);

                $aVariantArticles = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($variantQuery, $params) ?? [];

                if (Events::getSetting('REMOVE_PARENTS_WITHOUT_VARIANTS')) {
                    $this->removeParentsWithoutMatchingVariants($aParentArticles, $aVariantArticles);
                }
            }


            // Merge parents and variants in a flat list for now
            $aArticles = array_merge($aParentArticles, $aVariantArticles);


        } catch (\Exception $e) {
            ResponseHelper::internalServerError($e->getMessage());
        }

        $output = ['result' => ArticleArraySerializer::transform($aArticles)];

        if ($this->pagination) {
            $output['pagination'] = $this->pagination->getData();
        }

        return $this->renderJson($output);

    }

    /**
     * Removes all parent articles from $aParentArticles that do not have any variant inside $aVariantArticles
     *
     * @param $aParentArticles
     * @param $aVariantArticles
     * @return mixed
     */
    protected function removeParentsWithoutMatchingVariants($aParentArticles, $aVariantArticles)
    {
        foreach ($aParentArticles as $key=>$aParentArticle) {
            if ($aParentArticle['OXVARCOUNT'] > 0) {
                $hasMatchingVariants = array_search($aParentArticle['OXID'], array_column($aVariantArticles, 'OXPARENTID'));

                if (!$hasMatchingVariants) {
                    unset($aParentArticles[$key]);
                }
            }
        }

        return $aParentArticles;
    }

    /**
     * Returns query to select all parent articles
     *
     * @param $sWhereQuery
     * @param array $params
     * @return string
     */
    protected function getParentQuery($sWhereQuery, array $params)
    {
        $blacklistQuery = $this->getBlacklistedArticleQuery();

        $parentQuery = "SELECT a.OXID,a.OXPARENTID,a.OXTITLE,a.OXARTNUM,a.OXSTOCK,a.OXPIC1,ae.OXLONGDESC,a.OXVARCOUNT,
(
    SELECT s.OXSEOURL FROM oxseo s
    WHERE s.oxobjectid = a.OXID
    AND s.OXLANG = 0
    ORDER BY OXTIMESTAMP DESC
    LIMIT 1
 ) as seoLink
 FROM oxarticles a 
 LEFT JOIN oxartextends ae ON ae.OXID = a.OXID
WHERE a.OXPARENTID='' $blacklistQuery AND $sWhereQuery
LIMIT {$this->pagination->getOffset()},{$this->pagination->getPerPage()}";

        return $parentQuery;
    }

    /**
     * Returns query to select variants of the given parent OXIDs
     *
     * @param $sWhereQuery
     * @param $parentOxids
     * @return string
     */
    protected function getVariantQuery($sWhereQuery, $parentOxids)
    {
        $blacklistQuery = $this->getBlacklistedArticleQuery();

        $variantQuery = "SELECT a.OXID,a.OXPARENTID,a.OXTITLE,a.OXVARSELECT,a.OXARTNUM,a.OXSTOCK,a.OXPIC1,ae.OXLONGDESC,
(
    SELECT s.OXSEOURL FROM oxseo s
    WHERE s.oxobjectid = a.OXID
    AND s.OXLANG = 0
    ORDER BY OXTIMESTAMP DESC
    LIMIT 1
 ) as seoLink
 FROM oxarticles a 
 LEFT JOIN oxartextends ae ON ae.OXID = a.OXID 
WHERE a.OXPARENTID IN ('$parentOxids') $blacklistQuery AND $sWhereQuery";

        return $variantQuery;
    }

    /**
     * Returns WHERE statement for blacklisted articles
     *
     * @return string
     */
    protected function getBlacklistedArticleQuery()
    {
        $articleBlacklist = Events::getSetting('ARTICLE_BLACKLIST');

        $blacklistQuery = '';
        foreach ($articleBlacklist as $blacklistedArticleNumber) {
            $blacklistQuery .= " AND a.OXARTNUM NOT LIKE '$blacklistedArticleNumber%' ";
        }

        return $blacklistQuery;
    }

    /**
     * @param $conditionQuery string
     * @param $params array
     * @param string $blacklistQuery
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function getCountForConditionOnParentArticles($conditionQuery, $params)
    {
        $blacklistQuery = $this->getBlacklistedArticleQuery();

        $query = "SELECT count(*)
 FROM oxarticles a 
 LEFT JOIN oxartextends ae ON ae.OXID = a.OXID
WHERE a.OXPARENTID='' $blacklistQuery AND $conditionQuery";
        $totalCount = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getOne($query, $params);
        if (!$totalCount) {
            return 0;
        }
        return intval($totalCount);
    }

}