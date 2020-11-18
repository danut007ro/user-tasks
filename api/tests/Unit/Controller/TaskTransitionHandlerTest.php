<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\TaskTransitionHandler;
use App\Entity\Task;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\WorkflowInterface;

final class TaskTransitionHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testInvalidTransitionThrowsException(): void
    {
        self::expectException(BadRequestHttpException::class);
        $workflow = $this->prophesize(WorkflowInterface::class);

        $handler = new TaskTransitionHandler($workflow->reveal());
        $task = new Task();

        $workflow->can($task, 'foo')
            ->willReturn(false);

        $handler->__invoke($task, 'foo');
    }

    public function testAppliesValidTransition(): void
    {
        $workflow = $this->prophesize(WorkflowInterface::class);

        $handler = new TaskTransitionHandler($workflow->reveal());
        $task = new Task();

        $workflow->can($task, 'foo')
            ->willReturn(true);
        $workflow->apply($task, 'foo')
            ->shouldBeCalled();

        $handler->__invoke($task, 'foo');
    }
}
