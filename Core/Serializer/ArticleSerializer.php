<?php

namespace ActiveValue\Shopguardians\Core\Serializer;

use OxidEsales\Eshop\Application\Model\Article;

class ArticleSerializer
{
    /**
     * Turn this item object into a generic array
     *
     * @param Article $article
     * @return array
     */
    public static function transform(Article $article): ?array
    {
        return [
            'product_uid'          => $article->oxarticles__oxid->value,
            'stock'                => $article->oxarticles__oxstock->value,
            'price'                => money_format('%.2n', $article->getPrice()->getBruttoPrice()),
            'vat'                  => $article->getArticleVat(),
            'imageUrl'             => self::getMasterImageUrl($article),
            'thumbUrl'             => $article->getThumbnailUrl(),
            'description'          => $article->getShortDescription(),
            'category'             => $article->oxarticles__oxsearchkeys->value,
            'rating'               => $article->oxarticles__oxrating->value,
            'title'                => $article->oxarticles__oxtitle->value,
            'brand'                => $article->getManufacturerName(),
            'active'               => $article->oxarticles__oxactive->value,
            'description_full'     => $article->getLongDesc(),
            'parent'               => $article->oxarticles__oxvarcount->value > 0 ? true : false,
            'buyable'              => $article->isBuyable(),
            'variant_keys'         => $article->getVariantKeys(),
            'priceNet'             => money_format('%.2n', $article->getPrice()->getNettoPrice()),
            'persParams'           => self::getPersParams($article->oxarticles__oxpersparam->value),
            'artnum'               => $article->oxarticles__oxartnum->value,
            'url'                  => $article->getLink(),
            'sold_amount'          => $article->oxarticles__oxsoldamount->value
        ];
    }

    /**
     * Returns absolute URL to master image
     * otherwise transforms OXID false into null
     *
     * @param $oArticle
     * @return null|string
     */
    protected static function getMasterImageUrl($oArticle)
    {
        $masterImageUrl = $oArticle->getMasterZoomPictureUrl(1);
        return $masterImageUrl ? $masterImageUrl : null;
    }

    /**
     * Converts OXPERSPARAM to an array as per api-definition an array is required
     *
     * @param $sPersParam
     * @return array
     */
    protected function getPersParams($sPersParam)
    {
        if (empty($sPersParam)) {
            return [];
        }

        return [$sPersParam];
    }
}