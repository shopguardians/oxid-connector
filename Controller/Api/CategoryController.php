<?php

namespace ActiveValue\Shopguardians\Controller\Api;

use ActiveValue\Shopguardians\Core\ResponseHelper;
use ActiveValue\Shopguardians\Core\Serializer\CategoryListSerializer;
use ActiveValue\Shopguardians\Repositories\CategoryRepository;


/**
 * Class CategoryController
 * @package ActiveValue\Shopguardians\Controller\Api
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
class CategoryController extends BaseController
{
    /**
     * Returns categories with missing OXDESC, OXLONGDESC or OXTHUMB
     */
    public function getPoorlyMaintainedCategories()
    {
        $this->setPaginationParamsFromRequest();

        $aCategories = [];

        try {
            $count = CategoryRepository::getPoorlyMaintainedCategoriesCount();
            $this->pagination->setPagesCountFromTotalCount($count);
            $aCategories = CategoryRepository::getPoorlyMaintainedCategories($this->pagination);
        } catch (\Throwable $e) {
            ResponseHelper::internalServerError($e->getMessage());
        }

        $output = ['result' => CategoryListSerializer::transform($aCategories)];

        if ($this->pagination) {
            $output['pagination'] = $this->pagination->getData();
        }

        return $this->renderJson($output);
    }
}