<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use think\ai\Builder\AudioBuilder;
use think\ai\Client;

class AudioBuilderTest extends TestCase
{
    private AudioBuilder $builder;
    private Client $mockClient;
    
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->builder = new AudioBuilder($this->mockClient);
    }
    
    public function testModel()
    {
        $result = $this->builder->model('whisper-1');
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['model' => 'whisper-1'], $this->builder->getParams());
    }
    
    public function testInput()
    {
        $result = $this->builder->input('Hello world');
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['input' => 'Hello world'], $this->builder->getParams());
    }
    
    public function testFile()
    {
        $result = $this->builder->file('/path/to/audio.mp3');
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['file' => '/path/to/audio.mp3'], $this->builder->getParams());
    }
    
    public function testPrompt()
    {
        $result = $this->builder->prompt('Transcribe this audio');
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['prompt' => 'Transcribe this audio'], $this->builder->getParams());
    }
    
    public function testVoice()
    {
        $result = $this->builder->voice('alloy');
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['voice' => 'alloy'], $this->builder->getParams());
    }
    
    public function testResponseFormat()
    {
        $result = $this->builder->responseFormat('json');
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['response_format' => 'json'], $this->builder->getParams());
    }
    
    public function testSpeed()
    {
        $result = $this->builder->speed(1.5);
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['speed' => 1.5], $this->builder->getParams());
    }
    
    public function testLanguage()
    {
        $result = $this->builder->language('en');
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['language' => 'en'], $this->builder->getParams());
    }
    
    public function testTemperature()
    {
        $result = $this->builder->temperature(0.7);
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['temperature' => 0.7], $this->builder->getParams());
    }
    
    public function testTimestampGranularities()
    {
        $result = $this->builder->timestampGranularities(['word', 'segment']);
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame(['timestamp_granularities' => ['word', 'segment']], $this->builder->getParams());
    }
    
    public function testChaining()
    {
        $result = $this->builder
            ->model('whisper-1')
            ->file('/path/to/audio.mp3')
            ->language('en')
            ->responseFormat('json')
            ->temperature(0.5);
        
        $this->assertInstanceOf(AudioBuilder::class, $result);
        $this->assertSame([
            'model' => 'whisper-1',
            'file' => '/path/to/audio.mp3',
            'language' => 'en',
            'response_format' => 'json',
            'temperature' => 0.5
        ], $this->builder->getParams());
    }
}