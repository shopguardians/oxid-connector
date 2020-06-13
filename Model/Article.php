<?php

namespace ActiveValue\Shopguardians\Model;

use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Manufacturer;
use OxidEsales\Eshop\Application\Model\VariantHandler;
use OxidEsales\Eshop\Core\Registry;

class Article extends Article_parent
{
    /**
     * Returns short description field
     *
     * @return null|string
     */
    public function getShortDescription()
    {
        return isset($this->oxarticles__oxshortdesc->value) ? $this->oxarticles__oxshortdesc->value : null;
    }

    /**
     * Returns name of assigned main category
     *
     * @return null|string
     */
    public function getCategoryName()
    {
        $oCategory = $this->getCategory();

        if ($oCategory instanceof Category) {
            return $oCategory->getTitle();
        }

        return null;
    }

    /**
     * Returns name of assigned manufacturer
     *
     * @return null|string
     */
    public function getManufacturerName()
    {
        if (!$this->oxarticles__oxmanufacturerid->value) {
            return null;
        }

        $oManufacturer = $this->getManufacturer();

        if ($oManufacturer instanceof Manufacturer) {
            return $oManufacturer->getTitle();
        }

        return null;
    }

    /**
     * Returns a list of variant keys or an empty array if none
     *
     * @return array
     */
    public function getVariantKeys()
    {
        if ($this->getVariantsCount() == 0) {
            return [];
        }

        $variantHandler = Registry::get(VariantHandler::class);
        return $variantHandler->getSelections($this->oxarticles__oxvarname->value);
    }

    /**
     * Returns a list of key value pairs of the selected MD variants
     *
     * @return array|null
     */
    public function getVariantValues()
    {
        if ($this->oxarticles__oxvarselect->value == '') {
            return null;
        }

        $oParentArticle = $this->getParentArticle();
        if (!$oParentArticle instanceof \OxidEsales\Eshop\Application\Model\Article) {
            return null;
        }

        $oVariantHandler    = Registry::get( VariantHandler::class );
        $aVariantKeys       = $oParentArticle->getVariantKeys();
        $aVariantValues     = $oVariantHandler->getSelections($this->oxarticles__oxvarselect->value);

        $aVariantSelections = [];

        foreach ($aVariantValues as $key=>$value) {
            $aVariantSelections[] = [
                'key' => $aVariantKeys[$key],
                'value' => $value
            ];
        }

        return $aVariantSelections;

    }
}