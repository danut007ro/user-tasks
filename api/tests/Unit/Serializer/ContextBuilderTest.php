<?php

declare(strict_types=1);

namespace App\Tests\Unit\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Serializer\ContextBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ContextBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testAddAdminInput(): void
    {
        $decoratedSerializer = $this->prophesize(SerializerContextBuilderInterface::class);
        $authChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $request = new Request();

        $decoratedSerializer->createFromRequest($request, false, null)
            ->willReturn(['groups' => ['foo', 'bar']]);

        $authChecker->isGranted('ROLE_ADMIN')
            ->willReturn(true);

        $contextBuilder = new ContextBuilder($decoratedSerializer->reveal(), $authChecker->reveal());
        $context = $contextBuilder->createFromRequest($request, false);

        self::assertArrayHasKey('groups', $context);
        self::assertEquals(['foo', 'bar', 'admin:input'], $context['groups']);
    }
}
