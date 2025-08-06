<?php

namespace think\ai\Response;

class ChatResponse extends BaseResponse
{
    /**
     * 获取聊天内容
     */
    public function getContent(): string
    {
        return $this->data['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * 获取完整的消息对象
     */
    public function getMessage(): array
    {
        return $this->data['choices'][0]['message'] ?? [];
    }
    
    /**
     * 获取角色
     */
    public function getRole(): string
    {
        return $this->data['choices'][0]['message']['role'] ?? '';
    }
    
    /**
     * 获取使用的模型
     */
    public function getModel(): string
    {
        return $this->data['model'] ?? '';
    }
    
    /**
     * 获取使用的 token 数量
     */
    public function getUsage(): array
    {
        return $this->data['usage'] ?? [];
    }
    
    /**
     * 获取提示词 token 数
     */
    public function getPromptTokens(): int
    {
        return $this->data['usage']['prompt_tokens'] ?? 0;
    }
    
    /**
     * 获取完成 token 数
     */
    public function getCompletionTokens(): int
    {
        return $this->data['usage']['completion_tokens'] ?? 0;
    }
    
    /**
     * 获取总 token 数
     */
    public function getTotalTokens(): int
    {
        return $this->data['usage']['total_tokens'] ?? 0;
    }
    
    /**
     * 获取完成原因
     */
    public function getFinishReason(): string
    {
        return $this->data['choices'][0]['finish_reason'] ?? '';
    }
    
    /**
     * 是否因长度限制而停止
     */
    public function isLengthStop(): bool
    {
        return $this->getFinishReason() === 'length';
    }
    
    /**
     * 获取所有选择（如果有多个）
     */
    public function getChoices(): array
    {
        return $this->data['choices'] ?? [];
    }
    
    /**
     * 检查是否有工具调用
     */
    public function hasToolCalls(): bool
    {
        return !empty($this->data['choices'][0]['message']['tool_calls']);
    }
    
    /**
     * 获取工具调用列表
     * 
     * @return array 工具调用对象数组
     */
    public function getToolCalls(): array
    {
        $toolCalls = $this->data['choices'][0]['message']['tool_calls'] ?? [];
        
        // 转换为更易用的对象格式
        return array_map(function($toolCall) {
            return (object)[
                'id' => $toolCall['id'] ?? '',
                'type' => $toolCall['type'] ?? 'function',
                'name' => $toolCall['function']['name'] ?? '',
                'arguments' => json_decode($toolCall['function']['arguments'] ?? '{}', true),
                'result' => null // 需要手动执行工具后设置
            ];
        }, $toolCalls);
    }
    
    /**
     * 获取工具调用ID列表
     */
    public function getToolCallIds(): array
    {
        return array_column($this->data['choices'][0]['message']['tool_calls'] ?? [], 'id');
    }
}