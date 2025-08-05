<?php

namespace think\ai\tests\Unit\Builder;

use think\ai\Builder\ChatBuilder;
use think\ai\Client;
use think\ai\Enum\Model;
use think\ai\tests\TestCase;
use Mockery;

class ChatBuilderTest extends TestCase
{
    private $mockClient;
    private $builder;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = Mockery::mock(Client::class);
        $this->builder = new ChatBuilder($this->mockClient);
    }
    
    public function testModel()
    {
        $result = $this->builder->model(Model::GPT_4);
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals(Model::GPT_4, $params['model']);
    }
    
    public function testSystem()
    {
        $result = $this->builder->system('You are a helpful assistant');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertCount(1, $params['messages']);
        $this->assertEquals('system', $params['messages'][0]['role']);
        $this->assertEquals('You are a helpful assistant', $params['messages'][0]['content']);
    }
    
    public function testUser()
    {
        $result = $this->builder->user('Hello');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertCount(1, $params['messages']);
        $this->assertEquals('user', $params['messages'][0]['role']);
        $this->assertEquals('Hello', $params['messages'][0]['content']);
    }
    
    public function testAssistant()
    {
        $result = $this->builder->assistant('Hi there!');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertCount(1, $params['messages']);
        $this->assertEquals('assistant', $params['messages'][0]['role']);
        $this->assertEquals('Hi there!', $params['messages'][0]['content']);
    }
    
    public function testMultipleMessages()
    {
        $this->builder
            ->system('System message')
            ->user('User message')
            ->assistant('Assistant message');
        
        $params = $this->builder->getParams();
        $this->assertCount(3, $params['messages']);
        $this->assertEquals('system', $params['messages'][0]['role']);
        $this->assertEquals('user', $params['messages'][1]['role']);
        $this->assertEquals('assistant', $params['messages'][2]['role']);
    }
    
    public function testTemperature()
    {
        $result = $this->builder->temperature(0.7);
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals(0.7, $params['temperature']);
    }
    
    public function testMaxTokens()
    {
        $result = $this->builder->maxTokens(1000);
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals(1000, $params['max_tokens']);
    }
    
    public function testStream()
    {
        $result = $this->builder->stream(false);
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertFalse($params['stream']);
    }
    
    public function testModeration()
    {
        $result = $this->builder->moderation(false);
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertFalse($params['moderation']);
    }
    
    public function testChainedMethods()
    {
        $this->builder
            ->model(Model::GPT_35_TURBO)
            ->system('You are a poet')
            ->user('Write a haiku')
            ->temperature(0.9)
            ->maxTokens(50)
            ->topP(0.95)
            ->stream(true);
        
        $params = $this->builder->getParams();
        
        $this->assertEquals(Model::GPT_35_TURBO, $params['model']);
        $this->assertEquals(0.9, $params['temperature']);
        $this->assertEquals(50, $params['max_tokens']);
        $this->assertEquals(0.95, $params['top_p']);
        $this->assertTrue($params['stream']);
        $this->assertCount(2, $params['messages']);
    }
    
    public function testClearMessages()
    {
        $this->builder
            ->user('Message 1')
            ->user('Message 2')
            ->clearMessages()
            ->user('Message 3');
        
        $params = $this->builder->getParams();
        $this->assertCount(1, $params['messages']);
        $this->assertEquals('Message 3', $params['messages'][0]['content']);
    }
    
    public function testSendWithoutMessages()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('消息列表不能为空');
        
        $this->builder->model(Model::GPT_4)->send();
    }
    
    public function testSendWithoutModel()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('必须指定模型');
        
        $this->builder->user('Hello')->send();
    }
    
    public function testTools()
    {
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_weather',
                    'description' => 'Get weather information'
                ]
            ]
        ];
        
        $result = $this->builder->tools($tools);
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals($tools, $params['tools']);
    }
    
    public function testDefaultParameters()
    {
        $params = $this->builder->getParams();
        
        $this->assertTrue($params['stream']);
        $this->assertTrue($params['moderation']);
        $this->assertArrayNotHasKey('model', $params);
        $this->assertArrayNotHasKey('messages', $params);
    }
}