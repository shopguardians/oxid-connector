<?php

namespace ActiveValue\Shopguardians\Core\Serializer;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\ArticleList;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class ArticleListSerializer
 * @package ActiveValue\Shopguardians\Core\Serializer
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class ArticleListSerializer extends BaseSerializer
{
    /**
     * Maximum number of pictures on article
     *
     * @var int|null
     */
    protected $pictureCount;

    /**
     * Configured thumbnail size in backend
     *
     * @var mixed|null
     */
    protected $thumbnailSize;

    /**
     * Cached instance of PictureHandler
     *
     * @var \OxidEsales\Eshop\Core\PictureHandler
     */
    protected $pictureHandler;

    public function __construct()
    {
        $this->pictureCount     = Registry::getConfig()->getConfigParam('iPicCount');
        $this->thumbnailSize    = Registry::getConfig()->getConfigParam('sThumbnailsize');
        $this->pictureHandler   = Registry::getPictureHandler();
    }

    /**
     * Turn this item object into a generic array
     *
     * @param ArticleList $articles
     * @return array
     */
    public function transform(?ArticleList $articles): ?array
    {
        $serialized = [];

        foreach ($articles as $article) {
            /** @var Article $article */

            $articlePictures = $this->serializeArticlePictures($article);

            $serialized[] = [
                'product_uid'          => $article->oxarticles__oxid->value,
                'stock'                => (int) $article->oxarticles__oxstock->value,
                'price'                => money_format('%.2n', $article->getPrice()->getBruttoPrice()),
                'vat'                  => $article->getArticleVat(),
                'imageUrl'             => $article->getMasterZoomPictureUrl(1),
                'description'          => $article->getShortDescription(),
                'keywords'             => $article->oxarticles__oxsearchkeys->value,
                'rating'               => $article->oxarticles__oxrating->value,
                'title'                => $article->oxarticles__oxtitle->value,
                'brand'                => $article->getManufacturerName(),
                'active'               => $article->oxarticles__oxactive->value,
                'description_full'     => $this->sanitizeDescription($article->getLongDesc()),
                'parent'               => $article->oxarticles__oxvarcount->value > 0 ? true : false,
                'buyable'              => $article->isBuyable(),
                //'variant_keys'         => $article->getVariantKeys(),
                'priceNet'             => money_format('%.2n', $article->getPrice()->getNettoPrice()),
                'artnum'               => $article->oxarticles__oxartnum->value,
                'url'                  => $article->getLink(),
                'sold_amount'          => $article->oxarticles__oxsoldamount->value,
                'parent_id'            => $article->oxarticles__oxparentid->value,
                'thumb'                => $article->getThumbnailUrl(),
                'pictures'             => $articlePictures,
                'pictures_count'       => count($articlePictures),
                'category_main'        => $this->getCategoryName($article),
                'category_count'       => $article->getCategoryIds(),
                'manufacturer'         => $article->getManufacturer(),
                'vendor'               => $article->getVendor()
            ];
        }

        return $serialized;
    }


    /**
     * The OXID rich text editor may save an empty <p> tag
     *
     * @param $description
     * @return string|string[]
     */
    protected function sanitizeDescription($description)
    {
        return preg_replace('/^<p><\/p>$/i', '', $description );
    }

    /**
     * Returns category title or null
     *
     * @param Article $article
     * @return string|null
     */
    protected function getCategoryName(Article $article): ?string
    {
        $category = $article->getCategory();
        /** @var Category $category */

        return $category ? $category->getTitle() : null;
    }

    /**
     * Returns an array of all article pictures
     * Returns original image and thumbnail size
     *
     * @param Article $article
     * @return array
     */
    protected function serializeArticlePictures(Article $article)
    {
        $pictures = [];

        for ($i=1; $i <= $this->pictureCount; $i++) {
            $originalPicture = self::getMasterImageUrl($article, $i);
            $thumbnailPicture = self::getThumbnailUrl($article, $i);

            if (!$originalPicture && !$thumbnailPicture) {
                continue;
            }

            $pictures[] = [
                'index' => $i,
                'thumbnail' => $thumbnailPicture,
                'original' => $originalPicture
            ];
        }
        return $pictures;
    }

    /**
     * Returns the original image url
     *
     * @param Article $article
     * @param int|null $index
     * @return bool|string|null
     */
    protected function getMasterImageUrl(Article $article, ?int $index=1)
    {
        $key = "oxarticles__oxpic$index";

        if (empty($article->{$key}->value)) {
            return null;
        }

        return $article->getMasterZoomPictureUrl($index);
    }


    /**
     * Return thumbnail url for first article picture
     *
     * @param $article
     * @param int $inde
     * @return bool|null|string
     */
    protected function getThumbnailUrl(Article $article, ?int $index=1)
    {
        $key = "oxarticles__oxpic$index";

        if (empty($article->{$key}->value)) {
            return null;
        }

        $sDirname = "product/$index/";
        $sImgName = basename($article->{$key});

        return $this->pictureHandler->getProductPicUrl($sDirname, $sImgName, $this->thumbnailSize, $index);
    }

}