<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;
use function PHPUnit\Framework\throwException;

class FileUserStorage implements UserStorageInterface
{
    private string $filePath;
    private array $users = [];
    private int $nextId = 1;

    public function __construct()
    {
        $this->filePath = WRITEPATH . 'users.json';
        $this->load();
    }

    private function load(): void
    {
        if (file_exists($this->filePath)) {
            $usersData = json_decode(file_get_contents($this->filePath), true) ?? [];
            // Convert to username-keyed array
            foreach ($usersData as $user) {
                $userObj = new User(
                    username: $user['username'],
                    password: $user['password'],
                    role: $user['role'],
                    salt: $user['salt'],
                    id: $user['id']
                );
                $this->users[$userObj->getUsername()] = $userObj;
            }
            $ids = array_map(fn($user) => $user->getId(), $this->users);
            $this->nextId = $ids ? max($ids) + 1 : 1;
        }
    }

    private function saveToFile(): void
    {
        // Convert User objects to arrays for storage
        $usersToSave = array_map(fn($user) => json_decode($user->serialize(), true), $this->users);
        file_put_contents($this->filePath, json_encode($usersToSave, JSON_PRETTY_PRINT));
    }

    public function get(?int $id = null, ?string $username = null, ?string $password = null): User
    {

        if ($id !== null) {
            $user = current(array_filter($this->users, fn($user) => $user->getId() === $id));
            if (!$user) {
                throw new \RuntimeException("User with ID {$id} not found");
            }
            return $user;
        }

        if ($username === null || $password === null) {
            throw new \InvalidArgumentException("Id or username and password are required");
        }

        if (!isset($this->users[$username])) {
            throw new \RuntimeException("Username {$username} not found");
        }

        $user = $this->users[$username];
        $attemptHash = hash_hmac('sha256', $password, $user->getSalt());
        if ($attemptHash === $user->getPassword())
            return $user;

        throw new \RuntimeException("Username or password not match");
    }

    public function getAll(): array
    {
        return array_values($this->users);
    }

    public function save(User $user): User
    {    
        if (isset($this->users[$user->getUsername()])) {
            throw new \RuntimeException("Username {$user->getUsername()} already exists");
        }

        if (!$user->getId())
            $user->setId($this->nextId++);
        $this->users[$user->getUsername()] = $user;
        $this->saveToFile();
        return $user;
    }
    
    public function update(int $id, User $updatedUser): void
    {
        $user = current(array_filter($this->users, fn($user) => $user->getId() === $id));
        if (!$user) {
            throw new \RuntimeException("User with ID {$id} not found");
        }
        $this->delete($id);
        $this->save($updatedUser);
    }

    public function delete(int $id): void
    {
        $userToDelete = array_filter($this->users, fn($user) => $user->getId() === $id);

        if (empty($userToDelete)) {
            throw new \InvalidArgumentException("User with ID {$id} does not exist.");
        }

        $username = array_key_first($userToDelete);
        unset($this->users[$username]);
        $this->saveToFile();
    }
}
