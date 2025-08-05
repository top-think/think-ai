<?php

namespace think\ai\tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Mockery;
use GuzzleHttp\Psr7\Response;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
    
    /**
     * 创建模拟的 HTTP 响应
     */
    protected function createMockResponse(array $data, int $statusCode = 200): Response
    {
        return new Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }
    
    /**
     * 创建模拟的流式响应
     */
    protected function createMockStreamResponse(array $chunks): Response
    {
        $stream = '';
        foreach ($chunks as $chunk) {
            $stream .= "data: " . json_encode($chunk) . "\n\n";
        }
        $stream .= "data: [DONE]\n\n";
        
        return new Response(
            200,
            ['Content-Type' => 'text/event-stream'],
            $stream
        );
    }
}