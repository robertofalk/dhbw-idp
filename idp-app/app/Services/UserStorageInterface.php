<?php

namespace App\Services;

use App\Models\User;

interface UserStorageInterface
{
    public function getAll(): array;
    public function get(?int $id = null, ?string $username = null, ?string $password = null): User;
    public function save(User $user): User;
    public function update(int $id, User $user): void;
    public function delete(int $id): void;
}
