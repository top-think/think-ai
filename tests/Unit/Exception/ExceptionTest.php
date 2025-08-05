<?php

namespace think\ai\tests\Unit\Exception;

use think\ai\Exception\RateLimitException;
use think\ai\Exception\InvalidModelException;
use think\ai\Exception\AuthenticationException;
use think\ai\Exception\QuotaExceededException;
use think\ai\Exception\ContentFilterException;
use think\ai\Exception\NetworkException;
use think\ai\tests\TestCase;

class ExceptionTest extends TestCase
{
    public function testRateLimitException()
    {
        $exception = new RateLimitException('Custom message', 60);
        
        $this->assertEquals('Custom message', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
        $this->assertEquals(60, $exception->getRetryAfter());
        $this->assertTrue($exception->canRetry());
        
        // 测试默认消息
        $exception = new RateLimitException('', 30);
        $this->assertEquals('请求频率超限，请在 30 秒后重试', $exception->getMessage());
        
        // 测试无重试时间
        $exception = new RateLimitException('', 0);
        $this->assertEquals('请求频率超限', $exception->getMessage());
        $this->assertFalse($exception->canRetry());
    }
    
    public function testInvalidModelException()
    {
        $availableModels = ['gpt-3.5-turbo', 'gpt-4'];
        $exception = new InvalidModelException('invalid-model', $availableModels);
        
        $this->assertEquals('无效的模型: invalid-model。可用的模型: gpt-3.5-turbo, gpt-4', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('invalid-model', $exception->getModel());
        $this->assertEquals($availableModels, $exception->getAvailableModels());
        
        // 测试无可用模型列表
        $exception = new InvalidModelException('bad-model');
        $this->assertEquals('无效的模型: bad-model', $exception->getMessage());
        $this->assertEquals([], $exception->getAvailableModels());
    }
    
    public function testAuthenticationException()
    {
        $exception = new AuthenticationException('Invalid token');
        
        $this->assertEquals('Invalid token', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
        
        // 测试默认消息
        $exception = new AuthenticationException();
        $this->assertEquals('认证失败：无效的 API Token', $exception->getMessage());
    }
    
    public function testQuotaExceededException()
    {
        $exception = new QuotaExceededException('requests', 1000, 1200);
        
        $this->assertStringContainsString('Requests 配额超限', $exception->getMessage());
        $this->assertStringContainsString('已使用: 1200/1000', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
        $this->assertEquals('requests', $exception->getQuotaType());
        $this->assertEquals(1000, $exception->getLimit());
        $this->assertEquals(1200, $exception->getUsed());
        $this->assertEquals(0, $exception->getRemaining());
        
        // 测试默认参数
        $exception = new QuotaExceededException();
        $this->assertEquals('Tokens 配额超限', $exception->getMessage());
        $this->assertNull($exception->getLimit());
        $this->assertNull($exception->getUsed());
        $this->assertNull($exception->getRemaining());
        
        // 测试自定义消息
        $exception = new QuotaExceededException('', null, null, 'Custom quota message');
        $this->assertEquals('Custom quota message', $exception->getMessage());
    }
    
    public function testContentFilterException()
    {
        $exception = new ContentFilterException('image', 'violent content');
        
        $this->assertEquals('Image 被安全过滤器拦截', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('image', $exception->getFilterType());
        $this->assertEquals('violent content', $exception->getFlaggedContent());
        
        // 测试默认参数
        $exception = new ContentFilterException();
        $this->assertEquals('内容被安全过滤器拦截', $exception->getMessage());
        $this->assertEquals('content', $exception->getFilterType());
        $this->assertNull($exception->getFlaggedContent());
        
        // 测试自定义消息
        $exception = new ContentFilterException('', null, 'Custom filter message');
        $this->assertEquals('Custom filter message', $exception->getMessage());
    }
    
    public function testNetworkException()
    {
        $exception = new NetworkException('Connection timeout', true);
        
        $this->assertEquals('Connection timeout', $exception->getMessage());
        $this->assertTrue($exception->isRetryable());
        $this->assertEquals(0, $exception->getCode());
        
        // 测试不可重试
        $exception = new NetworkException('Fatal error', false, 500);
        $this->assertFalse($exception->isRetryable());
        $this->assertEquals(500, $exception->getCode());
        
        // 测试默认消息
        $exception = new NetworkException();
        $this->assertEquals('网络连接错误', $exception->getMessage());
        $this->assertTrue($exception->isRetryable());
    }
    
    public function testExceptionHierarchy()
    {
        // 确保所有自定义异常都继承自基础 Exception
        $exceptions = [
            new RateLimitException(),
            new InvalidModelException('test'),
            new AuthenticationException(),
            new QuotaExceededException(),
            new ContentFilterException(),
            new NetworkException(),
        ];
        
        foreach ($exceptions as $exception) {
            $this->assertInstanceOf(\think\ai\Exception::class, $exception);
        }
    }
}