<?php

namespace Tests\Unit\Services;

use App\Services\OpenAiClientWrapper;
use CodeIgniter\Test\CIUnitTestCase;
use Mockery;

class OpenAiClientWrapperTest extends CIUnitTestCase
{
    private $mockOpenAi;
    private $mockChat;
    private $wrapper;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the OpenAI client
        $this->mockOpenAi = Mockery::mock('overload:OpenAI\Client');
        $this->mockChat = Mockery::mock();
        $this->mockOpenAi->shouldReceive('chat')->andReturn($this->mockChat);
        $this->wrapper = new OpenAiClientWrapper();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testConstructorInitializesClient()
    {
        $this->assertInstanceOf(OpenAiClientWrapper::class, $this->wrapper);
    }

    public function testAskWithValidMessageReturnsResponse()
    {
        $expectedResponse = [
            'choices' => [[
                'message' => [
                    'content' => 'Test response'
                ]
            ]]
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('toArray')->andReturn($expectedResponse);

        $this->mockChat->shouldReceive('create')
            ->with(Mockery::any())
            ->andReturn($mockResponse);

        $result = $this->wrapper->ask('Test message');
        
        $this->assertEquals($expectedResponse, $result);
    }

    public function testAskWithFunctionCallReturnsFunctionResponse()
    {
        $expectedResponse = [
            'choices' => [[
                'message' => [
                    'function_call' => [
                        'name' => 'createUser',
                        'arguments' => json_encode([
                            'name' => 'testuser',
                            'role' => 'user',
                            'password' => 'password123'
                        ])
                    ]
                ]
            ]]
        ];

        $mockResponse = Mockery::mock();
        $mockResponse->shouldReceive('toArray')->andReturn($expectedResponse);

        $this->mockChat->shouldReceive('create')
            ->with(Mockery::any())
            ->andReturn($mockResponse);

        $result = $this->wrapper->ask('Create a new user');
        
        $this->assertEquals($expectedResponse, $result);
    }

    public function testAskWithInvalidApiKeyReturnsError()
    {
        $this->mockChat->shouldReceive('create')
            ->with(Mockery::any())
            ->andThrow(new \Exception('Invalid API key'));

        $result = $this->wrapper->ask('Test message');
        
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Invalid API key', $result['message']);
    }

    public function testAskWithInvalidMessageFormatReturnsError()
    {
        $this->mockChat->shouldReceive('create')
            ->with(Mockery::any())
            ->andThrow(new \Exception('Invalid message format'));

        $result = $this->wrapper->ask('');
        
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Invalid message format', $result['message']);
    }
} 