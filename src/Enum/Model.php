<?php

namespace think\ai\Enum;

/**
 * AI 模型枚举
 */
class Model
{
    // GPT 系列
    const GPT_35_TURBO = 'gpt-3.5-turbo';
    const GPT_35_TURBO_16K = 'gpt-3.5-turbo-16k';
    const GPT_4 = 'gpt-4';
    const GPT_4_32K = 'gpt-4-32k';
    const GPT_4_TURBO = 'gpt-4-turbo';
    const GPT_4_VISION = 'gpt-4-vision-preview';
    
    // DeepSeek 系列
    const DEEPSEEK_CHAT = 'deepseek-chat';
    const DEEPSEEK_CODER = 'deepseek-coder';
    
    // 智谱系列
    const GLM_4 = 'glm-4';
    const GLM_4_AIR = 'glm-4-air';
    const GLM_4_FLASH = 'glm-4-flash';
    
    // 通义千问系列
    const QWEN_TURBO = 'qwen-turbo';
    const QWEN_PLUS = 'qwen-plus';
    const QWEN_MAX = 'qwen-max';
    
    // 文心一言系列
    const ERNIE_BOT = 'ernie-bot';
    const ERNIE_BOT_TURBO = 'ernie-bot-turbo';
    const ERNIE_BOT_4 = 'ernie-bot-4';
    
    // 图像生成模型
    const DALL_E_2 = 'dall-e-2';
    const DALL_E_3 = 'dall-e-3';
    const STABLE_DIFFUSION = 'stable-diffusion';
    const MIDJOURNEY = 'midjourney';
    
    /**
     * 获取所有聊天模型
     */
    public static function getChatModels(): array
    {
        return [
            self::GPT_35_TURBO,
            self::GPT_35_TURBO_16K,
            self::GPT_4,
            self::GPT_4_32K,
            self::GPT_4_TURBO,
            self::GPT_4_VISION,
            self::DEEPSEEK_CHAT,
            self::DEEPSEEK_CODER,
            self::GLM_4,
            self::GLM_4_AIR,
            self::GLM_4_FLASH,
            self::QWEN_TURBO,
            self::QWEN_PLUS,
            self::QWEN_MAX,
            self::ERNIE_BOT,
            self::ERNIE_BOT_TURBO,
            self::ERNIE_BOT_4,
        ];
    }
    
    /**
     * 获取所有图像模型
     */
    public static function getImageModels(): array
    {
        return [
            self::DALL_E_2,
            self::DALL_E_3,
            self::STABLE_DIFFUSION,
            self::MIDJOURNEY,
        ];
    }
    
    /**
     * 检查是否是有效的模型
     */
    public static function isValid(string $model): bool
    {
        return in_array($model, array_merge(
            self::getChatModels(),
            self::getImageModels()
        ));
    }
    
    /**
     * 检查是否支持视觉能力
     */
    public static function supportsVision(string $model): bool
    {
        return in_array($model, [
            self::GPT_4_VISION,
            self::GLM_4,
        ]);
    }
    
    /**
     * 获取模型的最大上下文长度
     */
    public static function getContextLength(string $model): int
    {
        $lengths = [
            self::GPT_35_TURBO => 4096,
            self::GPT_35_TURBO_16K => 16384,
            self::GPT_4 => 8192,
            self::GPT_4_32K => 32768,
            self::GPT_4_TURBO => 128000,
            self::GPT_4_VISION => 128000,
            self::DEEPSEEK_CHAT => 32768,
            self::DEEPSEEK_CODER => 16384,
            self::GLM_4 => 128000,
            self::GLM_4_AIR => 8192,
            self::GLM_4_FLASH => 16384,
            self::QWEN_TURBO => 8192,
            self::QWEN_PLUS => 32768,
            self::QWEN_MAX => 32768,
        ];
        
        return $lengths[$model] ?? 4096;
    }
}