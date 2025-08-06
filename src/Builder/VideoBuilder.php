<?php

namespace think\ai\Builder;

use think\ai\Client;

class VideoBuilder extends BaseBuilder
{
    
    /**
     * 设置提示词
     */
    public function prompt(string $prompt): self
    {
        $this->params['prompt'] = $prompt;
        return $this;
    }
    
    /**
     * 设置图片URL（用于图片生成视频）
     */
    public function image(string $image): self
    {
        $this->params['image'] = $image;
        return $this;
    }
    
    /**
     * 设置质量
     */
    public function quality(string $quality): self
    {
        $this->params['quality'] = $quality;
        return $this;
    }
    
    /**
     * 设置视频大小
     */
    public function size(string $size): self
    {
        $this->params['size'] = $size;
        return $this;
    }
    
    /**
     * 设置视频风格
     */
    public function style(string $style): self
    {
        $this->params['style'] = $style;
        return $this;
    }
    
    /**
     * 设置视频时长
     */
    public function duration(int $duration): self
    {
        $this->params['duration'] = $duration;
        return $this;
    }
    
    /**
     * 设置帧率
     */
    public function fps(int $fps): self
    {
        $this->params['fps'] = $fps;
        return $this;
    }
    
    
    /**
     * 设置响应格式
     */
    public function responseFormat(string $format): self
    {
        $this->params['response_format'] = $format;
        return $this;
    }
    
    
    /**
     * 生成视频
     */
    public function generate()
    {
        $this->validateRequired(['model']);
        
        if (!isset($this->params['prompt']) && !isset($this->params['image'])) {
            throw new \InvalidArgumentException('必须指定提示词或图片');
        }
        
        return $this->client->video()->generations($this->params);
    }
    
    /**
     * 编辑视频
     */
    public function edit(string $video)
    {
        $this->validateRequired(['model']);
        
        $this->params['video'] = $video;
        
        return $this->client->video()->edits($this->params);
    }
    
    /**
     * 视频变体
     */
    public function variation(string $video)
    {
        $this->validateRequired(['model']);
        
        $this->params['video'] = $video;
        
        return $this->client->video()->variations($this->params);
    }
}