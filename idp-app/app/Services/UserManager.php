<?php

namespace App\Services;

use App\Models\User;
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
            $this->create('admin', 'admin', 'admin');
        }
    }

    public function create(string $username, string $password, string $role): User
    {
        // Create salt and hash the password
        $salt = bin2hex(random_bytes(16));
        $passwordHash = hash_hmac('sha256', $password, $salt);

        $user = new User(
            username: $username,
            password: $passwordHash,
            salt: $salt,
            role: $role
        );
        
        return $this->storage->save($user);
    }

    public function get(string $username, string $password): User
    {
        return $this->storage->get(username: $username, password: $password);
    }

    public function getAll(): array
    {
        return $this->storage->getAll();
    }

    public function update(int $id, ?string $username = null, ?string $password = null, ?string $role = null): void
    {
        $user = $this->storage->get(id: $id);

        if ($username !== null) {
            $user->setUsername($username);
        }
        if ($password !== null) {
            $salt = bin2hex(random_bytes(16));
            $passwordHash = hash_hmac('sha256', $password, $salt);
            $user->setPassword($passwordHash);
            $user->setSalt($salt);
        }
        if ($role !== null) {
            $user->setRole($role);
        }

        $this->storage->update($id, $user);
    }

    public function delete(int $id): void
    {
        $this->storage->delete($id);
    }
}
