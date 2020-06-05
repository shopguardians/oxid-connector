<?php

namespace ActiveValue\Shopguardians\Core;

class ResponseHelper
{
    public static function notFound(?string $message=null)
    {
        if (!$message) {
            $message = 'Not found';
        }

        self::sendResponse(404, $message);
    }

    /**
     * Stops execution and sends a 401 Header
     */
    public static function notAuthorized()
    {
        self::sendResponse(401, 'Unauthorized');
    }

    public static function internalServerError($message)
    {
        if (!$message) {
            $message = 'Internal server error';
        }

        self::sendResponse(500, $message);
    }

    public static function sendResponse(int $code, ?string $message)
    {
        header('Content-Type: application/json');
        header("HTTP/1.1 $code");
        die($message);
    }
}