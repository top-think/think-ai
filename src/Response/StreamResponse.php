<?php

namespace think\ai\Response;

use think\ai\StreamIterator;

class StreamResponse
{
    protected StreamIterator $stream;
    protected array $chunks = [];
    protected string $fullContent = '';
    
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
     * 处理每个数据块
     * 
     * @param callable $callback 回调函数，接收 ChatResponse 参数
     */
    public function onChunk(callable $callback): self
    {
        foreach ($this->stream as $chunk) {
            $this->chunks[] = $chunk;
            
            // 累积内容
            if (isset($chunk['choices'][0]['delta']['content'])) {
                $this->fullContent .= $chunk['choices'][0]['delta']['content'];
            }
            
            $response = new ChatResponse($chunk);
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
                if (isset($chunk['choices'][0]['delta']['content'])) {
                    $this->fullContent .= $chunk['choices'][0]['delta']['content'];
                }
            }
        }
        
        return $this->fullContent;
    }
    
    /**
     * 获取所有数据块
     */
    public function getChunks(): array
    {
        return $this->chunks;
    }
    
    /**
     * 转换为非流式响应
     */
    public function toResponse(): ChatResponse
    {
        $fullContent = $this->getFullContent();
        
        // 构造完整的响应数据
        $responseData = [
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => $fullContent
                    ],
                    'finish_reason' => 'stop'
                ]
            ]
        ];
        
        // 如果有其他元数据，从最后一个 chunk 中获取
        if (!empty($this->chunks)) {
            $lastChunk = end($this->chunks);
            if (isset($lastChunk['model'])) {
                $responseData['model'] = $lastChunk['model'];
            }
            if (isset($lastChunk['usage'])) {
                $responseData['usage'] = $lastChunk['usage'];
            }
        }
        
        return new ChatResponse($responseData);
    }
}