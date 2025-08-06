<?php

namespace think\ai\Builder;

use think\ai\Client;

class AudioBuilder extends BaseBuilder
{
    
    /**
     * 设置输入文本（用于语音合成）
     */
    public function input(string $input): self
    {
        $this->params['input'] = $input;
        return $this;
    }
    
    /**
     * 设置音频文件（用于转录或翻译）
     */
    public function file(string $file): self
    {
        $this->params['file'] = $file;
        return $this;
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
     * 设置语音
     */
    public function voice(string $voice): self
    {
        $this->params['voice'] = $voice;
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
     * 设置速度（用于语音合成）
     */
    public function speed(float $speed): self
    {
        $this->params['speed'] = $speed;
        return $this;
    }
    
    /**
     * 设置语言
     */
    public function language(string $language): self
    {
        $this->params['language'] = $language;
        return $this;
    }
    
    /**
     * 设置温度
     */
    public function temperature(float $temperature): self
    {
        $this->params['temperature'] = $temperature;
        return $this;
    }
    
    /**
     * 设置时间戳粒度（用于转录）
     */
    public function timestampGranularities(array $granularities): self
    {
        $this->params['timestamp_granularities'] = $granularities;
        return $this;
    }
    
    
    /**
     * 生成语音
     */
    public function speech()
    {
        $this->validateRequired(['model', 'input', 'voice']);
        
        return $this->client->audio()->speech($this->params);
    }
    
    /**
     * 转录音频
     */
    public function transcription()
    {
        $this->validateRequired(['file', 'model']);
        
        return $this->client->audio()->transcriptions($this->params);
    }
    
    /**
     * 翻译音频
     */
    public function translation()
    {
        $this->validateRequired(['file', 'model']);
        
        return $this->client->audio()->translations($this->params);
    }
}