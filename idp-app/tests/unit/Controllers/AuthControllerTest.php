<?php

namespace Tests\Unit\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use App\Controllers\AuthController;
use App\Services\UserManager;
use App\Entities\User;
use Mockery;
use Mockery\MockInterface;

class AuthControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private UserManager|MockInterface $userManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userManagerMock = Mockery::mock(UserManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testLoginWithValidCredentials()
    {
        $testUser = new User('testuser', 'password123');
        $this->userManagerMock->shouldReceive('getAll')
            ->once()
            ->andReturn([$testUser]);

        $controller = new AuthController($this->userManagerMock);
        $result = $controller->login();

        $this->assertEquals(200, $result->getStatusCode());
        $response = json_decode($result->getBody(), true);
        $this->assertArrayHasKey('token', $response);
    }

    public function testLoginWithInvalidCredentials()
    {
        $testUser = new User('testuser', 'password123');
        $this->userManagerMock->shouldReceive('getAll')
            ->once()
            ->andReturn([$testUser]);

        $controller = new AuthController($this->userManagerMock);
        $_POST['username'] = 'wronguser';
        $_POST['password'] = 'wrongpass';
        $result = $controller->login();

        $this->assertEquals(401, $result->getStatusCode());
        $response = json_decode($result->getBody(), true);
        $this->assertEquals('Invalid credentials', $response['error']);
    }

    public function testLoginWithMissingCredentials()
    {
        $controller = new AuthController($this->userManagerMock);
        $result = $controller->login();

        $this->assertEquals(400, $result->getStatusCode());
        $response = json_decode($result->getBody(), true);
        $this->assertEquals('Missing username or password', $response['error']);
    }
} 