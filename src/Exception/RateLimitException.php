<?php

namespace think\ai\Exception;

use think\ai\Exception;

class RateLimitException extends Exception
{
    protected int $retryAfter;
    
    public function __construct(string $message = '', int $retryAfter = 0, int $code = 429, \Throwable $previous = null)
    {
        $this->retryAfter = $retryAfter;
        
        if (empty($message)) {
            $message = "请求频率超限";
            if ($retryAfter > 0) {
                $message .= "，请在 {$retryAfter} 秒后重试";
            }
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 获取重试等待时间（秒）
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
    
    /**
     * 是否可以重试
     */
    public function canRetry(): bool
    {
        return $this->retryAfter > 0;
    }
}