<?php

namespace App\Controllers;

use App\Services\UserManager;
use App\Helpers\TokenHelper;
use App\Models\User;

class UserController extends BaseController
{
    private UserManager $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    private function validateToken(): bool
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $user = TokenHelper::validateToken($authHeader);

        if (!$user)
            return false;
        return true;
    }

    public function index()
    {
        return view('users');
    }

    public function getAll()
    {
        if (!$this->validateToken()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $users = $this->userManager->getAll();
        $serializedUsers = array_map(fn($user) => json_decode($user->serialize(), true), $users);

        return $this->response->setJSON($serializedUsers);
    }

    public function create()
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);

        $data = $this->request->getJSON(true);
        if (!$data || !isset($data['username'], $data['password'], $data['role']))
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Username, password and role required']);

        $user = $this->userManager->create($data['username'], $data['password'], $data['role']);
        return $this->response->setStatusCode(201)->setJSON($user);
    }

    public function update(int $id)
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);

        $data = $this->request->getJSON(true);

        try {
            $this->userManager->update(id: $id, username: $data['username'] ?? null, password: $data['password'] ?? null, role: $data['role'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid data']);
        }
        return $this->response->setJSON($data);
    }

    public function delete($id)
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        
        try {
            $this->userManager->delete((int)$id);
            return $this->response->setJSON(['status' => 'deleted']);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }
    }
}
