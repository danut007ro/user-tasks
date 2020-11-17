<?php

declare(strict_types=1);

namespace App\Tests\Functional;

trait JWTTokenTrait
{
    private static function login(string $email, string $password): string
    {
        $response = static::createClient()->request('POST', '/authentication_token', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        return $response->toArray()['token'];
    }
}
