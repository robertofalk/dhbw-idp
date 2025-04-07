<?php

namespace App\Controllers;

use App\Services\UserManager;
use App\Services\FileUserStorage;
use App\Helpers\TokenHelper;

class UserController extends BaseController
{
    private UserManager $userManager;

    public function __construct()
    {

        $useFile = env('FILE_STORAGE');
        $storage = $useFile ? new FileUserStorage() : throw new \RuntimeException("Only FILE_STORAGE=true is supported right now");

        $this->userManager = new UserManager($storage);
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
        return $this->response->setJSON($user);
    }

    public function update($id)
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);

        $data = $this->request->getJSON(true);
        $user = $this->userManager->update((int)$id, $data);
        if (!$user) return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        return $this->response->setJSON($user);
    }

    public function delete($id)
    {
        if (!$this->validateToken())
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        
        $success = $this->userManager->delete((int)$id);
        if (!$success) return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        return $this->response->setJSON(['status' => 'deleted']);
    }
}
