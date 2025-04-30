<?php

namespace App\Services;

use OpenAI;

class OpenAiService
{
    private OpenAiClientInterface $client;

    public function __construct(OpenAiClientInterface $client = null)
    {
        $this->client = $client ?? new OpenAiClientWrapper();
    }

    public function ask(string $message): array
    {
        return $this->client->ask($message);
    }
}
