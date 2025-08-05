<?php

namespace think\ai\tests\Unit\Response;

use think\ai\Response\ChatResponse;
use think\ai\tests\TestCase;

class ChatResponseTest extends TestCase
{
    private function createChatData(): array
    {
        return [
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'created' => 1677652288,
            'model' => 'gpt-3.5-turbo',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => '你好！我是 AI 助手。'
                    ],
                    'finish_reason' => 'stop'
                ]
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30
            ]
        ];
    }
    
    public function testGetContent()
    {
        $response = new ChatResponse($this->createChatData());
        $this->assertEquals('你好！我是 AI 助手。', $response->getContent());
    }
    
    public function testGetMessage()
    {
        $response = new ChatResponse($this->createChatData());
        $message = $response->getMessage();
        
        $this->assertIsArray($message);
        $this->assertEquals('assistant', $message['role']);
        $this->assertEquals('你好！我是 AI 助手。', $message['content']);
    }
    
    public function testGetRole()
    {
        $response = new ChatResponse($this->createChatData());
        $this->assertEquals('assistant', $response->getRole());
    }
    
    public function testGetModel()
    {
        $response = new ChatResponse($this->createChatData());
        $this->assertEquals('gpt-3.5-turbo', $response->getModel());
    }
    
    public function testGetUsage()
    {
        $response = new ChatResponse($this->createChatData());
        $usage = $response->getUsage();
        
        $this->assertIsArray($usage);
        $this->assertEquals(10, $usage['prompt_tokens']);
        $this->assertEquals(20, $usage['completion_tokens']);
        $this->assertEquals(30, $usage['total_tokens']);
    }
    
    public function testGetTokenCounts()
    {
        $response = new ChatResponse($this->createChatData());
        
        $this->assertEquals(10, $response->getPromptTokens());
        $this->assertEquals(20, $response->getCompletionTokens());
        $this->assertEquals(30, $response->getTotalTokens());
    }
    
    public function testGetFinishReason()
    {
        $response = new ChatResponse($this->createChatData());
        $this->assertEquals('stop', $response->getFinishReason());
    }
    
    public function testIsLengthStop()
    {
        $data = $this->createChatData();
        $response = new ChatResponse($data);
        $this->assertFalse($response->isLengthStop());
        
        $data['choices'][0]['finish_reason'] = 'length';
        $response = new ChatResponse($data);
        $this->assertTrue($response->isLengthStop());
    }
    
    public function testGetChoices()
    {
        $response = new ChatResponse($this->createChatData());
        $choices = $response->getChoices();
        
        $this->assertIsArray($choices);
        $this->assertCount(1, $choices);
        $this->assertEquals(0, $choices[0]['index']);
    }
    
    public function testEmptyResponse()
    {
        $response = new ChatResponse([]);
        
        $this->assertEquals('', $response->getContent());
        $this->assertEquals([], $response->getMessage());
        $this->assertEquals('', $response->getRole());
        $this->assertEquals('', $response->getModel());
        $this->assertEquals([], $response->getUsage());
        $this->assertEquals(0, $response->getPromptTokens());
        $this->assertEquals(0, $response->getCompletionTokens());
        $this->assertEquals(0, $response->getTotalTokens());
    }
    
    public function testInheritedMethods()
    {
        $data = $this->createChatData();
        $response = new ChatResponse($data);
        
        // 测试继承的方法
        $this->assertEquals($data, $response->getRawData());
        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getError());
        $this->assertEquals('gpt-3.5-turbo', $response->model); // __get
        $this->assertTrue($response->has('usage'));
        
        $json = $response->toJson();
        $this->assertJson($json);
        $this->assertStringContainsString('gpt-3.5-turbo', $json);
    }
}