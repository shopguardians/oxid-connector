<?php

namespace ActiveValue\Shopguardians\Controller\Api;

use ActiveValue\Shopguardians\Core\Events;
use ActiveValue\Shopguardians\Core\ResponseHelper;
use ActiveValue\Shopguardians\Core\Utils\PaginationData;
use ActiveValue\Shopguardians\Core\Utils\PaginationUtils;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class BaseController
 * @package ActiveValue\Shopguardians\Controller\Api
 *
 * @author Alex Schwarz <alex.schwarz@active-value.de>
 * @copyright 2020 active value GmbH
 *
 */
abstract class BaseController extends FrontendController
{

    /**
     * @var PaginationData
     */
    public $pagination;

    public function render()
    {
        return null;
    }

    public function __construct()
    {
        $this->handlePreflight();
    }

    /**
     * Outputs json encoded version of $data
     * Sets appropriate header and exists to omit OXIDs response
     *
     * @param $data
     */
    public function renderJson($data)
    {
        $this->checkAuth();

        $origin = $_SERVER['HTTP_ORIGIN'];

        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: x-api-key,content-type");
        header("Content-Type: application/json");
        echo json_encode($data);
        exit();
    }

    public function handlePreflight()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
            return;
        }
        $origin = $_SERVER['HTTP_ORIGIN'];
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: x-api-key,content-type");
        header("Content-Type: application/json");
        exit();
    }

    /**
     * Check if the configured api key is matching the request api key
     */
    public function checkAuth()
    {
        $apiKey = Events::getSetting('API_KEY');

        if (!$apiKey || $_SERVER['HTTP_X_API_KEY'] != $apiKey && $_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
            ResponseHelper::notAuthorized();
        }
    }

    protected function setPaginationParamsFromRequest()
    {
        $this->pagination = new PaginationData();
        $perPage = Registry::getRequest()->getRequestEscapedParameter('limit');
        if (empty($perPage)) {
            $perPage = 100;
        }
        $perPage = intval($perPage);

        $this->pagination->setPerPage($perPage);

        $page = Registry::getRequest()->getRequestEscapedParameter('page');
        if (!$page) {
            $page = 0;
        }
        $page = intval($page);

        $this->pagination->setPage($page);
    }

}