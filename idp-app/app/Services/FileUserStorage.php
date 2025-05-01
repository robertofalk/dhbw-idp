<?php

namespace App\Services;

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
                $this->users[$user['username']] = $user;
            }
            $ids = array_column($this->users, 'id');
            $this->nextId = $ids ? max($ids) + 1 : 1;
        }
    }

    private function saveToFile(): void
    {
        // Convert back to sequential array for storage
        $usersToSave = array_values($this->users);
        file_put_contents($this->filePath, json_encode($usersToSave, JSON_PRETTY_PRINT));
    }

    public function get(array $data): array
    {
        if (!isset($this->users[$data['username']])) {
            throw new \RuntimeException("Username {$data['username']} not found");
        }

        $user = $this->users[$data['username']];
        
        $attemptHash = hash_hmac('sha256', $data['password'], $user['salt']);
        if ($attemptHash === $user['password']) {
            return $user;
        }
        throw new \RuntimeException("Username or password not match");
    }

    public function getAll(): array
    {
        return array_values($this->users);
    }

    public function save(array $user): array
    {    
        if (isset($this->users[$user['username']])) {
            throw new \RuntimeException("Username {$user['username']} already exists");
        }

        $user['id'] = $this->nextId++;
        $this->users[$user['username']] = $user;
        $this->saveToFile();
        return $user;
    }
    
    public function update(array $data): void
    {
        if (!isset($data['username'])) {
            throw new \InvalidArgumentException("Username is required for update");
        }

        if (!isset($this->users[$data['username']])) {
            throw new \InvalidArgumentException("User with username {$data['username']} does not exist.");
        }
        
        $this->users[$data['username']] = array_merge($this->users[$data['username']], $data);
        $this->saveToFile();
    }

    public function delete(int $id): void
    {
        $userToDelete = null;
        foreach ($this->users as $username => $user) {
            if ($user['id'] === $id) {
                $userToDelete = $username;
                break;
            }
        }

        if ($userToDelete === null) {
            throw new \InvalidArgumentException("User with ID {$id} does not exist.");
        }
        
        unset($this->users[$userToDelete]);
        $this->saveToFile();
    }
}
