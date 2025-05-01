<?php

namespace App\Services;

interface UserStorageInterface
{
    public function getAll(): array;
    public function get(array $user): array;
    public function save(array $user): array;
    public function update(array $data): void;
    public function delete(int $id): void;
}
