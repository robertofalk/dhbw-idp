<?php

namespace Tests\Unit\Services;

use App\Services\UserManager;
use App\Services\UserStorageInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Mockery;

class UserManagerTest extends CIUnitTestCase
{
    private UserManager $manager;
    private $mockStorage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock storage
        $this->mockStorage = Mockery::mock(UserStorageInterface::class);
        
        // Create a new instance of UserManager
        $this->manager = new UserManager();
        
        // Use reflection to set the private storage property
        $reflection = new \ReflectionClass($this->manager);
        $property = $reflection->getProperty('storage');
        $property->setAccessible(true);
        $property->setValue($this->manager, $this->mockStorage);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testCreate()
    {
        $userData = [
            'name' => 'testuser',
            'role' => 'user',
            'password' => 'password123'
        ];

        $expectedUser = array_merge($userData, ['id' => 1]);

        $this->mockStorage->shouldReceive('save')
            ->with($userData)
            ->once()
            ->andReturn($expectedUser);

        $result = $this->manager->create($userData);

        $this->assertEquals($expectedUser, $result);
    }

    public function testGetAll()
    {
        $expectedUsers = [
            [
                'id' => 1,
                'name' => 'admin',
                'role' => 'admin'
            ],
            [
                'id' => 2,
                'name' => 'user1',
                'role' => 'user'
            ]
        ];

        $this->mockStorage->shouldReceive('getAll')
            ->once()
            ->andReturn($expectedUsers);

        $result = $this->manager->getAll();

        $this->assertEquals($expectedUsers, $result);
    }

    public function testUpdate()
    {
        $userId = 1;
        $updateData = [
            'name' => 'updateduser',
            'role' => 'editor'
        ];

        $expectedUser = array_merge(['id' => $userId], $updateData);

        $this->mockStorage->shouldReceive('update')
            ->with($userId, $updateData)
            ->once()
            ->andReturn($expectedUser);

        $result = $this->manager->update($userId, $updateData);

        $this->assertEquals($expectedUser, $result);
    }

    public function testUpdateNonExistentUser()
    {
        $userId = 999;
        $updateData = [
            'name' => 'updateduser',
            'role' => 'editor'
        ];

        $this->mockStorage->shouldReceive('update')
            ->with($userId, $updateData)
            ->once()
            ->andReturn(null);

        $result = $this->manager->update($userId, $updateData);

        $this->assertNull($result);
    }

    public function testDelete()
    {
        $userId = 1;

        $this->mockStorage->shouldReceive('delete')
            ->with($userId)
            ->once()
            ->andReturn(true);

        $result = $this->manager->delete($userId);

        $this->assertTrue($result);
    }

    public function testDeleteNonExistentUser()
    {
        $userId = 999;

        $this->mockStorage->shouldReceive('delete')
            ->with($userId)
            ->once()
            ->andReturn(false);

        $result = $this->manager->delete($userId);

        $this->assertFalse($result);
    }
} 