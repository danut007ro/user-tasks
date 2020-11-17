<?php

declare(strict_types=1);

namespace App\Tests\Unit\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Serializer\ContextBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ContextBuilderTest extends TestCase
{
    private Prophet $prophet;

    protected function setUp(): void
    {
        $this->prophet = new Prophet();
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }

    public function testAddAdminInput(): void
    {
        $decoratedSerializer = $this->prophet->prophesize(SerializerContextBuilderInterface::class);
        $authChecker = $this->prophet->prophesize(AuthorizationCheckerInterface::class);
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
