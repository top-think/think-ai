<?php

namespace think\ai;

use think\ai\Builder\ChatBuilder;
use think\ai\Response\ChatResponse;
use think\ai\Response\StreamResponse;

/**
 * 多轮对话管理器
 * 专门用于处理包含函数调用的多轮对话
 */
class MultiTurnChatManager
{
    protected Client $client;
    protected ChatBuilder $chatBuilder;
    protected array $toolRegistry = [];
    protected int $maxTurns = 10;
    protected int $currentTurn = 0;
    
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->chatBuilder = $client->chatBuilder();
    }
    
    /**
     * 开始一个新的对话
     */
    public function chat(): ChatBuilder
    {
        $this->currentTurn = 0;
        $this->chatBuilder->clearMessages();
        return $this->chatBuilder;
    }
    
    /**
     * 注册工具执行函数
     */
    public function registerTool(string $name, callable $executor): self
    {
        $this->toolRegistry[$name] = $executor;
        return $this;
    }
    
    /**
     * 批量注册工具
     */
    public function registerTools(array $tools): self
    {
        foreach ($tools as $name => $executor) {
            $this->registerTool($name, $executor);
        }
        return $this;
    }
    
    /**
     * 设置最大轮次
     */
    public function maxTurns(int $maxTurns): self
    {
        $this->maxTurns = $maxTurns;
        return $this;
    }
    
    /**
     * 执行工具调用
     */
    protected function executeToolCalls(ChatResponse $response): array
    {
        $toolResults = [];
        
        foreach ($response->getToolCalls() as $toolCall) {
            $toolName = $toolCall->name;
            $arguments = $toolCall->arguments;
            
            try {
                if (isset($this->toolRegistry[$toolName])) {
                    // 执行注册的工具函数
                    $result = call_user_func($this->toolRegistry[$toolName], $arguments);
                    $toolResults[] = [
                        'tool_call_id' => $toolCall->id,
                        'role' => 'tool',
                        'name' => $toolName,
                        'content' => is_string($result) ? $result : json_encode($result)
                    ];
                } else {
                    // 工具未注册，返回错误信息
                    $toolResults[] = [
                        'tool_call_id' => $toolCall->id,
                        'role' => 'tool',
                        'name' => $toolName,
                        'content' => json_encode(['error' => "Tool '$toolName' not found"])
                    ];
                }
            } catch (\Exception $e) {
                // 工具执行出错
                $toolResults[] = [
                    'tool_call_id' => $toolCall->id,
                    'role' => 'tool',
                    'name' => $toolName,
                    'content' => json_encode(['error' => $e->getMessage()])
                ];
            }
        }
        
        return $toolResults;
    }
    
    /**
     * 继续对话（处理工具调用后的下一轮）
     */
    public function continueConversation(ChatResponse $response): ChatResponse
    {
        $this->currentTurn++;
        
        if ($this->currentTurn > $this->maxTurns) {
            throw new \RuntimeException("Maximum number of turns ($this->maxTurns) exceeded");
        }
        
        if (!$response->hasToolCalls()) {
            return $response;
        }
        
        // 添加助手的完整消息到消息历史（包含工具调用信息）
        $rawData = $response->getRawData();
        $this->chatBuilder->messages([[
            'role' => 'assistant',
            'content' => $response->getContent(),
            'tool_calls' => $rawData['choices'][0]['message']['tool_calls'] ?? null
        ]]);
        
        // 执行工具调用
        $toolResults = $this->executeToolCalls($response);
        
        // 添加工具结果到消息历史
        foreach ($toolResults as $result) {
            $this->chatBuilder->messages([[
                'role' => 'tool',
                'tool_call_id' => $result['tool_call_id'],
                'name' => $result['name'],
                'content' => $result['content']
            ]]);
        }
        
        // 发送下一轮请求
        $nextResponse = $this->chatBuilder->send();
        
        // 如果下一轮响应有内容，添加到消息历史
        if ($nextResponse->getContent()) {
            $this->chatBuilder->assistant($nextResponse->getContent());
        }
        
        // 递归处理，直到没有工具调用或达到最大轮次
        if ($nextResponse->hasToolCalls() && $this->currentTurn < $this->maxTurns) {
            return $this->continueConversation($nextResponse);
        }
        
        return $nextResponse;
    }
    
    /**
     * 处理流式响应中的工具调用
     */
    public function processStreamWithTools(StreamResponse $stream, callable $toolExecutor = null): StreamResponse
    {
        $enhancedStream = new \think\ai\Response\EnhancedStreamResponse($stream->getStream());
        
        $enhancedStream->onFunctionCall(function($functionCall) use ($toolExecutor) {
            if ($toolExecutor) {
                call_user_func($toolExecutor, $functionCall);
            }
        });
        
        return $enhancedStream;
    }
    
    /**
     * 获取当前的消息历史
     */
    public function getMessageHistory(): array
    {
        return $this->chatBuilder->getParams()['messages'] ?? [];
    }
    
    /**
     * 清空消息历史
     */
    public function clearHistory(): self
    {
        $this->chatBuilder->clearMessages();
        $this->currentTurn = 0;
        return $this;
    }
}