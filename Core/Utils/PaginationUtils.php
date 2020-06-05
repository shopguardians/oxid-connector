<?php
/**
 * @author Hans Wellenschritt hans.wellenschritt@active-value.de
 * @copyright active value GmbH
 * Date: 29.04.20
 * Time: 18:26
 */

namespace ActiveValue\Shopguardians\Core\Utils;


use OxidEsales\Eshop\Core\DatabaseProvider;

class PaginationUtils
{

    public static function calcTotalPages($totalCount, $perPage)
    {
        return (int) ceil($totalCount / $perPage);
    }

}