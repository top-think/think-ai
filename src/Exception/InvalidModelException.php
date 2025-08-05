<?php

namespace think\ai\Exception;

use think\ai\Exception;

class InvalidModelException extends Exception
{
    protected string $model;
    protected array $availableModels;
    
    public function __construct(string $model, array $availableModels = [], int $code = 400, \Throwable $previous = null)
    {
        $this->model = $model;
        $this->availableModels = $availableModels;
        
        $message = "无效的模型: {$model}";
        if (!empty($availableModels)) {
            $message .= "。可用的模型: " . implode(', ', $availableModels);
        }
        
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * 获取请求的模型名称
     */
    public function getModel(): string
    {
        return $this->model;
    }
    
    /**
     * 获取可用的模型列表
     */
    public function getAvailableModels(): array
    {
        return $this->availableModels;
    }
}