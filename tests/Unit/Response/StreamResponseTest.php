<?php

namespace think\ai\tests\Unit\Response;

use think\ai\Response\StreamResponse;
use think\ai\Response\ChatResponse;
use think\ai\StreamIterator;
use think\ai\tests\TestCase;

class StreamResponseTest extends TestCase
{
    private function createMockStreamIterator(array $chunks): StreamIterator
    {
        $mockStream = $this->createMockStreamResponse($chunks);
        return new StreamIterator($mockStream->getBody());
    }
    
    private function createStreamChunks(): array
    {
        return [
            [  
                'delta' => ['content' => '你好']
            ],
            [
                'delta' => ['content' => '，我是']
            ],
            [
                'delta' => ['content' => ' AI 助手'],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 15,
                    'total_tokens' => 25
                ]
            ]
        ];
    }
    
    public function testOnChunk()
    {
        $chunks = $this->createStreamChunks();
        $iterator = $this->createMockStreamIterator($chunks);
        $response = new StreamResponse($iterator);
        
        $receivedContent = [];
        $response->onChunk(function($chunk) use (&$receivedContent) {
            $this->assertInstanceOf(ChatResponse::class, $chunk);
            $receivedContent[] = $chunk->getContent();
        });
        
        $this->assertEquals(['你好', '，我是', ' AI 助手'], $receivedContent);
    }
    
    public function testGetFullContent()
    {
        $chunks = $this->createStreamChunks();
        $iterator = $this->createMockStreamIterator($chunks);
        $response = new StreamResponse($iterator);
        
        $fullContent = $response->getFullContent();
        $this->assertEquals('你好，我是 AI 助手', $fullContent);
    }
    
    public function testGetFullContentAfterOnChunk()
    {
        $chunks = $this->createStreamChunks();
        $iterator = $this->createMockStreamIterator($chunks);
        $response = new StreamResponse($iterator);
        
        // 先处理 chunks
        $response->onChunk(function($chunk) {
            // 空处理
        });
        
        // 再获取完整内容
        $fullContent = $response->getFullContent();
        $this->assertEquals('你好，我是 AI 助手', $fullContent);
    }
    
    public function testGetChunks()
    {
        $chunks = $this->createStreamChunks();
        $iterator = $this->createMockStreamIterator($chunks);
        $response = new StreamResponse($iterator);
        
        // 触发处理
        $response->getFullContent();
        
        $receivedChunks = $response->getChunks();
        $this->assertCount(3, $receivedChunks);
        $this->assertEquals($chunks[0]['delta']['content'], $receivedChunks[0]['delta']['content']);
    }
    
    public function testGetStream()
    {
        $chunks = $this->createStreamChunks();
        $iterator = $this->createMockStreamIterator($chunks);
        $response = new StreamResponse($iterator);
        
        $stream = $response->getStream();
        $this->assertInstanceOf(StreamIterator::class, $stream);
        $this->assertSame($iterator, $stream);
    }
    
    public function testToResponse()
    {
        $chunks = $this->createStreamChunks();
        $iterator = $this->createMockStreamIterator($chunks);
        $response = new StreamResponse($iterator);
        
        $chatResponse = $response->toResponse();
        
        $this->assertInstanceOf(ChatResponse::class, $chatResponse);
        $this->assertEquals('你好，我是 AI 助手', $chatResponse->getContent());
        $this->assertEquals('assistant', $chatResponse->getRole());
        $this->assertEquals(25, $chatResponse->getTotalTokens());
    }
    
    public function testEmptyStream()
    {
        $iterator = $this->createMockStreamIterator([]);
        $response = new StreamResponse($iterator);
        
        $fullContent = $response->getFullContent();
        $this->assertEquals('', $fullContent);
        
        $chunks = $response->getChunks();
        $this->assertCount(0, $chunks);
    }
}