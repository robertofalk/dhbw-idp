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
                        'name' => ['type' => 'string'],
                        'role' => ['type' => 'string', 'enum' => ['admin', 'user']],
                        'password' => ['type' => 'string'],
                    ],
                    'required' => ['name', 'role', 'password']
                ]
            ],
            [
                'name' => 'updateUser',
                'description' => 'Updates an existing user',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'role' => ['type' => 'string', 'enum' => ['admin', 'user']],
                        'password' => ['type' => 'string']
                    ],
                    'required' => ['id']
                ]
            ],
            [
                'name' => 'deleteUser',
                'description' => 'Deletes a user by ID',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer']
                    ],
                    'required' => ['id']
                ]
            ]
        ];
        

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ],
            'functions' => $functions,
            'function_call' => 'auto'
        ]);

        return $response->toArray();
    }
}
