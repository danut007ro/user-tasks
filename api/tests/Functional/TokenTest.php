<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class TokenTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testAuthenticate(): void
    {
        static::createClient()->request('POST', '/authentication_token', [
            'json' => [
                'email' => 'user1@docler.com',
                'password' => 'docler1',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertMatchesJsonSchema([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['token'],
        ]);
    }
}
