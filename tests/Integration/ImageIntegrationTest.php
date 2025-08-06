<?php

namespace think\ai\tests\Integration;

use think\ai\Client;
use think\ai\Response\ImageResponse;
use think\ai\tests\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class ImageIntegrationTest extends TestCase
{
    /**
     * 测试图片生成流程
     */
    public function testImageGeneration()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'created' => time(),
                'data' => [
                    [
                        'url' => 'https://example.com/generated-image.png',
                        'revised_prompt' => 'A beautiful sunset over the ocean with golden colors'
                    ]
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $response = $client->imageBuilder()
            ->prompt('夕阳下的海洋')
            ->model('dall-e-3')
            ->size('1792x1024')
            ->quality('hd')
            ->style('vivid')
            ->generate();
        
        $this->assertInstanceOf(ImageResponse::class, $response);
        $this->assertEquals('https://example.com/generated-image.png', $response->getUrl());
        $this->assertEquals(1, $response->getImageCount());
    }
    
    /**
     * 测试多图片生成
     */
    public function testMultipleImageGeneration()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'created' => time(),
                'data' => [
                    ['url' => 'https://example.com/image1.png'],
                    ['url' => 'https://example.com/image2.png'],
                    ['url' => 'https://example.com/image3.png']
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $response = $client->imageBuilder()
            ->prompt('可爱的猫咪')
            ->model('dall-e-2')
            ->size('512x512')
            ->n(3)
            ->generate();
        
        $urls = $response->getUrls();
        $this->assertCount(3, $urls);
        $this->assertEquals('https://example.com/image1.png', $urls[0]);
        $this->assertEquals('https://example.com/image2.png', $urls[1]);
        $this->assertEquals('https://example.com/image3.png', $urls[2]);
    }
    
    /**
     * 测试使用回调语法生成图片
     */
    public function testImageGenerationWithCallback()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'data' => [
                    ['url' => 'https://example.com/callback-image.png']
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $builder = $client->images(function($img) {
            $img->prompt('山水画')
                ->model('dall-e-3')
                ->size('1024x1024');
        });
        
        $response = $builder->generate();
        $this->assertInstanceOf(ImageResponse::class, $response);
        $this->assertEquals('https://example.com/callback-image.png', $response->getUrl());
    }
    
    /**
     * 测试快捷图片生成方法
     */
    public function testQuickImageMethod()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'data' => [
                    ['url' => 'https://example.com/quick-image.png']
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        // 设置默认模型
        $client->setDefaultModel('image', 'flux-max');
        
        $url = $client->image('快速生成的图片');
        $this->assertEquals('https://example.com/quick-image.png', $url);
    }
    
    /**
     * 测试 Base64 格式响应
     */
    public function testBase64ImageGeneration()
    {
        $imageData = 'test image data';
        $base64Data = base64_encode($imageData);
        
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'data' => [
                    ['b64_json' => $base64Data]
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $response = $client->imageBuilder()
            ->prompt('测试图片')
            ->base64()
            ->generate();
        
        $this->assertEquals($base64Data, $response->getBase64());
        
        // 测试保存功能
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image_');
        $result = $response->saveImage($tempFile);
        
        $this->assertTrue($result);
        $this->assertFileExists($tempFile);
        $this->assertEquals($imageData, file_get_contents($tempFile));
        
        unlink($tempFile);
    }
    
    /**
     * 测试图片编辑模式
     */
    public function testImageEditMode()
    {
        $mockHandler = new MockHandler([
            $this->createMockResponse([
                'data' => [
                    ['url' => 'https://example.com/edited-image.png']
                ]
            ])
        ]);
        
        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client('test-token', $handlerStack);
        
        $response = $client->imageBuilder()
            ->edit()
            ->image('https://example.com/original.png')
            ->mask('https://example.com/mask.png')
            ->prompt('添加一只猫')
            ->generate();
        
        $this->assertEquals('https://example.com/edited-image.png', $response->getUrl());
    }
}