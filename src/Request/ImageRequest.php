<?php

namespace think\ai\Request;

use think\ai\Enum\ImageSize;
use think\ai\Enum\Model;

class ImageRequest
{
    protected string $prompt;
    protected string $model = Model::DALL_E_3;
    protected int $n = 1;
    protected string $size = ImageSize::SQUARE_HD;
    protected string $quality = 'standard';
    protected string $style = 'vivid';
    protected string $responseFormat = 'url';
    protected ?string $user = null;
    
    /**
     * 创建新的图像请求
     */
    public static function create(string $prompt): self
    {
        $request = new self();
        $request->prompt = $prompt;
        return $request;
    }
    
    /**
     * 设置提示词
     */
    public function prompt(string $prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }
    
    /**
     * 设置模型
     */
    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * 设置生成数量
     */
    public function n(int $n): self
    {
        if ($n < 1 || $n > 10) {
            throw new \InvalidArgumentException('生成数量必须在 1 到 10 之间');
        }
        
        $this->n = $n;
        return $this;
    }
    
    /**
     * 设置图片尺寸
     */
    public function size(string $size): self
    {
        // 验证尺寸是否适用于当前模型
        $validSizes = ImageSize::getSizesForModel($this->model);
        if (!empty($validSizes) && !in_array($size, $validSizes)) {
            throw new \InvalidArgumentException(
                "模型 {$this->model} 不支持尺寸 {$size}。支持的尺寸: " . implode(', ', $validSizes)
            );
        }
        
        $this->size = $size;
        return $this;
    }
    
    /**
     * 设置质量（标准或高清）
     */
    public function quality(string $quality): self
    {
        if (!in_array($quality, ['standard', 'hd'])) {
            throw new \InvalidArgumentException('质量必须是 "standard" 或 "hd"');
        }
        
        $this->quality = $quality;
        return $this;
    }
    
    /**
     * 设置高清质量
     */
    public function hd(): self
    {
        return $this->quality('hd');
    }
    
    /**
     * 设置风格
     */
    public function style(string $style): self
    {
        if (!in_array($style, ['vivid', 'natural'])) {
            throw new \InvalidArgumentException('风格必须是 "vivid" 或 "natural"');
        }
        
        $this->style = $style;
        return $this;
    }
    
    /**
     * 设置自然风格
     */
    public function natural(): self
    {
        return $this->style('natural');
    }
    
    /**
     * 设置生动风格
     */
    public function vivid(): self
    {
        return $this->style('vivid');
    }
    
    /**
     * 设置响应格式
     */
    public function responseFormat(string $format): self
    {
        if (!in_array($format, ['url', 'b64_json'])) {
            throw new \InvalidArgumentException('响应格式必须是 "url" 或 "b64_json"');
        }
        
        $this->responseFormat = $format;
        return $this;
    }
    
    /**
     * 设置返回 Base64 格式
     */
    public function base64(): self
    {
        return $this->responseFormat('b64_json');
    }
    
    /**
     * 设置返回 URL 格式
     */
    public function url(): self
    {
        return $this->responseFormat('url');
    }
    
    /**
     * 设置用户标识
     */
    public function user(string $user): self
    {
        $this->user = $user;
        return $this;
    }
    
    /**
     * 使用预设：方形图片
     */
    public function square(): self
    {
        return $this->size(ImageSize::SQUARE_HD);
    }
    
    /**
     * 使用预设：横向图片
     */
    public function landscape(): self
    {
        return $this->size(ImageSize::LANDSCAPE);
    }
    
    /**
     * 使用预设：纵向图片
     */
    public function portrait(): self
    {
        return $this->size(ImageSize::PORTRAIT);
    }
    
    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        if (empty($this->prompt)) {
            throw new \InvalidArgumentException('提示词不能为空');
        }
        
        $params = [
            'prompt' => $this->prompt,
            'model' => $this->model,
            'n' => $this->n,
            'size' => $this->size,
            'quality' => $this->quality,
            'style' => $this->style,
            'response_format' => $this->responseFormat,
        ];
        
        if ($this->user !== null) {
            $params['user'] = $this->user;
        }
        
        return $params;
    }
}