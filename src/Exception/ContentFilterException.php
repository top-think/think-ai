<?php

namespace think\ai\Exception;

use think\ai\Exception;

class ContentFilterException extends Exception
{
    protected string $filterType;
    protected ?string $flaggedContent;
    
    public function __construct(
        string $filterType = 'content',
        ?string $flaggedContent = null,
        string $message = '',
        int $code = 400,
        \Throwable $previous = null
    ) {
        $this->filterType = $filterType;
        $this->flaggedContent = $flaggedContent;
        
        if (empty($message)) {
            $message = "内容被安全过滤器拦截";
            if ($filterType !== 'content') {
                $message = ucfirst($filterType) . " 被安全过滤器拦截";
            }
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 获取过滤器类型
     */
    public function getFilterType(): string
    {
        return $this->filterType;
    }
    
    /**
     * 获取被标记的内容
     */
    public function getFlaggedContent(): ?string
    {
        return $this->flaggedContent;
    }
}