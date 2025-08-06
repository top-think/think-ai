<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use think\ai\Builder\VideoBuilder;
use think\ai\Client;

class VideoBuilderTest extends TestCase
{
    private VideoBuilder $builder;
    private Client $mockClient;
    
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->builder = new VideoBuilder($this->mockClient);
    }
    
    public function testModel()
    {
        $result = $this->builder->model('sora-1');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['model' => 'sora-1'], $this->builder->getParams());
    }
    
    public function testPrompt()
    {
        $result = $this->builder->prompt('A cat playing piano');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['prompt' => 'A cat playing piano'], $this->builder->getParams());
    }
    
    public function testImage()
    {
        $result = $this->builder->image('https://example.com/image.jpg');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['image' => 'https://example.com/image.jpg'], $this->builder->getParams());
    }
    
    public function testQuality()
    {
        $result = $this->builder->quality('hd');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['quality' => 'hd'], $this->builder->getParams());
    }
    
    public function testSize()
    {
        $result = $this->builder->size('1920x1080');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['size' => '1920x1080'], $this->builder->getParams());
    }
    
    public function testStyle()
    {
        $result = $this->builder->style('cinematic');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['style' => 'cinematic'], $this->builder->getParams());
    }
    
    public function testDuration()
    {
        $result = $this->builder->duration(30);
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['duration' => 30], $this->builder->getParams());
    }
    
    public function testFps()
    {
        $result = $this->builder->fps(60);
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['fps' => 60], $this->builder->getParams());
    }
    
    public function testUser()
    {
        $result = $this->builder->userId('user123');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['user' => 'user123'], $this->builder->getParams());
    }
    
    public function testResponseFormat()
    {
        $result = $this->builder->responseFormat('mp4');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame(['response_format' => 'mp4'], $this->builder->getParams());
    }
    
    public function testChaining()
    {
        $result = $this->builder
            ->model('sora-1')
            ->prompt('A beautiful sunset')
            ->quality('hd')
            ->size('1920x1080')
            ->duration(15)
            ->fps(30)
            ->style('natural');
        
        $this->assertInstanceOf(VideoBuilder::class, $result);
        $this->assertSame([
            'model' => 'sora-1',
            'prompt' => 'A beautiful sunset',
            'quality' => 'hd',
            'size' => '1920x1080',
            'duration' => 15,
            'fps' => 30,
            'style' => 'natural'
        ], $this->builder->getParams());
    }
}