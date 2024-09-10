<?php

namespace think\ai;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Utils;
use think\ai\api\Audio;
use think\ai\api\Chat;
use think\ai\api\Embeddings;
use think\ai\api\Images;
use think\ai\api\Model;
use think\ai\api\Plugin;
use think\ai\api\Rerank;
use think\ai\api\Sandbox;
use think\ai\api\Videos;

class Client
{
    protected $endpoint = 'https://ai.topthink.com/';

    protected $token;

    protected $handler;

    public function __construct($token, $handler = null)
    {
        $this->token = $token;
        if (!$handler) {
            $handler = new HandlerStack(Utils::chooseHandler());
        }
        $this->handler = $handler;
    }

    public function chat()
    {
        return new Chat($this);
    }

    public function images()
    {
        return new Images($this);
    }

    public function videos()
    {
        return new Videos($this);
    }

    public function audio()
    {
        return new Audio($this);
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

    public function createHttpClient()
    {
        return new \GuzzleHttp\Client([
            'base_uri' => $this->endpoint,
            'handler'  => $this->handler,
            'headers'  => [
                'Authorization' => "Bearer {$this->token}",
                'User-Agent'    => 'ThinkAi/1.0',
                'Accept'        => 'application/json',
            ],
            'verify'   => false,
        ]);
    }
}
