<?php

namespace App\Models;

class User
{
    private ?int $id;
    private string $username;
    private string $password;
    private string $role;
    private string $salt;

    public function __construct(
        string $username,
        string $password,
        string $role,
        string $salt,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
        $this->salt = $salt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    public function serialize(): string
    {
        return json_encode([
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'role' => $this->role,
            'salt' => $this->salt,
        ]);
    } 
}
