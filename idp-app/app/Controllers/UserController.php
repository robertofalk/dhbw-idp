<?php

namespace App\Controllers;

use App\Services\UserManager;
use App\Helpers\TokenHelper;

class UserController extends BaseController
{
    private UserManager $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    private function validateToken(): ?bool
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $user = TokenHelper::validateToken($authHeader);

        if (!$user)
            return false;
        return true;
    }

    public function index()
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        return $this->response->setJSON($this->userManager->getAll());
    }

    public function create()
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);

        $data = $this->request->getJSON(true);
        $user = $this->userManager->create($data);
        return $this->response->setStatusCode(201)->setJSON($user);
    }

    public function update($id)
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);

        $data = $this->request->getJSON(true);

        try {
            $this->userManager->update($data);
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
