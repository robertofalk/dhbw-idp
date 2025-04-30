<?php

namespace App\Services;

interface OpenAiClientInterface
{
    public function ask(string $message): array;
} 