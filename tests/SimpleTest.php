<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

class SimpleTest extends TestCase
{
    public function testBasic()
    {
        $this->assertTrue(true);
    }
    
    public function testClientCanBeInstantiated()
    {
        $client = new \think\ai\Client('test-token');
        $this->assertInstanceOf(\think\ai\Client::class, $client);
    }
}