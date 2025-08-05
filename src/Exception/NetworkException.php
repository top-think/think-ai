<?php

namespace think\ai\Exception;

use think\ai\Exception;

class NetworkException extends Exception
{
    protected bool $retryable;
    
    public function __construct(
        string $message = '',
        bool $retryable = true,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->retryable = $retryable;
        
        if (empty($message)) {
            $message = "网络连接错误";
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 是否可以重试
     */
    public function isRetryable(): bool
    {
        return $this->retryable;
    }
}