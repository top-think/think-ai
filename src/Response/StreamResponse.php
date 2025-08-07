<?php

namespace think\ai\Response;

use think\ai\StreamIterator;

class StreamResponse
{
    protected StreamIterator $stream;
    protected array $chunks = [];
    protected string $fullContent = '';
    protected string $fullReasoning = '';
    protected array $toolCalls = [];
    protected $plugin;
    
    // Event handlers
    protected $startHandler;
    protected $contentHandler;
    protected $toolcallHandler;
    protected $reasoningHandler;
    protected $endHandler;
    protected $errorHandler;
    protected $progressHandler;
    
    public function __construct(StreamIterator $stream)
    {
        $this->stream = $stream;
    }
    
    /**
     * 获取流式迭代器
     */
    public function getStream(): StreamIterator
    {
        return $this->stream;
    }
    
    /**
     * 设置开始处理时的回调
     */
    public function onStart(callable $callback): self
    {
        $this->startHandler = $callback;
        return $this;
    }
    
    /**
     * 设置内容处理时的回调
     */
    public function onContent(callable $callback): self
    {
        $this->contentHandler = $callback;
        return $this;
    }
    
    /**
     * 设置思考处理时的回调
     */
    public function onReasoning(callable $callback): self
    {
        $this->reasoningHandler = $callback;
        return $this;
    }

    /**
     * 设置工具调用时的回调
     */
    public function onToolCall(callable $callback): self
    {
        $this->toolcallHandler = $callback;
        return $this;
    }

    /**
     * 设置处理完成时的回调
     */
    public function onEnd(callable $callback): self
    {
        $this->endHandler = $callback;
        return $this;
    }
    
    /**
     * 设置错误处理时的回调
     */
    public function onError(callable $callback): self
    {
        $this->errorHandler = $callback;
        return $this;
    }
    
    /**
     * 设置进度跟踪回调
     */
    public function withProgress(callable $callback): self
    {
        $this->progressHandler = $callback;
        return $this;
    }
    
    /**
     * 处理每个数据块
     * 
     * @param callable $callback 回调函数，接收 ChatResponse 参数
     */
    public function onChunk(callable $callback): self
    {
        foreach ($this->stream as $chunk) {
            $this->chunks[] = $chunk;

            $content = $chunk['delta']['content'] ?? null;
            if ($content !== null) {
                $this->fullContent .= $content;
            }
            
            // 推理内容
            $reasoning = $chunk['delta']['reasoning'] ?? null;
            if ($reasoning !== null) {
                if (!isset($this->fullReasoning)) {
                    $this->fullReasoning = '';
                }
                $this->fullReasoning .= $reasoning;
            }
            
            // 处理工具调用
            if (isset($chunk['delta']['tool_calls'])) {
                foreach ($chunk['delta']['tool_calls'] as $toolCall) {
                    $this->toolCalls[] = new PluginResponse($toolCall);
                }
            }
            
            // 将 delta 格式转换为 message 格式，以便 ChatResponse 可以正确解析
            $formattedChunk['message'] = $chunk['delta'] ?? [];
            // 保留finish_reason和usage
            if (isset($chunk['finish_reason'])) {
                $formattedChunk['finish_reason'] = $chunk['finish_reason'];
            }
            if (isset($chunk['usage'])) {
                $formattedChunk['usage'] = $chunk['usage'];
            }
            
            $response = new ChatResponse($formattedChunk);
            $callback($response);
        }
        
        return $this;
    }
    
    /**
     * 获取完整的内容（在流式处理完成后）
     */
    public function getFullContent(): string
    {
        // 如果还没有处理流，先处理一遍
        if (empty($this->chunks)) {
            foreach ($this->stream as $chunk) {
                $this->chunks[] = $chunk;
                $content = $chunk['delta']['content'] ?? null;
                if ($content !== null) {
                    $this->fullContent .= $content;
                }
            }
        }
        
        return $this->fullContent;
    }
    
    /**
     * 获取完整的推理内容（针对推理模型）
     */
    public function getFullReasoning(): string
    {
        return $this->fullReasoning ?? '';
    }
    
    /**
     * 获取所有数据块
     */
    public function getChunks(): array
    {
        return $this->chunks;
    }
    
    /**
     * 获取所有工具调用
     */
    public function getToolCalls(): array
    {
        return $this->toolCalls;
    }
    
    /**
     * 处理流式响应
     */
    public function process(): self
    {
        try {
            // 触发开始事件
            if ($this->startHandler) {
                ($this->startHandler)();
            }
            
            $chunkCount = 0;
            
            foreach ($this->stream as $chunk) {
                $this->chunks[] = $chunk;
                $chunkCount++;
                
                $content = $chunk['delta']['reasoning'] ?? null;
                if ($content !== null) {
                    // 思考过程
                    $this->fullReasoning .= $content;
                    if ($this->reasoningHandler) {
                        ($this->reasoningHandler)($content);
                    }
                }                
                $content = $chunk['delta']['content'] ?? null;
                if ($content !== null) {
                    // 推理内容
                    $this->fullContent .= $content;
                    if ($this->contentHandler) {
                        ($this->contentHandler)($content);
                    }
                }
                
                // 处理工具调用
                if (isset($chunk['delta']['tool_calls'])) {
                    foreach ($chunk['delta']['tool_calls'] as $toolCall) {
                        if (is_null($this->plugin)) {
                            $this->plugin = new PluginResponse($toolCall);
                        }
                        $this->plugin->setPluginData($toolCall);

                        if ($this->toolcallHandler) {
                            ($this->toolcallHandler)($this->plugin);
                        }                       
                    }
                }
                
                // 触发进度事件
                if ($this->progressHandler) {
                    ($this->progressHandler)($chunkCount, $chunk);
                }
            }
            
            // 触发结束事件
            if ($this->endHandler) {
                ($this->endHandler)($this->fullContent);
            }
            
        } catch (\Exception $e) {
            // 触发错误事件
            if ($this->errorHandler) {
                ($this->errorHandler)($e);
            } else {
                throw $e;
            }
        }
        
        return $this;
    }
    
    /**
     * 流式输出到浏览器（SSE）
     */
    public function streamToBrowser(): void
    {
        // 设置SSE头
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        
        foreach ($this->stream as $chunk) {
            // 兼容新旧格式
            $content = $chunk['delta']['content'] ?? null;
            if ($content !== null) {
                echo "data: " . json_encode(['content' => $content]) . "\n\n";
                ob_flush();
                flush();
            }
        }
        
        echo "data: [DONE]\n\n";
        ob_flush();
        flush();
    }
    
    /**
     * 流式写入文件
     */
    public function streamToFile(string $filename): self
    {
        $file = fopen($filename, 'w');
        
        if (!$file) {
            throw new \RuntimeException("无法打开文件: {$filename}");
        }
        
        try {
            foreach ($this->stream as $chunk) {
                // 兼容新旧格式
                $content = $chunk['delta']['content'] ?? null;
                if ($content !== null) {
                    fwrite($file, $content);
                    fflush($file);
                }
            }
        } finally {
            fclose($file);
        }
        
        return $this;
    }
}