<?php

namespace think\ai\tests\Unit;

use think\ai\Client;
use think\ai\MultiTurnChatManager;
use think\ai\Response\ChatResponse;
use think\ai\tests\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class MultiTurnChatManagerTest extends TestCase
{
    protected MultiTurnChatManager $manager;
    protected MockHandler $mockHandler;
    
    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client('test-token', $handlerStack);
        $this->manager = new MultiTurnChatManager($client);
    }
    
    /**
     * 测试基本的多轮对话
     */
    public function testBasicMultiTurnConversation()
    {
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello!'
                    ]
                ]
            ]
        ]));
        
        $response = $this->manager->chat()
            ->model('gpt-3.5-turbo')
            ->user('Hi')
            ->send();
        
        $this->assertInstanceOf(ChatResponse::class, $response);
        $this->assertEquals('Hello!', $response->getContent());
    }
    
    /**
     * 测试工具调用处理
     */
    public function testToolCallExecution()
    {
        // 第一轮：返回工具调用
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'I will calculate that for you.',
                        'tool_calls' => [
                            [
                                'id' => 'call_123',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'calculator',
                                    'arguments' => json_encode(['expression' => '2 + 2'])
                                ]
                            ]
                        ]
                    ],
                    'finish_reason' => 'tool_calls'
                ]
            ]
        ]));
        
        // 第二轮：返回最终结果
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'The result is 4'
                    ]
                ]
            ]
        ]));
        
        // 注册计算器工具
        $this->manager->registerTool('calculator', function($args) {
            return eval('return ' . $args['expression'] . ';');
        });
        
        // 获取 chatBuilder 实例
        $chatBuilder = $this->manager->chat()
            ->model('gpt-4')
            ->user('What is 2 + 2?')
            ->function('calculator', 'Calculate mathematical expressions', [
                'type' => 'object',
                'properties' => [
                    'expression' => [
                        'type' => 'string',
                        'description' => 'The expression to calculate'
                    ]
                ],
                'required' => ['expression']
            ]);
        
        // 发送第一轮请求
        $response = $chatBuilder->send();
        
        // 继续对话处理工具调用
        $finalResponse = $this->manager->continueConversation($response);
        
        $this->assertEquals('The result is 4', $finalResponse->getContent());
        
        // 验证消息历史
        $history = $this->manager->getMessageHistory();
        $this->assertCount(4, $history); // user + assistant (with tool_calls) + tool + assistant (final)
        
        // 验证每条消息
        $this->assertEquals('user', $history[0]['role']);
        $this->assertEquals('What is 2 + 2?', $history[0]['content']);
        
        $this->assertEquals('assistant', $history[1]['role']);
        $this->assertEquals('I will calculate that for you.', $history[1]['content']);
        $this->assertNotEmpty($history[1]['tool_calls']);
        
        $this->assertEquals('tool', $history[2]['role']);
        $this->assertEquals('call_123', $history[2]['tool_call_id']);
        
        $this->assertEquals('assistant', $history[3]['role']);
        $this->assertEquals('The result is 4', $history[3]['content']);
    }
    
    /**
     * 测试多工具调用
     */
    public function testMultipleToolCalls()
    {
        // 第一轮：返回多个工具调用
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'I will get the weather and time for you.',
                        'tool_calls' => [
                            [
                                'id' => 'call_123',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_weather',
                                    'arguments' => json_encode(['city' => 'Beijing'])
                                ]
                            ],
                            [
                                'id' => 'call_456',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_time',
                                    'arguments' => json_encode(['timezone' => 'Asia/Shanghai'])
                                ]
                            ]
                        ]
                    ],
                    'finish_reason' => 'tool_calls'
                ]
            ]
        ]));
        
        // 第二轮：返回最终结果
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'The weather in Beijing is sunny and the time is 14:30.'
                    ]
                ]
            ]
        ]));
        
        // 注册多个工具
        $this->manager->registerTools([
            'get_weather' => function($args) {
                return ['weather' => 'sunny', 'temperature' => 25];
            },
            'get_time' => function($args) {
                return ['time' => '14:30', 'timezone' => $args['timezone']];
            }
        ]);
        
        $response = $this->manager->chat()
            ->model('gpt-4')
            ->user('What is the weather and time in Beijing?')
            ->send();
        
        $finalResponse = $this->manager->continueConversation($response);
        
        $this->assertStringContainsString('weather', $finalResponse->getContent());
        $this->assertStringContainsString('time', $finalResponse->getContent());
    }
    
    /**
     * 测试工具调用错误处理
     */
    public function testToolCallErrorHandling()
    {
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'I will call an unknown tool.',
                        'tool_calls' => [
                            [
                                'id' => 'call_789',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'unknown_tool',
                                    'arguments' => '{}'
                                ]
                            ]
                        ]
                    ],
                    'finish_reason' => 'tool_calls'
                ]
            ]
        ]));
        
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Sorry, I encountered an error while executing the tool.'
                    ]
                ]
            ]
        ]));
        
        $response = $this->manager->chat()
            ->model('gpt-4')
            ->user('Use unknown tool')
            ->send();
        
        $finalResponse = $this->manager->continueConversation($response);
        
        // 应该能够优雅地处理未注册的工具
        $this->assertIsString($finalResponse->getContent());
    }
    
    /**
     * 测试最大轮次限制
     */
    public function testMaxTurnsLimit()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Maximum number of turns');
        
        // 设置最大轮次为 0（这样第一次调用就会抛出异常）
        $this->manager->maxTurns(0);
        
        // 注册一个工具
        $this->manager->registerTool('test_tool', function($args) {
            return 'test result';
        });
        
        // 第一轮：返回工具调用
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'I will call a tool.',
                        'tool_calls' => [
                            [
                                'id' => 'call_123',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'test_tool',
                                    'arguments' => '{}'
                                ]
                            ]
                        ]
                    ],
                    'finish_reason' => 'tool_calls'
                ]
            ]
        ]));
        
        $response = $this->manager->chat()
            ->model('gpt-4')
            ->user('Test')
            ->send();
        
        // 尝试继续对话，应该抛出异常
        $this->manager->continueConversation($response);
    }
    
    /**
     * 测试清空历史
     */
    public function testClearHistory()
    {
        $this->mockHandler->append($this->createMockResponse([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Response'
                    ]
                ]
            ]
        ]));
        
        $this->manager->chat()
            ->user('Hello')
            ->send();
        
        $this->assertNotEmpty($this->manager->getMessageHistory());
        
        $this->manager->clearHistory();
        $this->assertEmpty($this->manager->getMessageHistory());
    }
}