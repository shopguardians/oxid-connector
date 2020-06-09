<?php
/**
 *
 * @category      module
 * @package       module
 * @author        active value GmbH
 * @link          http://active-value.de
 * @copyright (C) active value GmbH, 2017-2018
 */

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

/**
 * Module information
 */
$aModule = [
    'id'          => 'AvShopguardians',
    'title'       => '<span style="color: #706f6f;">SHOP</span><span style="color: #000;"><strong>GUARDIANS</strong></span> Connector',
    'description' => 'Shopguardians connector',
    'thumbnail'   => 'img.png',
    'version'     => '1.0.4',
    'author'      => 'active value GmbH',
    'url'         => 'http://active-value.de',
    'email'       => 'oxidmodule@active-value.de',
    'extend'      => [
        \OxidEsales\Eshop\Application\Model\Article::class => \ActiveValue\Shopguardians\Model\Article::class,
        \OxidEsales\Eshop\Application\Model\VariantHandler::class => \ActiveValue\Shopguardians\Model\VariantHandler::class,
        \OxidEsales\Eshop\Application\Model\User::class => \ActiveValue\Shopguardians\Model\User::class

    ],
    'controllers' => [
        'av_shopguardians_security' => \ActiveValue\Shopguardians\Controller\Api\SecurityController::class,
        'av_shopguardians_articles' => \ActiveValue\Shopguardians\Controller\Api\ArticleController::class,
        'av_shopguardians_orders' => \ActiveValue\Shopguardians\Controller\Api\OrderController::class,
        'av_shopguardians_customers' => \ActiveValue\Shopguardians\Controller\Api\CustomerController::class,
        'av_shopguardians_categories' => \ActiveValue\Shopguardians\Controller\Api\CategoryController::class
    ],
    'templates'   => [
    ],
    'blocks'      => [

    ],
    'settings'    => [
        ['group' => 'avshopguardians_main', 'name' => 'AVSHOPGUARDIANS_API_KEY', 'type' => 'str', 'value' => ''],
        ['group' => 'avshopguardians_main', 'name' => 'AVSHOPGUARDIANS_IGNORE_PARENT_STOCK', 'type' => 'bool', 'value' => 0],

        ['group' => 'avshopguardians_dataquality', 'name' => 'AVSHOPGUARDIANS_ONLY_PARENTS', 'type' => 'bool', 'value' => 0],
        ['group' => 'avshopguardians_dataquality', 'name' => 'AVSHOPGUARDIANS_REMOVE_PARENTS_WITHOUT_VARIANTS', 'type' => 'bool', 'value' => 0],
        ['group'  => 'avshopguardians_dataquality', 'name'  => 'AVSHOPGUARDIANS_ARTICLE_BLACKLIST','type'  => 'arr','value' => []],

        ['group'  => 'avshopguardians_sales', 'name' => 'AVSHOPGUARDIANS_OHS_DEVIATION_TRESHOLD','type' => 'str','value' => 50],
        ['group'  => 'avshopguardians_sales', 'name' => 'AVSHOPGUARDIANS_OHS_SAFETY_BUFFER_FACTOR','type' => 'str','value' => 5],
        ['group'  => 'avshopguardians_sales', 'name' => 'AVSHOPGUARDIANS_OHS_PAYMENTMETHOD_ACTIVITY_DAYS','type' => 'str','value' => 120],

    ],
    'events'      => [
        'onActivate'   => '\ActiveValue\Shopguardians\Core\Events::onActivate',
        'onDeactivate'   => '\ActiveValue\Shopguardians\Core\Events::onDeactivate',
    ],
];
