<?php

namespace think\ai\tests\Unit\Builder;

use think\ai\Builder\ImageBuilder;
use think\ai\Client;
use think\ai\tests\TestCase;
use Mockery;

class ImageBuilderTest extends TestCase
{
    private $mockClient;
    private $builder;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = Mockery::mock(Client::class);
        $this->builder = new ImageBuilder($this->mockClient);
    }
    
    public function testPrompt()
    {
        $result = $this->builder->prompt('A beautiful sunset');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('A beautiful sunset', $params['prompt']);
    }
    
    public function testModel()
    {
        $result = $this->builder->model('dall-e-3');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('dall-e-3', $params['model']);
    }
    
    public function testN()
    {
        $result = $this->builder->n(3);
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals(3, $params['n']);
    }
    
    public function testSize()
    {
        $result = $this->builder->size('1792x1024');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('1792x1024', $params['size']);
    }
    
    public function testQuality()
    {
        $result = $this->builder->quality('hd');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('hd', $params['quality']);
    }
    
    public function testStyle()
    {
        $result = $this->builder->style('natural');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('natural', $params['style']);
    }
    
    public function testResponseFormat()
    {
        $result = $this->builder->responseFormat('b64_json');
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('b64_json', $params['response_format']);
    }
    
    public function testBase64()
    {
        $result = $this->builder->base64();
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('b64_json', $params['response_format']);
    }
    
    public function testUrl()
    {
        $result = $this->builder->url();
        
        $this->assertSame($this->builder, $result);
        $params = $this->builder->getParams();
        $this->assertEquals('url', $params['response_format']);
    }
    
    public function testEndpointSwitching()
    {
        // Default is generations
        $this->assertEquals('generations', $this->getPrivateProperty('endpoint'));
        
        // Switch to edit
        $this->builder->edit();
        $this->assertEquals('edit', $this->getPrivateProperty('endpoint'));
        
        // Switch to inpainting
        $this->builder->inpainting();
        $this->assertEquals('inpainting', $this->getPrivateProperty('endpoint'));
        
        // Switch to outpainting
        $this->builder->outpainting();
        $this->assertEquals('outpainting', $this->getPrivateProperty('endpoint'));
        
        // Switch to upscale
        $this->builder->upscale();
        $this->assertEquals('upscale', $this->getPrivateProperty('endpoint'));
        
        // Switch to poster
        $this->builder->poster();
        $this->assertEquals('poster', $this->getPrivateProperty('endpoint'));
    }
    
    public function testChainedMethods()
    {
        $this->builder
            ->prompt('A futuristic city')
            ->model('dall-e-3')
            ->size('1792x1024')
            ->quality('hd')
            ->style('vivid')
            ->n(2)
            ->responseFormat('url');
        
        $params = $this->builder->getParams();
        
        $this->assertEquals('A futuristic city', $params['prompt']);
        $this->assertEquals('dall-e-3', $params['model']);
        $this->assertEquals('1792x1024', $params['size']);
        $this->assertEquals('hd', $params['quality']);
        $this->assertEquals('vivid', $params['style']);
        $this->assertEquals(2, $params['n']);
        $this->assertEquals('url', $params['response_format']);
    }
    
    public function testGenerateWithoutPrompt()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('必须设置提示词');
        
        $this->builder->generate();
    }
    
    private function getPrivateProperty(string $property)
    {
        $reflection = new \ReflectionClass($this->builder);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($this->builder);
    }
}