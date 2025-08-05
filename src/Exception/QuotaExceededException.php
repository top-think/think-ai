<?php

namespace think\ai\Exception;

use think\ai\Exception;

class QuotaExceededException extends Exception
{
    protected string $quotaType;
    protected ?float $limit;
    protected ?float $used;
    
    public function __construct(
        string $quotaType = 'tokens',
        ?float $limit = null,
        ?float $used = null,
        string $message = '',
        int $code = 429,
        \Throwable $previous = null
    ) {
        $this->quotaType = $quotaType;
        $this->limit = $limit;
        $this->used = $used;
        
        if (empty($message)) {
            $message = "配额超限";
            if ($quotaType) {
                $message = ucfirst($quotaType) . " 配额超限";
            }
            if ($limit !== null && $used !== null) {
                $message .= "（已使用: {$used}/{$limit}）";
            }
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 获取配额类型
     */
    public function getQuotaType(): string
    {
        return $this->quotaType;
    }
    
    /**
     * 获取配额限制
     */
    public function getLimit(): ?float
    {
        return $this->limit;
    }
    
    /**
     * 获取已使用配额
     */
    public function getUsed(): ?float
    {
        return $this->used;
    }
    
    /**
     * 获取剩余配额
     */
    public function getRemaining(): ?float
    {
        if ($this->limit !== null && $this->used !== null) {
            return max(0, $this->limit - $this->used);
        }
        return null;
    }
}