<?php

namespace think\ai\Enum;

/**
 * 图像尺寸枚举
 */
class ImageSize
{
    // DALL-E 2 尺寸
    const SMALL = '256x256';
    const MEDIUM = '512x512';
    const LARGE = '1024x1024';
    
    // DALL-E 3 尺寸
    const SQUARE_HD = '1024x1024';
    const PORTRAIT = '1024x1792';
    const LANDSCAPE = '1792x1024';
    
    // 自定义尺寸
    const WIDE_SMALL = '512x256';
    const WIDE_MEDIUM = '1024x512';
    const WIDE_LARGE = '2048x1024';
    
    const TALL_SMALL = '256x512';
    const TALL_MEDIUM = '512x1024';
    const TALL_LARGE = '1024x2048';
    
    /**
     * 获取 DALL-E 2 支持的尺寸
     */
    public static function getDallE2Sizes(): array
    {
        return [
            self::SMALL,
            self::MEDIUM,
            self::LARGE,
        ];
    }
    
    /**
     * 获取 DALL-E 3 支持的尺寸
     */
    public static function getDallE3Sizes(): array
    {
        return [
            self::SQUARE_HD,
            self::PORTRAIT,
            self::LANDSCAPE,
        ];
    }
    
    /**
     * 根据模型获取支持的尺寸
     */
    public static function getSizesForModel(string $model): array
    {
        switch ($model) {
            case Model::DALL_E_2:
                return self::getDallE2Sizes();
            case Model::DALL_E_3:
                return self::getDallE3Sizes();
            default:
                return array_merge(
                    self::getDallE2Sizes(),
                    self::getDallE3Sizes()
                );
        }
    }
    
    /**
     * 验证尺寸是否有效
     */
    public static function isValid(string $size): bool
    {
        $reflection = new \ReflectionClass(self::class);
        return in_array($size, $reflection->getConstants());
    }
    
    /**
     * 从宽高创建尺寸字符串
     */
    public static function fromDimensions(int $width, int $height): string
    {
        return "{$width}x{$height}";
    }
    
    /**
     * 解析尺寸字符串
     */
    public static function parseDimensions(string $size): array
    {
        $parts = explode('x', $size);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("无效的尺寸格式: {$size}");
        }
        
        return [
            'width' => (int) $parts[0],
            'height' => (int) $parts[1],
        ];
    }
}