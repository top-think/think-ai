<?php

namespace think\ai\Request;

use think\ai\Enum\Model;
use think\ai\Enum\Role;

class ChatRequest
{
    protected array $messages = [];
    protected string $model = Model::GPT_35_TURBO;
    protected float $temperature = 1.0;
    protected ?int $maxTokens = null;
    protected float $topP = 1.0;
    protected int $n = 1;
    protected bool $stream = true;
    protected $stop = null;
    protected float $presencePenalty = 0.0;
    protected float $frequencyPenalty = 0.0;
    protected ?array $logitBias = null;
    protected ?string $user = null;
    protected ?array $responseFormat = null;
    protected ?int $seed = null;
    protected ?array $tools = null;
    protected $toolChoice = null;
    protected bool $moderation = true;
    
    /**
     * 创建新的聊天请求
     */
    public static function create(): self
    {
        return new self();
    }
    
    /**
     * 设置模型
     */
    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * 添加消息
     */
    public function addMessage(string $role, string $content, ?string $name = null): self
    {
        if (!Role::isValid($role)) {
            throw new \InvalidArgumentException("无效的角色: {$role}");
        }
        
        $message = [
            'role' => $role,
            'content' => $content,
        ];
        
        if ($name !== null) {
            $message['name'] = $name;
        }
        
        $this->messages[] = $message;
        return $this;
    }
    
    /**
     * 添加系统消息
     */
    public function system(string $content): self
    {
        return $this->addMessage(Role::SYSTEM, $content);
    }
    
    /**
     * 添加用户消息
     */
    public function user(string $content): self
    {
        return $this->addMessage(Role::USER, $content);
    }
    
    /**
     * 添加助手消息
     */
    public function assistant(string $content): self
    {
        return $this->addMessage(Role::ASSISTANT, $content);
    }
    
    /**
     * 批量设置消息
     */
    public function messages(array $messages): self
    {
        foreach ($messages as $message) {
            if (!isset($message['role']) || !isset($message['content'])) {
                throw new \InvalidArgumentException('消息必须包含 role 和 content');
            }
            
            if (!Role::isValid($message['role'])) {
                throw new \InvalidArgumentException("无效的角色: {$message['role']}");
            }
        }
        
        $this->messages = $messages;
        return $this;
    }
    
    /**
     * 设置温度
     */
    public function temperature(float $temperature): self
    {
        if ($temperature < 0 || $temperature > 2) {
            throw new \InvalidArgumentException('温度必须在 0 到 2 之间');
        }
        
        $this->temperature = $temperature;
        return $this;
    }
    
    /**
     * 设置最大 token 数
     */
    public function maxTokens(int $maxTokens): self
    {
        if ($maxTokens < 1) {
            throw new \InvalidArgumentException('最大 token 数必须大于 0');
        }
        
        $this->maxTokens = $maxTokens;
        return $this;
    }
    
    /**
     * 设置 top_p
     */
    public function topP(float $topP): self
    {
        if ($topP < 0 || $topP > 1) {
            throw new \InvalidArgumentException('top_p 必须在 0 到 1 之间');
        }
        
        $this->topP = $topP;
        return $this;
    }
    
    /**
     * 设置生成数量
     */
    public function n(int $n): self
    {
        if ($n < 1) {
            throw new \InvalidArgumentException('生成数量必须大于 0');
        }
        
        $this->n = $n;
        return $this;
    }
    
    /**
     * 设置是否流式输出
     */
    public function stream(bool $stream = true): self
    {
        $this->stream = $stream;
        return $this;
    }
    
    /**
     * 设置停止词
     */
    public function stop($stop): self
    {
        $this->stop = $stop;
        return $this;
    }
    
    /**
     * 设置存在惩罚
     */
    public function presencePenalty(float $penalty): self
    {
        if ($penalty < -2 || $penalty > 2) {
            throw new \InvalidArgumentException('存在惩罚必须在 -2 到 2 之间');
        }
        
        $this->presencePenalty = $penalty;
        return $this;
    }
    
    /**
     * 设置频率惩罚
     */
    public function frequencyPenalty(float $penalty): self
    {
        if ($penalty < -2 || $penalty > 2) {
            throw new \InvalidArgumentException('频率惩罚必须在 -2 到 2 之间');
        }
        
        $this->frequencyPenalty = $penalty;
        return $this;
    }
    
    /**
     * 设置用户标识
     */
    public function user(string $user): self
    {
        $this->user = $user;
        return $this;
    }
    
    /**
     * 设置响应格式
     */
    public function responseFormat(array $format): self
    {
        $this->responseFormat = $format;
        return $this;
    }
    
    /**
     * 设置种子
     */
    public function seed(int $seed): self
    {
        $this->seed = $seed;
        return $this;
    }
    
    /**
     * 设置工具
     */
    public function tools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }
    
    /**
     * 设置工具选择
     */
    public function toolChoice($toolChoice): self
    {
        $this->toolChoice = $toolChoice;
        return $this;
    }
    
    /**
     * 设置是否启用内容审核
     */
    public function moderation(bool $moderation = true): self
    {
        $this->moderation = $moderation;
        return $this;
    }
    
    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        if (empty($this->messages)) {
            throw new \InvalidArgumentException('消息列表不能为空');
        }
        
        $params = [
            'model' => $this->model,
            'messages' => $this->messages,
            'temperature' => $this->temperature,
            'top_p' => $this->topP,
            'n' => $this->n,
            'stream' => $this->stream,
            'presence_penalty' => $this->presencePenalty,
            'frequency_penalty' => $this->frequencyPenalty,
            'moderation' => $this->moderation,
        ];
        
        // 只添加非空的可选参数
        if ($this->maxTokens !== null) {
            $params['max_tokens'] = $this->maxTokens;
        }
        
        if ($this->stop !== null) {
            $params['stop'] = $this->stop;
        }
        
        if ($this->logitBias !== null) {
            $params['logit_bias'] = $this->logitBias;
        }
        
        if ($this->user !== null) {
            $params['user'] = $this->user;
        }
        
        if ($this->responseFormat !== null) {
            $params['response_format'] = $this->responseFormat;
        }
        
        if ($this->seed !== null) {
            $params['seed'] = $this->seed;
        }
        
        if ($this->tools !== null) {
            $params['tools'] = $this->tools;
        }
        
        if ($this->toolChoice !== null) {
            $params['tool_choice'] = $this->toolChoice;
        }
        
        return $params;
    }
}