<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\FileUserStorage;
use CodeIgniter\Test\CIUnitTestCase;

class FileUserStorageTest extends CIUnitTestCase
{
    private FileUserStorage $storage;
    private string $testFilePath;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary file path for testing
        $this->testFilePath = WRITEPATH . 'users_test.json';
        
        // Create a new instance of FileUserStorage with test file path
        $this->storage = new FileUserStorage($this->testFilePath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up the test file
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testInitializationCreatesAdminUser()
    {
        $users = $this->storage->getAll();
        
        $this->assertCount(1, $users);
        $this->assertEquals('admin', $users[0]['name']);
        $this->assertEquals('admin', $users[0]['role']);
    }

    public function testSaveNewUser()
    {
        $newUser = [
            'name' => 'testuser',
            'role' => 'user',
            'password' => 'password123'
        ];

        $savedUser = $this->storage->save($newUser);

        $this->assertNotNull($savedUser['id']);
        $this->assertEquals('testuser', $savedUser['name']);
        $this->assertEquals('user', $savedUser['role']);
        $this->assertNotEquals('password123', $savedUser['password']); // Password should be hashed
        $this->assertNotNull($savedUser['salt']);
    }

    public function testUpdateUser()
    {
        // First save a user
        $newUser = [
            'name' => 'testuser',
            'role' => 'user',
            'password' => 'password123'
        ];
        $savedUser = $this->storage->save($newUser);

        // Update the user
        $updateData = [
            'name' => 'updateduser',
            'role' => 'editor'
        ];
        $updatedUser = $this->storage->update($savedUser['id'], $updateData);

        $this->assertNotNull($updatedUser);
        $this->assertEquals('updateduser', $updatedUser['name']);
        $this->assertEquals('editor', $updatedUser['role']);
    }

    public function testDeleteUser()
    {
        // First save a user
        $newUser = [
            'name' => 'testuser',
            'role' => 'user',
            'password' => 'password123'
        ];
        $savedUser = $this->storage->save($newUser);

        // Delete the user
        $result = $this->storage->delete($savedUser['id']);

        $this->assertTrue($result);
        
        // Verify user is deleted
        $users = $this->storage->getAll();
        $this->assertCount(1, $users); // Only admin user should remain
    }
} 