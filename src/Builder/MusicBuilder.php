<?php

namespace think\ai\Builder;

use think\ai\Client;

class MusicBuilder extends BaseBuilder
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
     * 设置音乐风格
     */
    public function style(string $style): self
    {
        $this->params['style'] = $style;
        return $this;
    }
    
    /**
     * 设置音乐流派
     */
    public function genre(string $genre): self
    {
        $this->params['genre'] = $genre;
        return $this;
    }
    
    /**
     * 设置乐器
     */
    public function instruments(array $instruments): self
    {
        $this->params['instruments'] = $instruments;
        return $this;
    }
    
    /**
     * 设置节奏
     */
    public function tempo(int $tempo): self
    {
        $this->params['tempo'] = $tempo;
        return $this;
    }
    
    /**
     * 设置音调
     */
    public function key(string $key): self
    {
        $this->params['key'] = $key;
        return $this;
    }
    
    /**
     * 设置时长（秒）
     */
    public function duration(int $duration): self
    {
        $this->params['duration'] = $duration;
        return $this;
    }
    
    /**
     * 设置情绪
     */
    public function mood(string $mood): self
    {
        $this->params['mood'] = $mood;
        return $this;
    }
    
    /**
     * 设置音质
     */
    public function quality(string $quality): self
    {
        $this->params['quality'] = $quality;
        return $this;
    }
    
    /**
     * 设置采样率
     */
    public function sampleRate(int $sampleRate): self
    {
        $this->params['sample_rate'] = $sampleRate;
        return $this;
    }
    
    /**
     * 设置音频格式
     */
    public function format(string $format): self
    {
        $this->params['format'] = $format;
        return $this;
    }
    
    /**
     * 设置歌词
     */
    public function lyrics(string $lyrics): self
    {
        $this->params['lyrics'] = $lyrics;
        return $this;
    }
    
    /**
     * 设置参考音频
     */
    public function reference(string $referenceUrl): self
    {
        $this->params['reference'] = $referenceUrl;
        return $this;
    }
    
    
    
    /**
     * 生成音乐
     */
    public function generate()
    {
        $this->validateRequired(['model', 'prompt']);
        
        return $this->client->music()->generations($this->params);
    }
    
    /**
     * 编辑音乐
     */
    public function edit(string $music)
    {
        $this->validateRequired(['model']);
        
        $this->params['music'] = $music;
        
        return $this->client->music()->edits($this->params);
    }
    
    /**
     * 音乐变体
     */
    public function variation(string $music)
    {
        $this->validateRequired(['model']);
        
        $this->params['music'] = $music;
        
        return $this->client->music()->variations($this->params);
    }
    
    /**
     * 混音
     */
    public function remix(array $tracks)
    {
        $this->validateRequired(['model']);
        
        $this->params['tracks'] = $tracks;
        
        return $this->client->music()->remix($this->params);
    }
}