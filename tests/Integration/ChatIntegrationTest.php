<?php

namespace think\ai\tests\Integration;

use think\ai\Client;
use think\ai\Enum\Model;
use think\ai\Response\ChatResponse;
use think\ai\Response\StreamResponse;
use think\ai\Exception\RateLimitException;
use think\ai\tests\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

class ChatIntegrationTest extends TestCase
{
    /**
     * 测试完整的聊天流程
     */
    public function testCompleteConversation()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'id' => 'chatcmpl-123',
                'model' => 'gpt-3.5-turbo',
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => '你好！有什么可以帮助你的吗？'
                        ],
                        'finish_reason' => 'stop'
                    ]
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 15,
                    'total_tokens' => 25
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        // 使用构建器模式
        $response = $client->chatBuilder()
            ->model(Model::GPT_35_TURBO)
            ->system('你是一个友好的助手')
            ->user('你好')
            ->temperature(0.7)
            ->maxTokens(100)
            ->stream(false)
            ->send();
        
        $this->assertInstanceOf(ChatResponse::class, $response);
        $this->assertEquals('你好！有什么可以帮助你的吗？', $response->getContent());
        $this->assertEquals(25, $response->getTotalTokens());
    }
    
    /**
     * 测试流式聊天
     */
    public function testStreamChat()
    {
        $chunks = [
            ['choices' => [['delta' => ['content' => '你好'], 'index' => 0]]],
            ['choices' => [['delta' => ['content' => '！'], 'index' => 0]]],
            ['choices' => [['delta' => ['content' => '有什么'], 'index' => 0]]],
            ['choices' => [['delta' => ['content' => '可以帮助你'], 'index' => 0]]],
            ['choices' => [['delta' => ['content' => '的吗？'], 'index' => 0]]]
        ];
        
        $mockHandler = new MockHandler([
            $this->createMockStreamResponse($chunks)
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $stream = $client->chatBuilder()
            ->model(Model::GPT_35_TURBO)
            ->user('你好')
            ->stream(true)
            ->send();
        
        $this->assertInstanceOf(StreamResponse::class, $stream);
        
        $content = '';
        $stream->onChunk(function($chunk) use (&$content) {
            $content .= $chunk->getContent();
        });
        
        $this->assertEquals('你好！有什么可以帮助你的吗？', $content);
    }
    
    /**
     * 测试使用回调语法
     */
    public function testCallbackSyntax()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Test response']]
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $builder = $client->chat(function($chat) {
            $chat->model(Model::GPT_4)
                ->user('Test message')
                ->temperature(0.5);
        });
        
        $this->assertInstanceOf(\think\ai\Builder\ChatBuilder::class, $builder);
        $params = $builder->getParams();
        $this->assertEquals(Model::GPT_4, $params['model']);
        $this->assertEquals(0.5, $params['temperature']);
    }
    
    /**
     * 测试快捷方法
     */
    public function testAskMethod()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => '这是一个测试响应']]
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        // 设置默认模型
        $client->setDefaultModel('chat', 'gpt-4.1-mini');
        
        $response = $client->ask('测试消息');
        $this->assertEquals('这是一个测试响应', $response);
    }
    
    /**
     * 测试错误处理
     */
    public function testErrorHandling()
    {
        $mockHandler = new MockHandler([
            new Response(429, [], json_encode([
                'error' => [
                    'message' => 'Rate limit exceeded',
                    'type' => 'rate_limit_error'
                ],
                'retry_after' => 60
            ]))
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $this->expectException(RateLimitException::class);
        
        $client->chatBuilder()
            ->model(Model::GPT_35_TURBO)
            ->user('Test')
            ->send();
    }
    
    /**
     * 测试中间件集成
     */
    public function testMiddlewareIntegration()
    {
        $requestCount = 0;
        $middleware = function($handler) use (&$requestCount) {
            return function($request, $options) use ($handler, &$requestCount) {
                $requestCount++;
                return $handler($request, $options);
            };
        };
        
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Response']]
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        $client->addMiddleware($middleware);
        
        $client->ask('Test');
        
        $this->assertEquals(1, $requestCount);
    }
    
    /**
     * 测试多轮对话
     */
    public function testMultiTurnConversation()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => '北京今天晴天，温度25度。']]
                ]
            ]),
            $this->createMockResponse([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => '上海今天多云，温度22度。']]
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $chatBuilder = $client->chatBuilder()
            ->model(Model::GPT_35_TURBO)
            ->system('你是一个天气助手');
        
        // 第一轮
        $response1 = $chatBuilder->user('北京天气怎么样？')->send();
        $this->assertStringContainsString('北京', $response1->getContent());
        
        // 第二轮
        $chatBuilder->assistant($response1->getContent());
        $response2 = $chatBuilder->user('那上海呢？')->send();
        $this->assertStringContainsString('上海', $response2->getContent());
        
        // 验证消息历史
        $params = $chatBuilder->getParams();
        $this->assertCount(4, $params['messages']); // system + user + assistant + user
    }
}