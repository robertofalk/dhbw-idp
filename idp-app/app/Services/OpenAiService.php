<?php

namespace App\Services;

use OpenAI;

class OpenAiService
{
    private $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    public function ask(string $message): array
    {
        $functions = [
            [
                'name' => 'createUser',
                'description' => 'Creates a new user',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'username' => ['type' => 'string'],
                        'role' => ['type' => 'string', 'enum' => ['admin', 'user']],
                        'password' => ['type' => 'string'],
                    ],
                    'required' => ['username', 'role', 'password']
                ]
            ],
            [
                'name' => 'updateUser',
                'description' => 'Updates an existing user',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'username' => ['type' => 'string'],
                        'role' => ['type' => 'string', 'enum' => ['admin', 'user']],
                        'password' => ['type' => 'string']
                    ],
                    'required' => ['id']
                ]
            ],
            [
                'name' => 'deleteUser',
                'description' => 'Deletes a user by ID. Reject if the ID was not provided.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer']
                    ],
                    'required' => ['id']
                ]
            ]
        ];

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4.1-nano',
                'messages' => [
                    ['role' => 'user', 'content' => $message]
                ],
                'functions' => $functions,
                'function_call' => 'auto'
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
