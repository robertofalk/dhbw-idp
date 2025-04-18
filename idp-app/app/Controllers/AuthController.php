<?php

namespace App\Controllers;

use App\Services\UserManager;

class AuthController extends BaseController
{
    public function login()
    {
        $data = $this->request->getJSON(true);

        if (!$data || !isset($data['username'], $data['password'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Username and password required']);
        }

        $manager = new UserManager();
        $users = $manager->getAll();

        $matched = null;
        foreach ($users as $user) {
            if ($user['name'] === $data['username']) {
                // Hash incoming password with the user's stored salt
                $attemptHash = hash_hmac('sha256', $data['password'], $user['salt']);
        
                if ($attemptHash === $user['password']) {
                    $matched = $user;
                    break;
                }
            }
        }
        

        if (!$matched) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid credentials']);
        }

        // Create a simple payload
        $payload = base64_encode(json_encode([
            'username' => $matched['name'],
            'role' => $matched['role'],
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
