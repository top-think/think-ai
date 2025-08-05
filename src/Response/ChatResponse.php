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
}