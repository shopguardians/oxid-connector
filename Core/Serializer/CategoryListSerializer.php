<?php

namespace ActiveValue\Shopguardians\Core\Serializer;

use OxidEsales\Eshop\Core\Registry;

class CategoryListSerializer extends BaseSerializer
{
    /**
     * Turn this item object into a generic array
     *
     * @param array $categories
     * @return array
     */
    public static function transform(array $categories): ?array
    {
        $serialized = [];

        foreach ($categories as $category) {
            $serialized[] = [
                'category_uid'              => $category['OXID'],
                'active'                    => (int) $category['OXACTIVE'],
                'title'                     => $category['OXTITLE'],
                'parent_id'                 => $category['OXPARENTID'],
                'description'               => $category['OXDESC'],
                'full_description_length'   => (int) $category['CHAR_LENGTH(OXLONGDESC)'],
                'thumb'                     => self::getThumbnailUrl($category),
                'url'                       => self::getDetailUrl($category)
            ];
        }

        return $serialized;
    }

    /**
     * Return thumbnail url for category
     *
     * @param $category
     * @return null|false|string
     */
    public static function getThumbnailUrl($category)
    {
        if (($sIcon = $category['OXTHUMB'])) {
            $sSize = Registry::getConfig()->getConfigParam('sCatThumbnailsize');

            return \OxidEsales\Eshop\Core\Registry::getPictureHandler()->getPicUrl("category/thumb/", $sIcon, $sSize);
        }

        return null;
    }

}