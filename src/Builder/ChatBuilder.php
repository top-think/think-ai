<?php

namespace think\ai\Builder;

use think\ai\Client;
use think\ai\Response\StreamResponse;

class ChatBuilder extends BaseBuilder
{
    protected array $messages = [];
    
    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->params = [
            'stream' => true,
            'moderation' => true,
        ];
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
     * 添加工具/函数调用
     * 
     * @param string $type 工具类型（plugin, function等）
     * @param array $config 工具配置
     * @return $this
     */
    public function tool(string $type, array $config): self
    {
        if (!isset($this->params['tools'])) {
            $this->params['tools'] = [];
        }
        
        $this->params['tools'][] = ['type' => $type, $type => $config];
        return $this;
    }
    
    /**
     * 添加插件工具
     * 
     * @param string $name 插件名称
     * @param string $tool 工具名称
     * @return $this
     */
    public function plugin(string $name, string $tool): self
    {
        return $this->tool('plugin', [
            'name' => $name,
            'tool' => $tool
        ]);
    }
    
    /**
     * 添加函数工具
     * 
     * @param string $name 函数名称
     * @param string $description 函数描述
     * @param array $parameters 参数模式
     * @return $this
     */
    public function function(string $name, string $description, array $parameters = []): self
    {
        return $this->tool('function', [
            'name' => $name,
            'description' => $description,
            'parameters' => $parameters
        ]);
    }
    
    /**
     * 强制使用指定工具
     * 
     * @param string $name 工具名称
     * @return $this
     */
    public function forceTool(string $name): self
    {
        return $this->toolChoice([
            'type' => 'function',
            'function' => ['name' => $name]
        ]);
    }
    
    /**
     * 添加图片消息（用于视觉模型）
     * 
     * @param string $imageUrl 图片URL或base64编码的图片
     * @param string $detail 图片细节级别（auto, low, high）
     * @return $this
     */
    public function image(string $imageUrl, string $detail = 'auto'): self
    {
        // 如果最后一条消息是用户消息，则添加图片到该消息
        $lastMessage = end($this->messages);
        $messageConverted = false;
        
        if ($lastMessage && $lastMessage['role'] === 'user' && is_string($lastMessage['content'])) {
            // 将文本内容转换为数组格式
            $index = count($this->messages) - 1;
            $this->messages[$index]['content'] = [
                [
                    'type' => 'text',
                    'text' => $lastMessage['content']
                ]
            ];
            $messageConverted = true;
        }
        
        // 添加图片
        $imageContent = [
            'type' => 'image_url',
            'image_url' => [
                'url' => $imageUrl
            ]
        ];
        
        if ($detail !== 'auto') {
            $imageContent['image_url']['detail'] = $detail;
        }
        
        // 重新获取最后一条消息（可能已被转换）
        $lastMessage = end($this->messages);
        
        // 如果最后一条是用户消息且内容是数组，添加到其中
        if ($lastMessage && $lastMessage['role'] === 'user' && is_array($lastMessage['content'])) {
            $index = count($this->messages) - 1;
            $this->messages[$index]['content'][] = $imageContent;
        } else {
            // 否则创建新的用户消息
            $this->messages[] = [
                'role' => 'user',
                'content' => [$imageContent]
            ];
        }
        
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
     * 获取当前参数（重写以包含消息）
     */
    public function getParams(): array
    {
        $params = parent::getParams();
        
        // 只有在有消息时才添加 messages 键
        if (!empty($this->messages)) {
            $params['messages'] = $this->messages;
        }
        
        return $params;
    }
    
    /**
     * 发送请求
     * 
     * @return ChatResponse|StreamResponse
     */
    public function send()
    {
        $this->validateRequired(['model']);
        
        if (empty($this->messages)) {
            throw new \InvalidArgumentException('消息列表不能为空');
        }
        
        $params = $this->getParams();
        $result = $this->client->chat()->completions($params);
        
        return $result;
    }
    
    /**
     * 直接发送消息并获取回复内容
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