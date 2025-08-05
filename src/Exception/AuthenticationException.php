<?php

namespace think\ai\Exception;

use think\ai\Exception;

class AuthenticationException extends Exception
{
    public function __construct(string $message = '', int $code = 401, \Throwable $previous = null)
    {
        if (empty($message)) {
            $message = "认证失败：无效的 API Token";
        }
        
        parent::__construct($message, $code, $previous);
    }
}