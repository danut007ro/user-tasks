<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Task;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class TaskTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use JWTTokenTrait;

    private static string $token = '';

    public static function setUpBeforeClass(): void
    {
        self::$token = self::login('user1@docler.com', 'docler1');
    }

    public function testCreateTaskWithoutTokenFails(): void
    {
        static::createClient()->request('POST', '/tasks', [
            'json' => [
                'description' => 'foo',
            ],
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    public function testCreateTaskWithMarkingIsIgnored(): void
    {
        $user = self::$fixtures['User_1']; // @phpstan-ignore-line

        $response = static::createClient()->request('POST', '/tasks', [
            'json' => [
                'description' => 'foo',
                'user' => "/users/{$user->getId()}",
                'marking' => 'in_progress',
            ],
            'headers' => [
                'Authorization' => 'Bearer '.self::$token,
            ],
        ]);

        self::assertEquals('new', $response->toArray()['marking']);
    }

    public function testCreateTaskForSelfSucceedsAsNew(): void
    {
        $user = self::$fixtures['User_1']; // @phpstan-ignore-line

        $response = static::createClient()->request('POST', '/tasks', [
            'json' => [
                'description' => 'foo',
                'user' => "/users/{$user->getId()}",
            ],
            'headers' => [
                'Authorization' => 'Bearer '.self::$token,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertEquals('new', $response->toArray()['marking']);
    }

    public function testCreateTaskForOtherFails(): void
    {
        $user = self::$fixtures['User_2']; // @phpstan-ignore-line

        static::createClient()->request('POST', '/tasks', [
            'json' => [
                'description' => 'foo',
                'user' => "/users/{$user->getId()}",
            ],
            'headers' => [
                'Authorization' => 'Bearer '.self::$token,
            ],
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testCreateTaskForOtherAsAdminSucceeds(): void
    {
        $token = self::login('admin@docler.com', 'admin');
        $user = self::$fixtures['User_2']; // @phpstan-ignore-line

        $response = static::createClient()->request('POST', '/tasks', [
            'json' => [
                'description' => 'foo',
                'user' => "/users/{$user->getId()}",
            ],
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertEquals("/users/{$user->getId()}", $response->toArray()['user']);
    }

    public function testUpdateTaskForSelfSucceeds(): void
    {
        $task = self::$fixtures['Task_1_1']; // @phpstan-ignore-line

        $response = static::createClient()->request('PATCH', "/tasks/{$task->getId()}", [
            'json' => [
                'description' => 'foo',
                'user' => "/users/{$task->getUser()->getId()}",
            ],
            'headers' => [
                'Authorization' => 'Bearer '.self::$token,
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertEquals('foo', $response->toArray()['description']);
    }

    public function testUpdateTaskForOtherFails(): void
    {
        $user = self::$fixtures['User_2']; // @phpstan-ignore-line
        $task = self::$fixtures['Task_1_1']; // @phpstan-ignore-line

        static::createClient()->request('PATCH', "/tasks/{$task->getId()}", [
            'json' => [
                'user' => "/users/{$user->getId()}",
            ],
            'headers' => [
                'Authorization' => 'Bearer '.self::$token,
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testUpdateTaskForOtherAsAdminSucceeds(): void
    {
        $token = self::login('admin@docler.com', 'admin');
        $user = self::$fixtures['User_2']; // @phpstan-ignore-line
        $task = self::$fixtures['Task_1_1']; // @phpstan-ignore-line

        $response = static::createClient()->request('PATCH', "/tasks/{$task->getId()}", [
            'json' => [
                'user' => "/users/{$user->getId()}",
            ],
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertEquals("/users/{$user->getId()}", $response->toArray()['user']);
    }

    public function testGetItem(): void
    {
        $task = self::$fixtures['Task_1_1']; // @phpstan-ignore-line
        static::createClient()->request('GET', "/tasks/{$task->getId()}");

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonEquals([
            '@context' => '/contexts/Task',
            '@id' => "/tasks/{$task->getId()}",
            '@type' => 'Task',
            'id' => $task->getId(),
            'description' => 'Task 1 for user1',
            'user' => "/users/{$task->getUser()->getId()}",
            'marking' => 'new',
        ]);

        self::assertMatchesResourceItemJsonSchema(Task::class);
    }

    public function testGetCollectionForUser(): void
    {
        $user = self::$fixtures['User_3']; // @phpstan-ignore-line
        $response = static::createClient()->request('GET', "/users/{$user->getId()}/tasks");

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/contexts/Task',
            '@id' => "/users/{$user->getId()}/tasks",
            '@type' => 'hydra:Collection',
            'hydra:view' => [
                '@id' => "/users/{$user->getId()}/tasks?page=1",
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => "/users/{$user->getId()}/tasks?page=1",
                'hydra:last' => "/users/{$user->getId()}/tasks?page=4",
                'hydra:next' => "/users/{$user->getId()}/tasks?page=2",
            ],
            'hydra:totalItems' => 100,
        ]);

        self::assertCount(30, $response->toArray()['hydra:member']);
        self::assertMatchesResourceCollectionJsonSchema(Task::class);
    }
}
