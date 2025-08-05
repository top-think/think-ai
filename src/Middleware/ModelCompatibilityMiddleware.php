<?php

namespace think\ai\Middleware;

use think\ai\Enum\Model;

/**
 * 模型兼容性中间件
 * 自动处理不同模型的特殊需求和参数调整
 */
class ModelCompatibilityMiddleware
{
    /**
     * 创建中间件
     */
    public static function create(): callable
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                // 获取请求体
                if (isset($options['json'])) {
                    $body = &$options['json'];
                    
                    // 处理模型特定的参数调整
                    if (isset($body['model'])) {
                        $model = $body['model'];
                        
                        // 根据不同模型调整参数
                        self::adjustParametersForModel($model, $body);
                    }
                }
                
                return $handler($request, $options);
            };
        };
    }
    
    /**
     * 根据模型调整参数
     */
    protected static function adjustParametersForModel(string $model, array &$params): void
    {
        // GPT-4 Vision 特殊处理
        if ($model === Model::GPT_4_VISION) {
            // 确保消息内容支持图片
            if (isset($params['messages'])) {
                foreach ($params['messages'] as &$message) {
                    if (is_string($message['content'])) {
                        // 如果内容包含图片 URL，转换为正确格式
                        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $message['content'])) {
                            $message['content'] = [
                                ['type' => 'image_url', 'image_url' => ['url' => $message['content']]]
                            ];
                        }
                    }
                }
            }
        }
        
        // DeepSeek 模型参数调整
        if (strpos($model, 'deepseek') !== false) {
            // DeepSeek 不支持某些参数，需要移除
            unset($params['logit_bias']);
            unset($params['response_format']);
        }
        
        // 智谱 GLM 模型参数调整
        if (strpos($model, 'glm') !== false) {
            // GLM 模型对 temperature 范围有特殊要求
            if (isset($params['temperature']) && $params['temperature'] > 1.0) {
                $params['temperature'] = 1.0;
            }
        }
        
        // 文心一言模型参数调整
        if (strpos($model, 'ernie') !== false) {
            // 文心一言使用不同的参数名
            if (isset($params['max_tokens'])) {
                $params['max_output_tokens'] = $params['max_tokens'];
                unset($params['max_tokens']);
            }
        }
        
        // 通义千问模型参数调整
        if (strpos($model, 'qwen') !== false) {
            // 通义千问的特殊参数处理
            if (!isset($params['top_k'])) {
                $params['top_k'] = 50;
            }
        }
        
        // 根据模型的上下文长度限制 max_tokens
        $contextLength = Model::getContextLength($model);
        if (isset($params['max_tokens']) && $params['max_tokens'] > $contextLength) {
            $params['max_tokens'] = min($params['max_tokens'], (int)($contextLength * 0.5));
        }
    }
}