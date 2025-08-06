<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use think\ai\Builder\EmbeddingsBuilder;
use think\ai\Client;

class EmbeddingsBuilderTest extends TestCase
{
    private EmbeddingsBuilder $builder;
    private Client $mockClient;
    
    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->builder = new EmbeddingsBuilder($this->mockClient);
    }
    
    public function testModel()
    {
        $result = $this->builder->model('text-embedding-ada-002');
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['model' => 'text-embedding-ada-002'], $this->builder->getParams());
    }
    
    public function testInputWithString()
    {
        $result = $this->builder->input('Hello world');
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['input' => 'Hello world'], $this->builder->getParams());
    }
    
    public function testInputWithArray()
    {
        $inputs = ['Hello', 'World'];
        $result = $this->builder->input($inputs);
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['input' => $inputs], $this->builder->getParams());
    }
    
    public function testAddInput()
    {
        $result = $this->builder
            ->addInput('First text')
            ->addInput('Second text')
            ->addInput('Third text');
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['input' => ['First text', 'Second text', 'Third text']], $this->builder->getParams());
    }
    
    public function testAddInputAfterStringInput()
    {
        $result = $this->builder
            ->input('Initial text')
            ->addInput('Additional text');
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['input' => ['Initial text', 'Additional text']], $this->builder->getParams());
    }
    
    public function testEncodingFormat()
    {
        $result = $this->builder->encodingFormat('float');
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['encoding_format' => 'float'], $this->builder->getParams());
    }
    
    public function testDimensions()
    {
        $result = $this->builder->dimensions(1536);
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['dimensions' => 1536], $this->builder->getParams());
    }
    
    public function testUser()
    {
        $result = $this->builder->userId('user123');
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame(['user' => 'user123'], $this->builder->getParams());
    }
    
    public function testChaining()
    {
        $result = $this->builder
            ->model('text-embedding-ada-002')
            ->input('Sample text')
            ->encodingFormat('base64')
            ->dimensions(1536)
            ->userId('user456');
        
        $this->assertInstanceOf(EmbeddingsBuilder::class, $result);
        $this->assertSame([
            'model' => 'text-embedding-ada-002',
            'input' => 'Sample text',
            'encoding_format' => 'base64',
            'dimensions' => 1536,
            'user' => 'user456'
        ], $this->builder->getParams());
    }
    
    public function testCosineSimilarity()
    {
        $vec1 = [1, 0, 0];
        $vec2 = [0, 1, 0];
        
        $similarity = EmbeddingsBuilder::cosineSimilarity($vec1, $vec2);
        $this->assertEqualsWithDelta(0, $similarity, 0.0001);
        
        $vec3 = [1, 0, 0];
        $vec4 = [1, 0, 0];
        
        $similarity2 = EmbeddingsBuilder::cosineSimilarity($vec3, $vec4);
        $this->assertEqualsWithDelta(1, $similarity2, 0.0001);
        
        $vec5 = [0.5, 0.5, 0];
        $vec6 = [0.5, 0.5, 0];
        
        $similarity3 = EmbeddingsBuilder::cosineSimilarity($vec5, $vec6);
        $this->assertEqualsWithDelta(1, $similarity3, 0.0001);
    }
    
    public function testCosineSimilarityWithDifferentDimensions()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('向量维度必须相同');
        
        $vec1 = [1, 0, 0];
        $vec2 = [1, 0];
        
        EmbeddingsBuilder::cosineSimilarity($vec1, $vec2);
    }
    
    public function testEuclideanDistance()
    {
        $vec1 = [0, 0, 0];
        $vec2 = [3, 4, 0];
        
        $distance = EmbeddingsBuilder::euclideanDistance($vec1, $vec2);
        $this->assertEqualsWithDelta(5, $distance, 0.0001);
        
        $vec3 = [1, 1, 1];
        $vec4 = [1, 1, 1];
        
        $distance2 = EmbeddingsBuilder::euclideanDistance($vec3, $vec4);
        $this->assertEqualsWithDelta(0, $distance2, 0.0001);
    }
    
    public function testEuclideanDistanceWithDifferentDimensions()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('向量维度必须相同');
        
        $vec1 = [1, 0, 0];
        $vec2 = [1, 0];
        
        EmbeddingsBuilder::euclideanDistance($vec1, $vec2);
    }
    
    public function testCosineSimilarityWithZeroVectors()
    {
        $vec1 = [0, 0, 0];
        $vec2 = [0, 0, 0];
        
        $similarity = EmbeddingsBuilder::cosineSimilarity($vec1, $vec2);
        $this->assertEquals(0, $similarity);
    }
}