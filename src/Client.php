<?php

namespace think\ai;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Utils;
use think\ai\api\Audio;
use think\ai\api\Chat;
use think\ai\api\Embeddings;
use think\ai\api\Images;
use think\ai\api\Model;
use think\ai\api\Music;
use think\ai\api\Plugin;
use think\ai\api\Rerank;
use think\ai\api\Sandbox;
use think\ai\api\Videos;
use think\ai\Builder\ChatBuilder;
use think\ai\Builder\ImageBuilder;
use think\ai\Response\ChatResponse;
use think\ai\Response\ImageResponse;

class Client
{
    protected $endpoint = 'https://ai.topthink.com/';

    protected $token;

    protected $handler;
    
    protected array $defaultModel = [
        'chat'  => 'glm-4.5-air',
        'image' => 'flux-max',
    ];
    
    protected array $middleware = [];

    public function __construct($token = null, $handler = null)
    {
        // 支持从环境变量读取 token
        $this->token = $token ?: getenv('THINK_AI_TOKEN');
        
        if (!$this->token) {
            throw new Exception('Token 不能为空，请提供 token 参数或设置 THINK_AI_TOKEN 环境变量');
        }
        
        if (!$handler) {
            $handler = new HandlerStack(Utils::chooseHandler());
        }
        $this->handler = $handler;
    }
    
    /**
     * 创建客户端实例（静态工厂方法）
     */
    public static function create($token = null, $handler = null): self
    {
        return new self($token, $handler);
    }

    /**
     * 获取聊天 API 或使用构建器
     * 
     * @param callable|null $callback 如果提供回调，返回构建器
     * @return Chat|ChatBuilder
     */
    public function chat(?callable $callback = null)
    {
        if ($callback === null) {
            return new Chat($this);
        }
        
        // 使用构建器模式
        $builder = $this->chatBuilder();
        $callback($builder);
        return $builder;
    }

    /**
     * 获取图片 API 或使用构建器
     * 
     * @param callable|null $callback 如果提供回调，返回构建器
     * @return Images|ImageBuilder
     */
    public function images(?callable $callback = null)
    {
        if ($callback === null) {
            return new Images($this);
        }
        
        // 使用构建器模式
        $builder = $this->imageBuilder();
        $callback($builder);
        return $builder;
    }

    public function videos()
    {
        return new Videos($this);
    }

    public function audio()
    {
        return new Audio($this);
    }

    public function music()
    {
        return new Music($this);
    }

    public function embeddings()
    {
        return new Embeddings($this);
    }

    public function rerank()
    {
        return new Rerank($this);
    }

    public function plugin()
    {
        return new Plugin($this);
    }

    public function model()
    {
        return new Model($this);
    }

    public function sandbox()
    {
        return new Sandbox($this);
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }
    
    /**
     * 设置默认模型
     */
    public function setDefaultModel(string $type, string $model): self
    {
        $this->defaultModel[$type] = $model;
        return $this;
    }
    
    /**
     * 获取聊天构建器
     */
    public function chatBuilder(): ChatBuilder
    {
        return (new ChatBuilder($this))->model($this->defaultModel['chat']);
    }
    
    /**
     * 获取图片构建器
     */
    public function imageBuilder(): ImageBuilder
    {
        return (new ImageBuilder($this))->model($this->defaultModel['image']);
    }
    
    /**
     * 快捷聊天方法
     * 
     * @param string|array $message 消息内容或完整参数
     * @return string|ChatResponse
     */
    public function ask($message, bool $stream = false)
    {
        if (is_string($message)) {
            $builder = $this->chatBuilder()
                ->user($message)
                ->stream($stream);
            
            $response = $builder->send();
            
            // 如果不是流式，返回内容字符串
            if (!$stream && $response instanceof ChatResponse) {
                return $response->getContent();
            }
            
            return $response;
        }
        
        // 如果是数组，作为完整参数传递
        return $this->chat()->completions($message);
    }
    
    /**
     * 快捷图片生成方法
     * 
     * @param string|array $prompt 提示词或完整参数
     * @return string|ImageResponse
     */
    public function image($prompt, bool $returnUrl = true)
    {
        if (is_string($prompt)) {
            $response = $this->imageBuilder()
                ->prompt($prompt)
                ->generate();
            
            // 如果需要返回 URL
            if ($returnUrl) {
                return $response->getUrl();
            }
            
            return $response;
        }
        
        // 如果是数组，作为完整参数传递
        $result = $this->images()->generations($prompt);
        return new ImageResponse($result);
    }
    
    /**
     * 添加中间件
     */
    public function addMiddleware(callable $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * 获取中间件
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function createHttpClient()
    {
        $stack = $this->handler;
        
        // 应用中间件
        foreach ($this->middleware as $middleware) {
            $stack->push($middleware);
        }
        
        return new \GuzzleHttp\Client([
            'base_uri' => $this->endpoint,
            'handler'  => $stack,
            'headers'  => [
                'Authorization' => "Bearer {$this->token}",
                'User-Agent'    => 'ThinkAi/2.0',
                'Accept'        => 'application/json',
            ],
            'verify'   => false,
        ]);
    }
}
