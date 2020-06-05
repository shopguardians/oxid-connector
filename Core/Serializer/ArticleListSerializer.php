<?php

namespace ActiveValue\Shopguardians\Core\Serializer;

use OxidEsales\Eshop\Core\Registry;

class ArticleListSerializer
{
    /**
     * Turn this item object into a generic array
     *
     * @param array $articles
     * @return array
     */
    public static function transform(array $articles): ?array
    {
        $serialized = [];

        foreach ($articles as $article) {
            $serialized[] = [
                'product_uid'          => $article['OXID'],
                'stock'                => (int) $article['OXSTOCK'],
                'title'                => self::getTitle($article),
                'parent_id'            => $article['OXPARENTID'],
                'artnum'               => $article['OXARTNUM'],
                'thumb'                => self::getThumbnailUrl($article),
                'url'                  => self::getDetailUrl($article)
            ];
        }

        return $serialized;
    }

    /**
     * Returns full url to article
     *
     * @param $article
     * @return string
     */
    public static function getDetailUrl($article)
    {
        if (empty($article['seoLink'])) return null;
        $sFullUrl =  Registry::getConfig()->getShopUrl() . $article['seoLink'];
        return \OxidEsales\Eshop\Core\Registry::getUtilsUrl()->processSeoUrl($sFullUrl);
    }

    /**
     * Return thumbnail url for first article picture
     *
     * @param $article
     * @return bool|string
     */
    public static function getThumbnailUrl($article)
    {
        $sDirname = "product/1/";
        $sImgName = basename($article['OXPIC1']);

        $sSize = Registry::getConfig()->getConfigParam('sThumbnailsize');

        return \OxidEsales\Eshop\Core\Registry::getPictureHandler()->getProductPicUrl($sDirname, $sImgName, $sSize, 0);
    }

    /**
     * Returns article title
     *
     * @param $article
     * @return string
     */
    public static function getTitle($article)
    {
        $sVariantName = $article['PARENTTITLE'] . ' ' . $article['OXVARSELECT'];

        return !empty($article['OXTITLE']) ? $article['OXTITLE'] : $sVariantName;
    }
}