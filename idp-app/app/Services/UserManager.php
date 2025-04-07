<?php

namespace App\Services;

class UserManager
{
    private UserStorageInterface $storage;

    public function __construct(UserStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function create(array $data): array
    {
        return $this->storage->save($data);
    }

    public function getAll(): array
    {
        return $this->storage->getAll();
    }

    public function update(int $id, array $data): ?array
    {
        return $this->storage->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->storage->delete($id);
    }
}
