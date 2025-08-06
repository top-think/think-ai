<?php

namespace think\ai\Builder;

use think\ai\Client;

abstract class BaseBuilder
{
    protected Client $client;
    protected array $params = [];
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * 设置模型
     */
    public function model(string $model): static
    {
        $this->params['model'] = $model;
        return $this;
    }
    
    /**
     * 设置用户标识
     */
    public function userId(string $userId): static
    {
        $this->params['user'] = $userId;
        return $this;
    }
    
    /**
     * 设置自定义参数
     */
    public function param(string $key, $value): static
    {
        $this->params[$key] = $value;
        return $this;
    }
    
    /**
     * 批量设置参数
     */
    public function params(array $params): static
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    /**
     * 获取当前参数
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * 获取指定参数
     */
    public function getParam(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    
    /**
     * 检查参数是否存在
     */
    public function hasParam(string $key): bool
    {
        return isset($this->params[$key]);
    }
    
    /**
     * 移除参数
     */
    public function removeParam(string $key): static
    {
        unset($this->params[$key]);
        return $this;
    }
    
    /**
     * 清空所有参数
     */
    public function clearParams(): static
    {
        $this->params = [];
        return $this;
    }
    
    /**
     * 获取Client实例
     */
    protected function getClient(): Client
    {
        return $this->client;
    }
    
    /**
     * 验证必需的参数
     */
    protected function validateRequired(array $required): void
    {
        foreach ($required as $param) {
            if (!isset($this->params[$param])) {
                throw new \InvalidArgumentException("必须指定{$param}");
            }
        }
    }
    
    /**
     * 验证参数值在允许的范围内
     */
    protected function validateEnum(string $param, array $allowedValues): void
    {
        if (isset($this->params[$param]) && !in_array($this->params[$param], $allowedValues, true)) {
            $allowed = implode(', ', $allowedValues);
            throw new \InvalidArgumentException("{$param}必须是以下值之一: {$allowed}");
        }
    }
    
    /**
     * 验证数值参数的范围
     */
    protected function validateRange(string $param, ?float $min = null, ?float $max = null): void
    {
        if (!isset($this->params[$param])) {
            return;
        }
        
        $value = $this->params[$param];
        
        if ($min !== null && $value < $min) {
            throw new \InvalidArgumentException("{$param}不能小于{$min}");
        }
        
        if ($max !== null && $value > $max) {
            throw new \InvalidArgumentException("{$param}不能大于{$max}");
        }
    }
}