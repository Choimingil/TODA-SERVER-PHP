<?php

use JetBrains\PhpStorm\NoReturn;

class DefaultResponse
{
    #[NoReturn] public function __construct(bool $isSuccess, int $code, string $message)
    {
        $res = (object) Array();
        $res->isSuccess = $isSuccess;
        $res->code = $code;
        $res->message = $message;
        echo json_encode($res,JSON_NUMERIC_CHECK);
        exit;
    }
}

class ResultResponse
{
    #[NoReturn] public function __construct($result, bool $isSuccess, int $code, string $message)
    {
        $res = (object) Array();
        $res->result = $result;
        $res->isSuccess = $isSuccess;
        $res->code = $code;
        $res->message = $message;
        echo json_encode($res,JSON_NUMERIC_CHECK);
        exit;
    }
}