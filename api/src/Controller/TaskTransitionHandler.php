<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Task;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Handler for Task transitions.
 */
final class TaskTransitionHandler
{
    private WorkflowInterface $tasksStateMachine;

    public function __construct(WorkflowInterface $tasksStateMachine)
    {
        $this->tasksStateMachine = $tasksStateMachine;
    }

    public function __invoke(Task $data, string $transition): Task
    {
        if (!$this->tasksStateMachine->can($data, $transition)) {
            throw new BadRequestHttpException(sprintf('Cannot run transition "%s".', $transition));
        }

        $this->tasksStateMachine->apply($data, $transition);

        return $data;
    }
}
