<?php

namespace think\ai\Response;

use think\ai\StreamIterator;

/**
 * 增强的流式响应处理类
 */
class EnhancedStreamResponse extends StreamResponse
{
    protected array $eventHandlers = [];
    protected bool $processed = false;
    
    /**
     * 注册事件处理器
     */
    public function on(string $event, callable $handler): self
    {
        $this->eventHandlers[$event] = $handler;
        return $this;
    }
    
    /**
     * 处理内容更新事件
     */
    public function onContent(callable $handler): self
    {
        return $this->on('content', $handler);
    }
    
    /**
     * 处理开始事件
     */
    public function onStart(callable $handler): self
    {
        return $this->on('start', $handler);
    }
    
    /**
     * 处理结束事件
     */
    public function onEnd(callable $handler): self
    {
        return $this->on('end', $handler);
    }
    
    /**
     * 处理错误事件
     */
    public function onError(callable $handler): self
    {
        return $this->on('error', $handler);
    }
    
    /**
     * 处理函数调用事件（用于 Function Calling）
     */
    public function onFunctionCall(callable $handler): self
    {
        return $this->on('function_call', $handler);
    }
    
    /**
     * 处理工具调用事件
     */
    public function onToolCalls(callable $handler): self
    {
        return $this->on('tool_calls', $handler);
    }
    
    /**
     * 处理推理内容事件（针对推理模型）
     */
    public function onReasoning(callable $handler): self
    {
        return $this->on('reasoning', $handler);
    }
    
    /**
     * 开始处理流
     */
    public function process(): self
    {
        if ($this->processed) {
            return $this;
        }
        
        $this->processed = true;
        $isFirst = true;
        
        try {
            // 触发开始事件
            $this->trigger('start');
            
            foreach ($this->stream as $chunk) {
                $this->chunks[] = $chunk;
                
                $content = $chunk['delta']['content'] ?? null;
                if ($content !== null) {
                    $this->fullContent .= $content;
                    
                    // 触发内容事件
                    $this->trigger('content', $content, $chunk);
                }
                
                // 处理推理内容
                $reasoning = $chunk['delta']['reasoning'] ?? null;
                if ($reasoning !== null) {
                    if (!isset($this->fullReasoning)) {
                        $this->fullReasoning = '';
                    }
                    $this->fullReasoning .= $reasoning;
                    
                    // 触发推理事件
                    $this->trigger('reasoning', $reasoning, $chunk);
                }
                
                // 处理函数调用 
                $functionCall = $chunk['delta']['function_call'] ?? null;
                if ($functionCall !== null) {
                    $this->trigger('function_call', $functionCall, $chunk);
                }
                
                // 处理工具调用 
                $toolCalls = $chunk['delta']['tool_calls'] ?? null;
                if ($toolCalls !== null) {
                    $this->trigger('tool_calls', $toolCalls, $chunk);
                }
                
                // 通用 chunk 事件
                $this->trigger('chunk', $chunk);
                
                $isFirst = false;
            }
            
            // 触发结束事件
            $this->trigger('end', $this->fullContent);
            
        } catch (\Exception $e) {
            // 触发错误事件
            $this->trigger('error', $e);
            throw $e;
        }
        
        return $this;
    }
    
    /**
     * 触发事件
     */
    protected function trigger(string $event, ...$args): void
    {
        if (isset($this->eventHandlers[$event])) {
            call_user_func_array($this->eventHandlers[$event], $args);
        }
    }
    
    /**
     * 流式输出到浏览器（支持 SSE）
     */
    public function streamToBrowser(): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no'); // 禁用 Nginx 缓冲
        
        $this->onContent(function($content) {
            echo "data: " . json_encode(['content' => $content]) . "\n\n";
            flush();
        });
        
        $this->onEnd(function() {
            echo "data: [DONE]\n\n";
            flush();
        });
        
        $this->process();
    }
    
    /**
     * 流式写入文件
     */
    public function streamToFile(string $filename): self
    {
        $file = fopen($filename, 'w');
        
        $this->onContent(function($content) use ($file) {
            fwrite($file, $content);
        });
        
        $this->onEnd(function() use ($file) {
            fclose($file);
        });
        
        return $this->process();
    }
    
    /**
     * 使用缓冲区批量处理
     */
    public function buffer(int $size = 100): self
    {
        $buffer = '';
        
        $originalHandler = $this->eventHandlers['content'] ?? null;
        
        $this->onContent(function($content) use (&$buffer, $size, $originalHandler) {
            $buffer .= $content;
            
            if (mb_strlen($buffer) >= $size) {
                if ($originalHandler) {
                    $originalHandler($buffer);
                }
                $buffer = '';
            }
        });
        
        $this->onEnd(function() use (&$buffer, $originalHandler) {
            if ($buffer && $originalHandler) {
                $originalHandler($buffer);
            }
        });
        
        return $this;
    }
    
    /**
     * 添加进度跟踪
     */
    public function withProgress(callable $progressHandler): self
    {
        $totalChunks = 0;
        
        $this->on('chunk', function($chunk) use (&$totalChunks, $progressHandler) {
            $totalChunks++;
            $progressHandler($totalChunks, $chunk);
        });
        
        return $this;
    }
}