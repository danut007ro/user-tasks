<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\TaskTransitionHandler;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     denormalizationContext={"groups"={"task:input"}},
 *     collectionOperations={
 *      "post"={
 *          "openapi_context"={
 *              "summary"="Creates a Task resource. Needs to be logged in. Only admin can specify another user than itself.",
 *          },
 *          "security_post_denormalize"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getUser() == user)",
 *      },
 *     },
 *     itemOperations={
 *      "get"={
 *          "openapi_context"={
 *              "security"={},
 *          },
 *      },
 *      "patch"={
 *          "openapi_context"={
 *              "summary"="Updates the Task resource. Needs to be logged in. Only admin can specify another user than itself.",
 *          },
 *          "security_post_denormalize"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getUser() == user)",
 *      },
 *      "delete"={
 *          "openapi_context"={
 *              "summary"="Removes the Task resource. Needs to be logged in. Only admin can remove tasks for other users. Only tasks marked as `done` can be removed.",
 *          },
 *          "security"="object.getMarking() == 'done' and (is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getUser() == user))",
 *      },
 *      "transition"={
 *          "denormalization_context"={"groups"={"task:transition"}},
 *          "security"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object.getUser() == user)",
 *          "method"="POST",
 *          "path"="/tasks/{id}/transition/{transition}",
 *          "controller"=TaskTransitionHandler::class,
 *          "openapi_context"={
 *              "summary"="Creates a Task transition. Needs to be logged in. Only admin can transition tasks for other users. Transitions must follow the defined workflow.",
 *              "parameters"={
 *                  {
 *                      "name"="id",
 *                      "required"=true,
 *                      "in"="path",
 *                      "schema"={
 *                          "type"="string",
 *                      },
 *                  },
 *                  {
 *                      "name"="transition",
 *                      "required"=true,
 *                      "in"="path",
 *                      "schema"={
 *                          "type"="string",
 *                          "enum"={"working", "completed", "not_done"},
 *                      },
 *                  },
 *              },
 *          },
 *      },
 *     },
 * )
 * @ORM\Entity()
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Groups({"task:input"})
     * @ORM\Column(type="text")
     */
    private string $description = '';

    /**
     * @ORM\Column(type="string")
     */
    private string $marking = 'new';

    /**
     * @Groups({"task:input"})
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMarking(): string
    {
        return $this->marking;
    }

    public function setMarking(string $marking): self
    {
        $this->marking = $marking;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
