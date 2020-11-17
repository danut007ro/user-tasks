<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\Entity\User;
use App\EventListener\JWTListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Security;

final class JWTListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testListensForJWTCreatedEvent(): void
    {
        $events = JWTListener::getSubscribedEvents();

        self::assertEquals('onJWTCreated', $events['lexik_jwt_authentication.on_jwt_created']);
    }

    public function testAddUserIdToPayload(): void
    {
        $security = $this->prophesize(Security::class);
        $security->getUser()
            ->willReturn(new User());

        $listener = new JWTListener();
        $user = $this->prophesize(User::class);
        $user->getId()
            ->willReturn(1);

        $event = new JWTCreatedEvent(['foo' => 'bar'], $user->reveal());
        $listener->onJWTCreated($event);

        self::assertEquals(
            [
                'foo' => 'bar',
                'user_id' => 1,
            ],
            $event->getData(),
        );
    }
}
