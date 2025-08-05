<?php

namespace think\ai\tests\Unit\Response;

use think\ai\Response\ImageResponse;
use think\ai\tests\TestCase;

class ImageResponseTest extends TestCase
{
    private function createImageData(): array
    {
        return [
            'created' => 1677652288,
            'data' => [
                [
                    'url' => 'https://example.com/image1.png',
                    'revised_prompt' => 'A cute cat'
                ],
                [
                    'url' => 'https://example.com/image2.png',
                    'revised_prompt' => 'A cute cat'
                ]
            ]
        ];
    }
    
    private function createBase64ImageData(): array
    {
        return [
            'created' => 1677652288,
            'data' => [
                [
                    'b64_json' => base64_encode('image data 1'),
                ],
                [
                    'b64_json' => base64_encode('image data 2'),
                ]
            ]
        ];
    }
    
    public function testGetImages()
    {
        $response = new ImageResponse($this->createImageData());
        $images = $response->getImages();
        
        $this->assertIsArray($images);
        $this->assertCount(2, $images);
    }
    
    public function testGetUrl()
    {
        $response = new ImageResponse($this->createImageData());
        $this->assertEquals('https://example.com/image1.png', $response->getUrl());
    }
    
    public function testGetUrls()
    {
        $response = new ImageResponse($this->createImageData());
        $urls = $response->getUrls();
        
        $this->assertIsArray($urls);
        $this->assertCount(2, $urls);
        $this->assertEquals('https://example.com/image1.png', $urls[0]);
        $this->assertEquals('https://example.com/image2.png', $urls[1]);
    }
    
    public function testGetBase64()
    {
        $response = new ImageResponse($this->createBase64ImageData());
        $this->assertEquals(base64_encode('image data 1'), $response->getBase64());
    }
    
    public function testGetAllBase64()
    {
        $response = new ImageResponse($this->createBase64ImageData());
        $base64Data = $response->getAllBase64();
        
        $this->assertIsArray($base64Data);
        $this->assertCount(2, $base64Data);
        $this->assertEquals(base64_encode('image data 1'), $base64Data[0]);
        $this->assertEquals(base64_encode('image data 2'), $base64Data[1]);
    }
    
    public function testGetImageCount()
    {
        $response = new ImageResponse($this->createImageData());
        $this->assertEquals(2, $response->getImageCount());
    }
    
    public function testEmptyResponse()
    {
        $response = new ImageResponse([]);
        
        $this->assertEquals([], $response->getImages());
        $this->assertEquals('', $response->getUrl());
        $this->assertEquals([], $response->getUrls());
        $this->assertEquals('', $response->getBase64());
        $this->assertEquals([], $response->getAllBase64());
        $this->assertEquals(0, $response->getImageCount());
    }
    
    public function testSaveImage()
    {
        // 创建临时文件路径
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image_');
        
        // 模拟 base64 图片数据
        $imageData = 'test image content';
        $data = [
            'data' => [
                ['b64_json' => base64_encode($imageData)]
            ]
        ];
        
        $response = new ImageResponse($data);
        $result = $response->saveImage($tempFile);
        
        $this->assertTrue($result);
        $this->assertFileExists($tempFile);
        $this->assertEquals($imageData, file_get_contents($tempFile));
        
        // 清理
        unlink($tempFile);
    }
    
    public function testSaveImageInvalidIndex()
    {
        $response = new ImageResponse($this->createImageData());
        $result = $response->saveImage('test.png', 10); // 不存在的索引
        
        $this->assertFalse($result);
    }
    
    public function testSaveAllImages()
    {
        // 创建临时目录
        $tempDir = sys_get_temp_dir() . '/test_images_' . uniqid();
        mkdir($tempDir);
        
        // 模拟 base64 图片数据
        $data = [
            'data' => [
                ['b64_json' => base64_encode('image 1')],
                ['b64_json' => base64_encode('image 2')]
            ]
        ];
        
        $response = new ImageResponse($data);
        $savedPaths = $response->saveAllImages($tempDir, 'test');
        
        $this->assertCount(2, $savedPaths);
        foreach ($savedPaths as $path) {
            $this->assertFileExists($path);
            $this->assertStringStartsWith($tempDir, $path);
            $this->assertStringContainsString('test_', basename($path));
        }
        
        // 清理
        foreach ($savedPaths as $path) {
            unlink($path);
        }
        rmdir($tempDir);
    }
}