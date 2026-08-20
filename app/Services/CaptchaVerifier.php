<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CaptchaVerifier
{
    public function verify(string $token, ?string $ip): bool
    {
        $secret = config('services.turnstile.secret');

        if (blank($secret)) {
            return false;
        }

        $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret, 'response' => $token, 'remoteip' => $ip,
        ]);

        return $response->successful() && $response->json('success') === true;
    }
}
