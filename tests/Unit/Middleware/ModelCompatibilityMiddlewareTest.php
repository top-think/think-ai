<?php

namespace think\ai\tests\Unit\Middleware;

use think\ai\Middleware\ModelCompatibilityMiddleware;
use think\ai\tests\TestCase;
use GuzzleHttp\Psr7\Request;

class ModelCompatibilityMiddlewareTest extends TestCase
{
    private $middleware;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = ModelCompatibilityMiddleware::create();
    }
    
    public function testDeepSeekParameterRemoval()
    {
        $capturedOptions = null;
        $handler = function ($request, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return $this->createMockResponse(['success' => true]);
        };
        
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => 'deepseek-chat',
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'logit_bias' => ['50256' => -100],
                'response_format' => ['type' => 'json_object']
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查不支持的参数是否被移除
        $this->assertArrayNotHasKey('logit_bias', $capturedOptions['json']);
        $this->assertArrayNotHasKey('response_format', $capturedOptions['json']);
    }
    
    public function testGLMTemperatureAdjustment()
    {
        $capturedOptions = null;
        $handler = function ($request, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return $this->createMockResponse(['success' => true]);
        };
        
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => 'glm-4',
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'temperature' => 1.5
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查温度是否被调整到范围内
        $this->assertEquals(1.0, $capturedOptions['json']['temperature']);
    }
    
    public function testErnieParameterRenaming()
    {
        $capturedOptions = null;
        $handler = function ($request, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return $this->createMockResponse(['success' => true]);
        };
        
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => 'ernie-bot',
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'max_tokens' => 1000
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查参数名是否被转换
        $this->assertArrayNotHasKey('max_tokens', $capturedOptions['json']);
        $this->assertArrayHasKey('max_output_tokens', $capturedOptions['json']);
        $this->assertEquals(1000, $capturedOptions['json']['max_output_tokens']);
    }
    
    public function testQwenDefaultParameters()
    {
        $capturedOptions = null;
        $handler = function ($request, $options) use (&$capturedOptions) {
            $capturedOptions = $options;
            return $this->createMockResponse(['success' => true]);
        };
        
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => 'qwen-turbo',
                'messages' => [['role' => 'user', 'content' => 'Hello']]
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查是否添加了默认的 top_k 参数
        $this->assertArrayHasKey('top_k', $capturedOptions['json']);
        $this->assertEquals(50, $capturedOptions['json']['top_k']);
    }
    
    public function testNoModelSpecified()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'messages' => [['role' => 'user', 'content' => 'Hello']]
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        
        // 不应该抛出异常
        $this->assertNotNull($wrappedHandler($request, $options));
    }
    
    public function testNonJsonRequest()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'form_params' => ['key' => 'value']
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        
        // 不应该抛出异常
        $this->assertNotNull($wrappedHandler($request, $options));
    }
    
    private function createMockHandler()
    {
        return function ($request, $options) {
            return $this->createMockResponse(['success' => true]);
        };
    }
}