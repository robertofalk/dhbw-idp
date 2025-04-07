<?php

namespace App\Helpers;

class TokenHelper
{
    public static function validateToken(string $header): ?array
    {
        if (!str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = substr($header, 7); // Strip "Bearer "
        [$payloadEncoded, $signature] = explode('.', $token, 2);

        $secret = env('AUTH_SECRET', 'default_secret');
        $expectedSig = hash_hmac('sha256', $payloadEncoded, $secret);

        if (!hash_equals($expectedSig, $signature)) {
            return null;
        }

        $payload = json_decode(base64_decode($payloadEncoded), true);
        return $payload ?? null;
    }
}
