<?php

namespace App\Controllers;

use App\Services\UserManager;

class AuthController extends BaseController
{

    private UserManager $userManager;

    public function index()
    {
        return view('index'); // This will be your login page
    }

    public function __construct() {
        $this->userManager = new UserManager();
    }

    public function login()
    {
        $data = $this->request->getJSON(true);

        if (!$data || !isset($data['username'], $data['password']))
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Username and password required']);
 
        try {
            $users = $this->userManager->get($data);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid credentials']);
        }
        
        // Create a simple payload
        $payload = base64_encode(json_encode([
            'username' => $users['username'],
            'role' => $users['role'],
            'iat' => time()
        ]));

        // Sign the payload
        $secret = env('AUTH_SECRET');
        $signature = hash_hmac('sha256', $payload, $secret);

        return $this->response->setJSON([
            'token' => $payload . '.' . $signature
        ]);
    }
}
