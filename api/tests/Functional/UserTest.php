<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class UserTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => '/users',
            '@type' => 'hydra:Collection',
            'hydra:view' => [
                '@id' => '/users?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/users?page=1',
                'hydra:last' => '/users?page=4',
                'hydra:next' => '/users?page=2',
            ],
            'hydra:totalItems' => 101,
        ]);

        $this->assertCount(30, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testGetItem(): void
    {
        $user = self::$fixtures['User_1']; // @phpstan-ignore-line
        static::createClient()->request('GET', "/users/{$user->getId()}");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/contexts/User',
            '@id' => "/users/{$user->getId()}",
            '@type' => 'User',
            'email' => 'user1@docler.com',
        ]);

        $this->assertMatchesResourceItemJsonSchema(User::class);
    }
}
