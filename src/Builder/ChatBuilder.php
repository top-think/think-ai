<?php

namespace think\ai\Builder;

use think\ai\Client;
use think\ai\Response\ChatResponse;
use think\ai\Response\StreamResponse;

class ChatBuilder
{
    protected Client $client;
    protected array $messages = [];
    protected array $params = [
        'stream' => true,
        'moderation' => true,
    ];
    
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * 设置模型
     */
    public function model(string $model): self
    {
        $this->params['model'] = $model;
        return $this;
    }
    
    /**
     * 添加系统消息
     */
    public function system(string $content): self
    {
        $this->messages[] = [
            'role' => 'system',
            'content' => $content,
        ];
        return $this;
    }
    
    /**
     * 添加用户消息
     */
    public function user(string $content): self
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $content,
        ];
        return $this;
    }
    
    /**
     * 添加助手消息
     */
    public function assistant(string $content): self
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content,
        ];
        return $this;
    }
    
    /**
     * 添加自定义消息
     */
    public function message(string $role, string $content): self
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
        ];
        return $this;
    }
    
    /**
     * 批量添加消息
     */
    public function messages(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);
        return $this;
    }
    
    /**
     * 设置温度（创造性）
     */
    public function temperature(float $temperature): self
    {
        $this->params['temperature'] = $temperature;
        return $this;
    }
    
    /**
     * 设置最大 token 数
     */
    public function maxTokens(int $maxTokens): self
    {
        $this->params['max_tokens'] = $maxTokens;
        return $this;
    }
    
    /**
     * 设置 top_p
     */
    public function topP(float $topP): self
    {
        $this->params['top_p'] = $topP;
        return $this;
    }
    
    /**
     * 设置频率惩罚
     */
    public function frequencyPenalty(float $penalty): self
    {
        $this->params['frequency_penalty'] = $penalty;
        return $this;
    }
    
    /**
     * 设置存在惩罚
     */
    public function presencePenalty(float $penalty): self
    {
        $this->params['presence_penalty'] = $penalty;
        return $this;
    }
    
    /**
     * 设置停止词
     */
    public function stop($stop): self
    {
        $this->params['stop'] = $stop;
        return $this;
    }
    
    /**
     * 设置是否启用流式输出
     */
    public function stream(bool $stream = true): self
    {
        $this->params['stream'] = $stream;
        return $this;
    }
    
    /**
     * 设置是否启用内容审核
     */
    public function moderation(bool $moderation = true): self
    {
        $this->params['moderation'] = $moderation;
        return $this;
    }
    
    /**
     * 设置用户标识
     */
    public function user_id(string $userId): self
    {
        $this->params['user'] = $userId;
        return $this;
    }
    
    /**
     * 设置响应格式
     */
    public function responseFormat(array $format): self
    {
        $this->params['response_format'] = $format;
        return $this;
    }
    
    /**
     * 设置种子（用于可重复的输出）
     */
    public function seed(int $seed): self
    {
        $this->params['seed'] = $seed;
        return $this;
    }
    
    /**
     * 设置工具/函数
     */
    public function tools(array $tools): self
    {
        $this->params['tools'] = $tools;
        return $this;
    }
    
    /**
     * 设置工具选择
     */
    public function toolChoice($toolChoice): self
    {
        $this->params['tool_choice'] = $toolChoice;
        return $this;
    }
    
    /**
     * 清空消息
     */
    public function clearMessages(): self
    {
        $this->messages = [];
        return $this;
    }
    
    /**
     * 获取当前参数
     */
    public function getParams(): array
    {
        return array_merge($this->params, [
            'messages' => $this->messages,
        ]);
    }
    
    /**
     * 发送请求
     * 
     * @return ChatResponse|StreamResponse
     */
    public function send()
    {
        if (empty($this->messages)) {
            throw new \InvalidArgumentException('消息列表不能为空');
        }
        
        if (!isset($this->params['model'])) {
            throw new \InvalidArgumentException('必须指定模型');
        }
        
        $params = $this->getParams();
        $result = $this->client->chat()->completions($params);
        
        // 如果是流式响应，包装成 StreamResponse
        if ($result instanceof \think\ai\StreamIterator) {
            return new StreamResponse($result);
        }
        
        // 否则返回 ChatResponse
        return new ChatResponse($result);
    }
    
    /**
     * 快捷方法：直接发送消息并获取回复内容
     * 
     * @param string $message 用户消息
     * @return string 助手回复的内容
     */
    public function ask(string $message): string
    {
        $this->user($message);
        $response = $this->send();
        
        if ($response instanceof StreamResponse) {
            return $response->getFullContent();
        }
        
        return $response->getContent();
    }
}