<?php

namespace think\ai\tests\Unit;

use think\ai\Client;
use think\ai\tests\TestCase;
use think\ai\Exception;
use think\ai\api\Chat;
use think\ai\api\Images;
use think\ai\Builder\ChatBuilder;
use think\ai\Builder\ImageBuilder;

class ClientTest extends TestCase
{
    public function testConstructorWithToken()
    {
        $client = new Client('test-token');
        $this->assertInstanceOf(Client::class, $client);
    }
    
    public function testConstructorWithEnvironmentVariable()
    {
        putenv('THINK_AI_TOKEN=env-token');
        $client = new Client();
        $this->assertInstanceOf(Client::class, $client);
        putenv('THINK_AI_TOKEN='); // 清理
    }
    
    public function testConstructorThrowsExceptionWithoutToken()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token 不能为空');
        
        putenv('THINK_AI_TOKEN='); // 确保环境变量为空
        new Client();
    }
    
    public function testStaticCreateMethod()
    {
        $client = Client::create('test-token');
        $this->assertInstanceOf(Client::class, $client);
    }
    
    public function testSetEndpoint()
    {
        $client = new Client('test-token');
        $result = $client->setEndpoint('https://custom.api.com/');
        
        $this->assertSame($client, $result); // 测试流式接口
    }
    
    public function testSetDefaultModel()
    {
        $client = new Client('test-token');
        $result = $client->setDefaultModel('chat', 'gpt-4');
        
        $this->assertSame($client, $result);
    }
    
    public function testChatMethodReturnsApi()
    {
        $client = new Client('test-token');
        $chat = $client->chat();
        
        $this->assertInstanceOf(Chat::class, $chat);
    }
    
    public function testChatMethodWithCallback()
    {
        $client = new Client('test-token');
        $builder = $client->chat(function($chat) {
            $this->assertInstanceOf(ChatBuilder::class, $chat);
        });
        
        $this->assertInstanceOf(ChatBuilder::class, $builder);
    }
    
    public function testImagesMethodReturnsApi()
    {
        $client = new Client('test-token');
        $images = $client->images();
        
        $this->assertInstanceOf(Images::class, $images);
    }
    
    public function testImagesMethodWithCallback()
    {
        $client = new Client('test-token');
        $builder = $client->images(function($img) {
            $this->assertInstanceOf(ImageBuilder::class, $img);
        });
        
        $this->assertInstanceOf(ImageBuilder::class, $builder);
    }
    
    public function testChatBuilder()
    {
        $client = new Client('test-token');
        $builder = $client->chatBuilder();
        
        $this->assertInstanceOf(ChatBuilder::class, $builder);
    }
    
    public function testImageBuilder()
    {
        $client = new Client('test-token');
        $builder = $client->imageBuilder();
        
        $this->assertInstanceOf(ImageBuilder::class, $builder);
    }
    
    public function testAddMiddleware()
    {
        $client = new Client('test-token');
        $middleware = function($handler) {
            return function($request, $options) use ($handler) {
                return $handler($request, $options);
            };
        };
        
        $result = $client->addMiddleware($middleware);
        $this->assertSame($client, $result);
        
        $middlewares = $client->getMiddleware();
        $this->assertCount(1, $middlewares);
        $this->assertSame($middleware, $middlewares[0]);
    }
    
    public function testCreateHttpClient()
    {
        $client = new Client('test-token');
        $httpClient = $client->createHttpClient();
        
        $this->assertInstanceOf(\GuzzleHttp\Client::class, $httpClient);
        
        // 测试配置
        $config = $httpClient->getConfig();
        $this->assertEquals('https://ai.topthink.com/', $config['base_uri']->__toString());
        $this->assertEquals('Bearer test-token', $config['headers']['Authorization']);
        $this->assertEquals('ThinkAi/2.0', $config['headers']['User-Agent']);
    }
}