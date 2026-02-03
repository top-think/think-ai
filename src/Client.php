<?php

namespace think\ai;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use think\ai\api\Audio;
use think\ai\api\Avatar;
use think\ai\api\Chat;
use think\ai\api\Embeddings;
use think\ai\api\Images;
use think\ai\api\Model;
use think\ai\api\Music;
use think\ai\api\Plugin;
use think\ai\api\Rerank;
use think\ai\api\Responses;
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
        $this->setupRetryMiddleware();
    }

    public function responses()
    {
        return new Responses($this);
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

    public function avatar()
    {
        return new Avatar($this);
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

    protected function setupRetryMiddleware()
    {
        $this->handler->push(Middleware::retry(
            function ($retries, Request $request, Response $response = null, RequestException $exception = null) {
                if ($retries >= 3) {
                    return false;
                }

                if ($exception instanceof ConnectException) {
                    return true;
                }

                if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504])) {
                    return true;
                }

                return false;
            },
            function ($retries) {
                return 1000 * pow(2, $retries);
            }
        ));
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
            'read_timeout' => 300,
            'verify'       => false,
        ]);
    }
}
