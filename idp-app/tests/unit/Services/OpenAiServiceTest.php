<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\OpenAiService;
use App\Services\OpenAiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Mockery;

class OpenAiServiceTest extends CIUnitTestCase
{
    private OpenAiService $service;
    private $mockClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock client
        $this->mockClient = Mockery::mock(OpenAiClientInterface::class);
        
        // Create a new instance of OpenAiService with the mock client
        $this->service = new OpenAiService($this->mockClient);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testAskWithSuccessfulResponse()
    {
        $expectedResponse = [
            'id' => 'chatcmpl-123',
            'object' => 'chat.completion',
            'created' => 1677652288,
            'model' => 'gpt-4.1-nano',
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => null,
                        'function_call' => [
                            'name' => 'createUser',
                            'arguments' => json_encode([
                                'name' => 'testuser',
                                'role' => 'user',
                                'password' => 'password123'
                            ])
                        ]
                    ],
                    'finish_reason' => 'function_call'
                ]
            ]
        ];
        
        // Set up the mock client to return the expected response
        $this->mockClient->shouldReceive('ask')
            ->with('Create a new user named testuser with role user and password password123')
            ->andReturn($expectedResponse);
        
        // Call the ask method
        $result = $this->service->ask('Create a new user named testuser with role user and password password123');
        
        // Assert the result
        $this->assertIsArray($result);
        $this->assertArrayHasKey('choices', $result);
        $this->assertCount(1, $result['choices']);
        $this->assertArrayHasKey('function_call', $result['choices'][0]['message']);
        $this->assertEquals('createUser', $result['choices'][0]['message']['function_call']['name']);
    }

    public function testAskWithErrorResponse()
    {
        $errorResponse = [
            'error' => true,
            'message' => 'API Error'
        ];
        
        // Set up the mock client to return an error response
        $this->mockClient->shouldReceive('ask')
            ->with('Create a new user')
            ->andReturn($errorResponse);
        
        // Call the ask method
        $result = $this->service->ask('Create a new user');
        
        // Assert the result
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertTrue($result['error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('API Error', $result['message']);
    }
} 