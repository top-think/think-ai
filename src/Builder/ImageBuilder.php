<?php

namespace think\ai\Builder;

use think\ai\Client;
use think\ai\Response\ImageResponse;

class ImageBuilder
{
    protected Client $client;
    protected array $params = [];
    protected string $endpoint = 'generations';
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * 设置提示词
     */
    public function prompt(string $prompt): self
    {
        $this->params['prompt'] = $prompt;
        return $this;
    }
    
    /**
     * 设置模型
     */
    public function model(string $model): self
    {
        $this->params['model'] = $model;
        return $this;
    }
    
    /**
     * 设置图片数量
     */
    public function n(int $n): self
    {
        $this->params['n'] = $n;
        return $this;
    }
    
    /**
     * 设置图片尺寸
     * 
     * @param string $size 例如: "1024x1024", "1792x1024", "1024x1792"
     */
    public function size(string $size): self
    {
        $this->params['size'] = $size;
        return $this;
    }
    
    /**
     * 设置质量
     * 
     * @param string $quality "standard" 或 "hd"
     */
    public function quality(string $quality): self
    {
        $this->params['quality'] = $quality;
        return $this;
    }
    
    /**
     * 设置风格
     * 
     * @param string $style "vivid" 或 "natural"
     */
    public function style(string $style): self
    {
        $this->params['style'] = $style;
        return $this;
    }
    
    /**
     * 设置响应格式
     * 
     * @param string $format "url" 或 "b64_json"
     */
    public function responseFormat(string $format): self
    {
        $this->params['response_format'] = $format;
        return $this;
    }
    
    /**
     * 设置用户标识
     */
    public function user(string $user): self
    {
        $this->params['user'] = $user;
        return $this;
    }
    
    /**
     * 设置参考图片（用于编辑、扩展等）
     */
    public function image(string $image): self
    {
        $this->params['image'] = $image;
        return $this;
    }
    
    /**
     * 设置蒙版图片（用于编辑）
     */
    public function mask(string $mask): self
    {
        $this->params['mask'] = $mask;
        $this->endpoint = 'edit';
        return $this;
    }
    
    /**
     * 设置负面提示词
     */
    public function negativePrompt(string $negativePrompt): self
    {
        $this->params['negative_prompt'] = $negativePrompt;
        return $this;
    }
    
    /**
     * 切换到编辑模式
     */
    public function edit(): self
    {
        $this->endpoint = 'edit';
        return $this;
    }
    
    /**
     * 切换到涂抹编辑模式
     */
    public function inpainting(): self
    {
        $this->endpoint = 'inpainting';
        return $this;
    }
    
    /**
     * 切换到扩展模式
     */
    public function outpainting(): self
    {
        $this->endpoint = 'outpainting';
        return $this;
    }
    
    /**
     * 切换到放大模式
     */
    public function upscale(): self
    {
        $this->endpoint = 'upscale';
        return $this;
    }
    
    /**
     * 切换到海报模式
     */
    public function poster(): self
    {
        $this->endpoint = 'poster';
        return $this;
    }
    
    /**
     * 设置海报文本
     */
    public function text(string $text): self
    {
        $this->params['text'] = $text;
        return $this;
    }
    
    /**
     * 设置种子值（用于可重复的生成）
     */
    public function seed(int $seed): self
    {
        $this->params['seed'] = $seed;
        return $this;
    }
    
    /**
     * 设置引导比例
     */
    public function guidanceScale(float $scale): self
    {
        $this->params['guidance_scale'] = $scale;
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
     * 生成图片
     * 
     * @return ImageResponse
     */
    public function generate(): ImageResponse
    {
        if (!isset($this->params['prompt']) && $this->endpoint === 'generations') {
            throw new \InvalidArgumentException('必须设置提示词');
        }
        
        $images = $this->client->images();
        
        $result = match($this->endpoint) {
            'generations' => $images->generations($this->params),
            'edit' => $images->edit($this->params),
            'inpainting' => $images->inpainting($this->params),
            'outpainting' => $images->outpainting($this->params),
            'upscale' => $images->upscale($this->params),
            'poster' => $images->poster($this->params),
            default => throw new \InvalidArgumentException("未知的端点: {$this->endpoint}")
        };
        
        return new ImageResponse($result);
    }
    
    /**
     * 快捷方法：生成并保存图片
     * 
     * @param string $path 保存路径
     * @return bool 是否保存成功
     */
    public function generateAndSave(string $path): bool
    {
        $response = $this->generate();
        return $response->saveImage($path);
    }
    
    /**
     * 快捷方法：生成并获取 URL
     * 
     * @return string 图片 URL
     */
    public function generateAndGetUrl(): string
    {
        $response = $this->generate();
        return $response->getUrl();
    }
}