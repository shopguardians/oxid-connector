<?php

namespace ActiveValue\Shopguardians\Model;

class VariantHandler extends VariantHandler_parent
{
    /**
     * Public getter for protected method
     *
     * @inheritdoc
     */
    public function getSelections($sTitle)
    {
        return $this->_getSelections($sTitle);
    }
}