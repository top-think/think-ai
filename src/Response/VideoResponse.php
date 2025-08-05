<?php

namespace think\ai\Response;

class VideoResponse extends BaseResponse
{
    /**
     * 获取视频 URL
     */
    public function getUrl(): string
    {
        return $this->data['url'] ?? $this->data['video_url'] ?? '';
    }
    
    /**
     * 获取视频 ID
     */
    public function getVideoId(): string
    {
        return $this->data['video_id'] ?? $this->data['id'] ?? '';
    }
    
    /**
     * 获取视频状态
     */
    public function getStatus(): string
    {
        return $this->data['status'] ?? '';
    }
    
    /**
     * 检查视频是否已完成
     */
    public function isCompleted(): bool
    {
        return in_array($this->getStatus(), ['completed', 'success', 'done']);
    }
    
    /**
     * 检查视频是否正在处理
     */
    public function isProcessing(): bool
    {
        return in_array($this->getStatus(), ['processing', 'pending', 'in_progress']);
    }
    
    /**
     * 检查视频是否失败
     */
    public function isFailed(): bool
    {
        return in_array($this->getStatus(), ['failed', 'error']);
    }
    
    /**
     * 获取预览图 URL
     */
    public function getThumbnailUrl(): ?string
    {
        return $this->data['thumbnail_url'] ?? $this->data['preview_url'] ?? null;
    }
    
    /**
     * 获取视频时长（秒）
     */
    public function getDuration(): ?float
    {
        return $this->data['duration'] ?? null;
    }
    
    /**
     * 获取视频尺寸
     */
    public function getDimensions(): array
    {
        return [
            'width' => $this->data['width'] ?? null,
            'height' => $this->data['height'] ?? null,
        ];
    }
    
    /**
     * 获取错误信息（如果有）
     */
    public function getErrorMessage(): ?string
    {
        return $this->data['error_message'] ?? $this->data['error'] ?? null;
    }
    
    /**
     * 下载视频到本地
     * 
     * @param string $path 保存路径
     * @return bool 是否下载成功
     */
    public function downloadVideo(string $path): bool
    {
        $url = $this->getUrl();
        if (empty($url)) {
            return false;
        }
        
        $content = file_get_contents($url);
        return file_put_contents($path, $content) !== false;
    }
}