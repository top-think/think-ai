<?php

namespace think\ai\Response;

class AudioResponse extends BaseResponse
{
    /**
     * 获取音频内容（用于语音合成）
     */
    public function getAudioContent(): ?string
    {
        return $this->data['audio'] ?? null;
    }
    
    /**
     * 获取转录文本（用于语音识别）
     */
    public function getText(): string
    {
        return $this->data['text'] ?? '';
    }
    
    /**
     * 获取语言代码
     */
    public function getLanguage(): ?string
    {
        return $this->data['language'] ?? null;
    }
    
    /**
     * 获取持续时间（秒）
     */
    public function getDuration(): ?float
    {
        return $this->data['duration'] ?? null;
    }
    
    /**
     * 保存音频文件
     * 
     * @param string $path 保存路径
     * @return bool 是否保存成功
     */
    public function saveAudio(string $path): bool
    {
        $audioContent = $this->getAudioContent();
        if (!$audioContent) {
            return false;
        }
        
        // 如果是 base64 编码，先解码
        if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $audioContent)) {
            $audioContent = base64_decode($audioContent);
        }
        
        return file_put_contents($path, $audioContent) !== false;
    }
    
    /**
     * 获取分段信息（如果有）
     */
    public function getSegments(): array
    {
        return $this->data['segments'] ?? [];
    }
}