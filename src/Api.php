<?php

namespace think\ai;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use think\ai\Exception\AuthenticationException;
use think\ai\Exception\ContentFilterException;
use think\ai\Exception\InvalidModelException;
use think\ai\Exception\NetworkException;
use think\ai\Exception\QuotaExceededException;
use think\ai\Exception\RateLimitException;

abstract class Api
{
    public function __construct(protected Client $client)
    {
    }

    protected function request($method, $uri, $options = [])
    {
        try {
            $client = $this->client->createHttpClient();
            $response = $client->request($method, $uri, $options);
        } catch (ConnectException $e) {
            throw new NetworkException(
                '网络连接失败: ' . $e->getMessage(),
                true,
                0,
                $e
            );
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $content = $response->getBody()->getContents();
                $result = $content ? json_decode($content, true) : null;
                
                $this->handleErrorResponse($statusCode, $result, $content);
            }
            throw new NetworkException(
                '请求失败: ' . $e->getMessage(),
                false,
                0,
                $e
            );
        }

        $contentType = $response->getHeaderLine('Content-Type');

        if (str_starts_with($contentType, 'text/event-stream')) {
            return $response->getBody();
        }

        $statusCode = $response->getStatusCode();
        $isOk       = $statusCode >= 200 && $statusCode < 300;
        $content    = $response->getBody()->getContents();
        $result     = $content ? json_decode($content, true) : null;

        if (!$isOk) {
            $this->handleErrorResponse($statusCode, $result, $content);
        }

        return $result;
    }
    
    /**
     * 处理错误响应
     */
    protected function handleErrorResponse(int $statusCode, ?array $result, string $rawContent): void
    {
        $message = $result['message'] ?? $result['error']['message'] ?? 'Unknown error';
        $errorType = $result['error']['type'] ?? '';
        $errorCode = $result['error']['code'] ?? '';
        
        // 处理特定的错误类型
        switch ($statusCode) {
            case 401:
                throw new AuthenticationException($message);
                
            case 429:
                // 检查是否是速率限制还是配额超限
                if (stripos($message, 'rate') !== false || stripos($errorType, 'rate') !== false) {
                    $retryAfter = $result['retry_after'] ?? 60;
                    throw new RateLimitException($message, $retryAfter);
                } else {
                    throw new QuotaExceededException('api', null, null, $message);
                }
                
            case 400:
                // 检查是否是模型错误
                if (stripos($message, 'model') !== false || $errorCode === 'invalid_model') {
                    $model = $result['error']['param'] ?? 'unknown';
                    throw new InvalidModelException($model);
                }
                // 检查是否是内容过滤
                if (stripos($message, 'content') !== false || stripos($message, 'filter') !== false) {
                    throw new ContentFilterException('content', null, $message);
                }
                break;
                
            case 422:
                // 验证错误，返回原始内容
                throw new Exception($rawContent, 422);
        }
        
        // 默认异常
        throw new Exception($message, $statusCode);
    }
}
