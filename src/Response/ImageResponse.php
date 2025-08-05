<?php

namespace think\ai\Response;

class ImageResponse extends BaseResponse
{
    /**
     * 获取所有图片
     */
    public function getImages(): array
    {
        return $this->data['data'] ?? [];
    }
    
    /**
     * 获取第一张图片的 URL
     */
    public function getUrl(): string
    {
        return $this->data['data'][0]['url'] ?? '';
    }
    
    /**
     * 获取所有图片的 URL
     */
    public function getUrls(): array
    {
        $urls = [];
        foreach ($this->getImages() as $image) {
            if (isset($image['url'])) {
                $urls[] = $image['url'];
            }
        }
        return $urls;
    }
    
    /**
     * 获取第一张图片的 Base64 数据
     */
    public function getBase64(): string
    {
        return $this->data['data'][0]['b64_json'] ?? '';
    }
    
    /**
     * 获取所有图片的 Base64 数据
     */
    public function getAllBase64(): array
    {
        $base64Data = [];
        foreach ($this->getImages() as $image) {
            if (isset($image['b64_json'])) {
                $base64Data[] = $image['b64_json'];
            }
        }
        return $base64Data;
    }
    
    /**
     * 保存图片到文件
     * 
     * @param string $path 保存路径
     * @param int $index 图片索引（默认保存第一张）
     * @return bool 是否保存成功
     */
    public function saveImage(string $path, int $index = 0): bool
    {
        $images = $this->getImages();
        if (!isset($images[$index])) {
            return false;
        }
        
        $image = $images[$index];
        
        // 如果是 URL，下载图片
        if (isset($image['url'])) {
            $content = file_get_contents($image['url']);
            return file_put_contents($path, $content) !== false;
        }
        
        // 如果是 Base64，解码保存
        if (isset($image['b64_json'])) {
            $content = base64_decode($image['b64_json']);
            return file_put_contents($path, $content) !== false;
        }
        
        return false;
    }
    
    /**
     * 保存所有图片
     * 
     * @param string $directory 保存目录
     * @param string $prefix 文件名前缀
     * @return array 保存的文件路径列表
     */
    public function saveAllImages(string $directory, string $prefix = 'image'): array
    {
        $savedPaths = [];
        $images = $this->getImages();
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        foreach ($images as $index => $image) {
            $filename = sprintf('%s_%d_%s.png', $prefix, $index + 1, uniqid());
            $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;
            
            if ($this->saveImage($path, $index)) {
                $savedPaths[] = $path;
            }
        }
        
        return $savedPaths;
    }
    
    /**
     * 获取图片数量
     */
    public function getImageCount(): int
    {
        return count($this->getImages());
    }
}