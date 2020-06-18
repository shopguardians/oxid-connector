<?php

namespace ActiveValue\Shopguardians\Core\Serializer;

use ActiveValue\Shopguardians\Core\Events;
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

    protected $viewNameGenerator;

    public function __construct()
    {
        $this->pictureCount     = Registry::getConfig()->getConfigParam('iPicCount');
        $this->thumbnailSize    = Registry::getConfig()->getConfigParam('sThumbnailsize');
        $this->pictureHandler   = Registry::getPictureHandler();
        $this->viewNameGenerator = Registry::get(\OxidEsales\Eshop\Core\TableViewNameGenerator::class);
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

            $articlePictures = $this->serializeArticlePictures($article);

            $serializedFields = [
                'productUid'          => $article->oxarticles__oxid->value,
                'stock'                => (int) $article->oxarticles__oxstock->value,
                'price'                => money_format('%.2n', $article->getPrice()->getBruttoPrice()),
                'vat'                  => $article->getArticleVat(),
                'rating'               => $article->oxarticles__oxrating->value,
                'active'               => $article->oxarticles__oxactive->value,
                'hasVariants'         => $article->oxarticles__oxvarcount->value > 0 ? true : false,
                'buyable'              => $article->isBuyable(),
                //'variant_keys'         => $article->getVariantKeys(),
                'priceNet'             => money_format('%.2n', $article->getPrice()->getNettoPrice()),
                'artnum'               => $article->oxarticles__oxartnum->value,
                'url'                  => [$article->getLink()],
                'soldAmount'          => $article->oxarticles__oxsoldamount->value,
                'parentUid'            => $article->oxarticles__oxparentid->value,
                'thumb'                => $article->getThumbnailUrl(),
                'pictures'             => $articlePictures,
                'pictureCount'       => count($articlePictures)
            ];

            // Multi language handling
            $languages = Events::getActiveLanguages();


            foreach ($languages as $languageCode=>$language) {
                if ($language['baseId'] !== 0) {
                    $article = oxNew(Article::class);
                    $article->loadInLang($language['baseId'], $article->getId());
                }

                $serializedFields['title'][$languageCode] = $article->getTitle();
                $serializedFields['fullDescription'][$languageCode] = $this->sanitizeDescription($article->getLongDesc());
                $serializedFields['mainCategory'][$languageCode] = $this->getCategoryName($article);
                $serializedFields['categories'][$languageCode] = $article->getCategoryIds(); // TODO: Change to names instead of ids
                $serializedFields['manufacturer'][$languageCode] = $article->getManufacturer();
                $serializedFields['vendor'][$languageCode] = $article->getVendor();
                $serializedFields['shortDescription'][$languageCode] = $article->getCoreFieldInLanguage('oxshortdesc', $language['baseId']);
                $serializedFields['keywords'][$languageCode] = $article->getCoreFieldInLanguage('oxsearchkeys', $language['baseId']);
                $serializedFields['brand'][$languageCode] = $article->getManufacturername();
                $serializedFields['url'][$languageCode] = $article->getLink();
            }


            $serialized[] = $serializedFields;


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