<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to add current logged in user_id as payload to generated JWT token.
 */
final class JWTListener implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [Events::JWT_CREATED => 'onJWTCreated'];
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        // Ensure current user is logged in.
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        // Add "user_id" to payload.
        $payload = $event->getData();
        $payload['user_id'] = $user->getId();

        $event->setData($payload);
    }
}
