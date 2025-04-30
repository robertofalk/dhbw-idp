<?php

namespace Tests\Unit\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\ChatController;
use App\Services\OpenAiService;
use App\Services\UserManager;
use App\Helpers\TokenHelper;
use Mockery;
use Mockery\MockInterface;

class ChatControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private $mockOpenAiService;
    private $mockUserManager;
    private $tokenHelperMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock TokenHelper
        $this->tokenHelperMock = Mockery::mock('alias:' . TokenHelper::class);
        $this->tokenHelperMock->shouldReceive('validateToken')
            ->with('Bearer valid-token')
            ->andReturn(['id' => 1, 'username' => 'testuser']);
        $this->tokenHelperMock->shouldReceive('validateToken')
            ->with('Bearer invalid-token')
            ->andReturnNull();

        // Mock OpenAiService
        $this->mockOpenAiService = Mockery::mock(OpenAiService::class);
        
        // Mock UserManager
        $this->mockUserManager = Mockery::mock(UserManager::class);
        
        // Set up service instance
        $chatController = new class($this->mockOpenAiService, $this->mockUserManager) extends ChatController {
            private $mockOpenAi;
            private $mockManager;
            
            public function __construct($mockOpenAi, $mockManager)
            {
                $this->mockOpenAi = $mockOpenAi;
                $this->mockManager = $mockManager;
                $this->initController(
                    \Config\Services::request(),
                    \Config\Services::response(),
                    \Config\Services::logger()
                );
                $this->initializeServices();
            }
            
            public function initializeServices()
            {
                $reflection = new \ReflectionClass(ChatController::class);
                $openAiProperty = $reflection->getProperty('openAiService');
                $openAiProperty->setAccessible(true);
                $openAiProperty->setValue($this, $this->mockOpenAi);
                
                $managerProperty = $reflection->getProperty('userManager');
                $managerProperty->setAccessible(true);
                $managerProperty->setValue($this, $this->mockManager);
            }
        };

        // Register the controller as a service
        \CodeIgniter\Config\Services::injectMock('chatcontroller', $chatController);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testMessageWithInvalidToken()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode(['message' => 'test message']))
        ->post('chat');

        $result->assertStatus(401);
        $result->assertJSON(['error' => 'Unauthorized']);
    }

    public function testMessageWithMissingMessage()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode([]))
        ->post('chat');

        $result->assertStatus(400);
        $result->assertJSON(['error' => 'Missing message']);
    }

    public function testMessageWithCreateUserFunction()
    {
        $this->mockOpenAiService->shouldReceive('ask')
            ->with('create a new user')
            ->andReturn([
                'choices' => [[
                    'message' => [
                        'function_call' => [
                            'name' => 'createUser',
                            'arguments' => json_encode([
                                'name' => 'newuser',
                                'role' => 'user',
                                'password' => 'password123'
                            ])
                        ]
                    ]
                ]]
            ]);

        $this->mockUserManager->shouldReceive('create')
            ->with(Mockery::any())
            ->andReturn(['id' => 1, 'name' => 'newuser']);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode(['message' => 'create a new user']))
        ->post('chat');

        $result->assertStatus(200);
        $result->assertJSON([
            'reply' => "✅ User 'newuser' created successfully!",
            'action' => 'refresh'
        ]);
    }

    public function testMessageWithUpdateUserFunction()
    {
        $this->mockOpenAiService->shouldReceive('ask')
            ->with('update user 1')
            ->andReturn([
                'choices' => [[
                    'message' => [
                        'function_call' => [
                            'name' => 'updateUser',
                            'arguments' => json_encode([
                                'id' => 1,
                                'name' => 'updateduser',
                                'role' => 'editor'
                            ])
                        ]
                    ]
                ]]
            ]);

        $this->mockUserManager->shouldReceive('update')
            ->with(1, Mockery::any())
            ->andReturn(['id' => 1, 'name' => 'updateduser']);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode(['message' => 'update user 1']))
        ->post('chat');

        $result->assertStatus(200);
        $result->assertJSON([
            'reply' => "✅ User #1 updated!",
            'action' => 'refresh'
        ]);
    }

    public function testMessageWithDeleteUserFunction()
    {
        $this->mockOpenAiService->shouldReceive('ask')
            ->with('delete user 1')
            ->andReturn([
                'choices' => [[
                    'message' => [
                        'function_call' => [
                            'name' => 'deleteUser',
                            'arguments' => json_encode([
                                'id' => 1
                            ])
                        ]
                    ]
                ]]
            ]);

        $this->mockUserManager->shouldReceive('delete')
            ->with(1)
            ->andReturn(true);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode(['message' => 'delete user 1']))
        ->post('chat');

        $result->assertStatus(200);
        $result->assertJSON([
            'reply' => "🗑️ User #1 deleted.",
            'action' => 'refresh'
        ]);
    }

    public function testMessageWithUnsupportedFunction()
    {
        $this->mockOpenAiService->shouldReceive('ask')
            ->with('unsupported function')
            ->andReturn([
                'choices' => [[
                    'message' => [
                        'function_call' => [
                            'name' => 'unsupportedFunction',
                            'arguments' => '{}'
                        ]
                    ]
                ]]
            ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode(['message' => 'unsupported function']))
        ->post('chat');

        $result->assertStatus(200);
        $result->assertJSON([
            'reply' => "⚠️ Function 'unsupportedFunction' is not supported yet."
        ]);
    }

    public function testMessageWithRegularResponse()
    {
        $this->mockOpenAiService->shouldReceive('ask')
            ->with('hello')
            ->andReturn([
                'choices' => [[
                    'message' => [
                        'content' => 'Hi there!'
                    ]
                ]]
            ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode(['message' => 'hello']))
        ->post('chat');

        $result->assertStatus(200);
        $result->assertJSON([
            'reply' => 'Hi there!'
        ]);
    }
} 