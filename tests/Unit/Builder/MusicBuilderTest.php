<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use think\ai\Builder\MusicBuilder;
use think\ai\Client;

class MusicBuilderTest extends TestCase
{
    private MusicBuilder $builder;
    private Client $mockClient;
    
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->builder = new MusicBuilder($this->mockClient);
    }
    
    public function testModel()
    {
        $result = $this->builder->model('music-gen-1');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['model' => 'music-gen-1'], $this->builder->getParams());
    }
    
    public function testPrompt()
    {
        $result = $this->builder->prompt('Upbeat jazz piano');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['prompt' => 'Upbeat jazz piano'], $this->builder->getParams());
    }
    
    public function testStyle()
    {
        $result = $this->builder->style('classical');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['style' => 'classical'], $this->builder->getParams());
    }
    
    public function testGenre()
    {
        $result = $this->builder->genre('jazz');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['genre' => 'jazz'], $this->builder->getParams());
    }
    
    public function testInstruments()
    {
        $instruments = ['piano', 'violin', 'drums'];
        $result = $this->builder->instruments($instruments);
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['instruments' => $instruments], $this->builder->getParams());
    }
    
    public function testTempo()
    {
        $result = $this->builder->tempo(120);
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['tempo' => 120], $this->builder->getParams());
    }
    
    public function testKey()
    {
        $result = $this->builder->key('C major');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['key' => 'C major'], $this->builder->getParams());
    }
    
    public function testDuration()
    {
        $result = $this->builder->duration(60);
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['duration' => 60], $this->builder->getParams());
    }
    
    public function testMood()
    {
        $result = $this->builder->mood('happy');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['mood' => 'happy'], $this->builder->getParams());
    }
    
    public function testQuality()
    {
        $result = $this->builder->quality('high');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['quality' => 'high'], $this->builder->getParams());
    }
    
    public function testSampleRate()
    {
        $result = $this->builder->sampleRate(44100);
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['sample_rate' => 44100], $this->builder->getParams());
    }
    
    public function testFormat()
    {
        $result = $this->builder->format('mp3');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['format' => 'mp3'], $this->builder->getParams());
    }
    
    public function testLyrics()
    {
        $result = $this->builder->lyrics('La la la');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['lyrics' => 'La la la'], $this->builder->getParams());
    }
    
    public function testReference()
    {
        $result = $this->builder->reference('https://example.com/reference.mp3');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['reference' => 'https://example.com/reference.mp3'], $this->builder->getParams());
    }
    
    public function testUser()
    {
        $result = $this->builder->userId('user123');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame(['user' => 'user123'], $this->builder->getParams());
    }
    
    public function testChaining()
    {
        $result = $this->builder
            ->model('music-gen-1')
            ->prompt('Epic orchestral')
            ->genre('classical')
            ->style('cinematic')
            ->instruments(['violin', 'cello', 'brass'])
            ->tempo(80)
            ->key('D minor')
            ->duration(180)
            ->mood('dramatic')
            ->quality('high');
        
        $this->assertInstanceOf(MusicBuilder::class, $result);
        $this->assertSame([
            'model' => 'music-gen-1',
            'prompt' => 'Epic orchestral',
            'genre' => 'classical',
            'style' => 'cinematic',
            'instruments' => ['violin', 'cello', 'brass'],
            'tempo' => 80,
            'key' => 'D minor',
            'duration' => 180,
            'mood' => 'dramatic',
            'quality' => 'high'
        ], $this->builder->getParams());
    }
}