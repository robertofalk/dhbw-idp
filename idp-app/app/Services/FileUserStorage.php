<?php

namespace App\Services;

class FileUserStorage implements UserStorageInterface
{
    private string $filePath;
    private array $users = [];
    private int $nextId = 1;

    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?? WRITEPATH . 'users.json';
        $this->load();

        if (empty($this->users)) {
            $salt = bin2hex(random_bytes(16));
            $password = 'admin';
            $passwordHash = hash_hmac('sha256', $password, $salt);
        
            $this->users[] = [
                'id' => $this->nextId++,
                'name' => 'admin',
                'role' => 'admin',
                'salt' => $salt,
                'password' => $passwordHash
            ];
        
            $this->saveToFile();
        }
    }

    private function load(): void
    {
        if (file_exists($this->filePath)) {
            $this->users = json_decode(file_get_contents($this->filePath), true) ?? [];
            $ids = array_column($this->users, 'id');
            $this->nextId = $ids ? max($ids) + 1 : 1;
        }
    }

    private function saveToFile(): void
    {
        file_put_contents($this->filePath, json_encode($this->users, JSON_PRETTY_PRINT));
    }

    public function getAll(): array
    {
        return $this->users;
    }

    public function save(array $user): array
    {
        // Create salt and hash the password
        $salt = bin2hex(random_bytes(16));
        $passwordHash = hash_hmac('sha256', $user['password'], $salt);
    
        $user['id'] = $this->nextId++;
        $user['salt'] = $salt;
        $user['password'] = $passwordHash;
    
        $this->users[] = $user;
        $this->saveToFile();
        return $user;
    }
    

    public function update(int $id, array $data): ?array
    {
        foreach ($this->users as &$user) {
            if ($user['id'] === $id) {
                $user = array_merge($user, $data);
                $this->saveToFile();
                return $user;
            }
        }
        return null;
    }

    public function delete(int $id): bool
    {
        foreach ($this->users as $i => $user) {
            if ($user['id'] === $id) {
                unset($this->users[$i]);
                $this->users = array_values($this->users); // reindex
                $this->saveToFile();
                return true;
            }
        }
        return false;
    }
}
