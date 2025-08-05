<?php

namespace think\ai\tests\Unit\Middleware;

use think\ai\Middleware\ModelCompatibilityMiddleware;
use think\ai\Enum\Model;
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
    
    public function testGPT4VisionImageHandling()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => Model::GPT_4_VISION,
                'messages' => [
                    ['role' => 'user', 'content' => 'https://example.com/image.jpg']
                ]
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查图片 URL 是否被正确转换
        $expectedContent = [
            ['type' => 'image_url', 'image_url' => ['url' => 'https://example.com/image.jpg']]
        ];
        $this->assertEquals($expectedContent, $options['json']['messages'][0]['content']);
    }
    
    public function testDeepSeekParameterRemoval()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => Model::DEEPSEEK_CHAT,
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'logit_bias' => ['50256' => -100],
                'response_format' => ['type' => 'json_object']
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查不支持的参数是否被移除
        $this->assertArrayNotHasKey('logit_bias', $options['json']);
        $this->assertArrayNotHasKey('response_format', $options['json']);
    }
    
    public function testGLMTemperatureAdjustment()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => Model::GLM_4,
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'temperature' => 1.5
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查温度是否被调整到范围内
        $this->assertEquals(1.0, $options['json']['temperature']);
    }
    
    public function testErnieParameterRenaming()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => Model::ERNIE_BOT,
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'max_tokens' => 1000
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查参数名是否被转换
        $this->assertArrayNotHasKey('max_tokens', $options['json']);
        $this->assertArrayHasKey('max_output_tokens', $options['json']);
        $this->assertEquals(1000, $options['json']['max_output_tokens']);
    }
    
    public function testQwenDefaultParameters()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => Model::QWEN_TURBO,
                'messages' => [['role' => 'user', 'content' => 'Hello']]
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查是否添加了默认的 top_k 参数
        $this->assertArrayHasKey('top_k', $options['json']);
        $this->assertEquals(50, $options['json']['top_k']);
    }
    
    public function testMaxTokensLimiting()
    {
        $handler = $this->createMockHandler();
        $middleware = $this->middleware;
        $wrappedHandler = $middleware($handler);
        
        $options = [
            'json' => [
                'model' => Model::GPT_35_TURBO,
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'max_tokens' => 10000 // 超过模型限制
            ]
        ];
        
        $request = new Request('POST', 'https://api.example.com');
        $wrappedHandler($request, $options);
        
        // 检查 max_tokens 是否被限制在合理范围内
        $this->assertLessThanOrEqual(2048, $options['json']['max_tokens']); // 4096 * 0.5
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