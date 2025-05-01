<?php

namespace App\Services;

use RuntimeException;

class UserManager
{
    private UserStorageInterface $storage;

    public function __construct()
    {

        $useFile = env('FILE_STORAGE');
        $this->storage = $useFile ? new FileUserStorage() : throw new \RuntimeException("Only FILE_STORAGE=true is supported right now");

        $users = $this->storage->getAll();
        if (empty($users)) {
            $this->create([
                'username' => 'admin',
                'password' => 'admin',
                'role' => 'admin'
            ]);
        }
    }

    private function newSalt(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function create(array $data): array
    {
        // Create salt and hash the password
        $salt = $this->newSalt();
        $passwordHash = hash_hmac('sha256', $data['password'], $salt);
        
        $user['username'] = $data['username'];
        $user['role'] = $data['role'];
        $user['salt'] = $salt;
        $user['password'] = $passwordHash;

        return $this->storage->save($user);
    }

    public function get(array $data): array
    {
        return $this->storage->get($data);
    }

    public function getAll(): array
    {
        return $this->storage->getAll();
    }

    public function update(array $data): void
    {
        $this->storage->update($data);
    }

    public function delete(int $id): void
    {
        $this->storage->delete($id);
    }
}
