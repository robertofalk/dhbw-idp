<?php

namespace App\Services;

interface UserStorageInterface
{
    public function getAll(): array;
    public function save(array $user): array;
    public function update(int $id, array $data): ?array;
    public function delete(int $id): bool;
}
