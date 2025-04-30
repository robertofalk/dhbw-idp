<?php

namespace App\Tests\Unit\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\UserController;
use App\Services\UserManager;
use App\Services\UserStorageInterface;
use App\Helpers\TokenHelper;
use Mockery;
use Mockery\MockInterface;

class UserControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    private $tokenHelperMock;
    private $userStorageMock;
    private $userManager;

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

        // Mock UserStorage
        $this->userStorageMock = Mockery::mock(UserStorageInterface::class);
        $this->userStorageMock->shouldReceive('getAll')->andReturn([
            ['id' => 1, 'username' => 'testuser', 'email' => 'test@example.com']
        ]);
        $this->userStorageMock->shouldReceive('save')->andReturnUsing(function ($data) {
            return array_merge(['id' => 1], $data);
        });
        $this->userStorageMock->shouldReceive('update')->andReturnUsing(function ($id, $data) {
            return array_merge(['id' => $id], $data);
        });
        $this->userStorageMock->shouldReceive('delete')->andReturn(true);

        // Create UserManager with mocked storage
        $this->userManager = new UserManager($this->userStorageMock);

        // Set up service instance
        $userController = new class($this->userManager) extends UserController {
            private $mockManager;
            protected UserManager $userManager;
            
            public function __construct($mockManager)
            {
                $this->mockManager = $mockManager;
                $this->initController(
                    \Config\Services::request(),
                    \Config\Services::response(),
                    \Config\Services::logger()
                );
                $this->initializeUserManager();
            }
            
            public function initializeUserManager()
            {
                $this->userManager = $this->mockManager;
            }
        };

        // Register the controller as a service
        \CodeIgniter\Config\Services::injectMock('usercontroller', $userController);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexWithValidToken()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->post('api/users');

        $result->assertStatus(200);
        $result->assertJSON([
            ['id' => 1, 'username' => 'testuser', 'email' => 'test@example.com']
        ]);
    }

    public function testIndexWithInvalidToken()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Content-Type' => 'application/json'
        ])
        ->post('api/users');

        $result->assertStatus(401);
        $result->assertJSON(['error' => 'Unauthorized']);
    }

    public function testCreateWithValidToken()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode([
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123'
        ]))
        ->post('api/users/create');

        $result->assertStatus(200);
        $result->assertJSON([
            'id' => 1,
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123'
        ]);
    }

    public function testUpdateWithValidToken()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode([
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ]))
        ->post('api/users/update/1');

        $result->assertStatus(200);
        $result->assertJSON([
            'id' => 1,
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ]);
    }

    public function testUpdateNonExistentUser()
    {
        $this->userStorageMock->shouldReceive('update')
            ->with(999, Mockery::any())
            ->andReturnNull();

        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->withBody(json_encode([
            'username' => 'updateduser',
            'email' => 'updated@example.com'
        ]))
        ->post('api/users/update/999');

        $result->assertStatus(404);
        $result->assertJSON(['error' => 'User not found']);
    }

    public function testDeleteWithValidToken()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->post('api/users/delete/1');

        $result->assertStatus(200);
        $result->assertJSON(['status' => 'deleted']);
    }

    public function testDeleteNonExistentUser()
    {
        $this->userStorageMock->shouldReceive('delete')
            ->with(999)
            ->andReturn(false);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer valid-token',
            'Content-Type' => 'application/json'
        ])
        ->post('api/users/delete/999');

        $result->assertStatus(404);
        $result->assertJSON(['error' => 'User not found']);
    }
} 