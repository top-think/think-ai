<?php

namespace think\ai\Response;

abstract class BaseResponse
{
    protected array $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * 获取原始响应数据
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * 将响应转换为 JSON 字符串
     */
    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * 判断响应是否成功
     */
    public function isSuccess(): bool
    {
        return !isset($this->data['error']);
    }
    
    /**
     * 获取错误信息（如果有）
     */
    public function getError(): ?string
    {
        return $this->data['error']['message'] ?? null;
    }
    
    /**
     * 魔术方法，允许直接访问响应数据
     */
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }
    
    /**
     * 魔术方法，允许设置响应数据
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 检查响应中是否存在某个键
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
}