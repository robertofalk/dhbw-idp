<?php

namespace App\Services;

use RuntimeException;

class UserManager
{
    private UserStorageInterface $storage;

    public function __construct(?UserStorageInterface $storage = null)
    {
        if ($storage === null) {
            $useFile = env('FILE_STORAGE');
            $this->storage = $useFile ? new FileUserStorage() : throw new \RuntimeException("Only FILE_STORAGE=true is supported right now");
        } else {
            $this->storage = $storage;
        }
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
