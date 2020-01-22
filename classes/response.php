<?php

class Response
{
    public static $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        403 => 'Forbidden',
        404 => 'Not Found',
        409 => 'Conflict',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
    ];

    public static function getMessage($responseCode)
    {
        return $responseCode . ' ' . self::$statusTexts[$responseCode];
    }
}