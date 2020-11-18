# About

The application is using [api-platform](https://api-platform.com/) with JWT authentication and Symfony's [Workflow](https://symfony.com/doc/current/components/workflow.html) component to manage transitions for Tasks.

For coding standards and static code check I am using [friendsofphp/php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) and [phpstan](https://github.com/phpstan/phpstan) with `max` setting.

Defined users are visible in fixture [User.yaml](/api/fixtures/User.yaml)

# Installation

- clone
- run `docker-compose up -d`
- navigate to https://localhost:8443 for openapi documentation

# Composer commands

Some commands are available as composer scripts:

- `docker-compose exec php composer tests` for running tests (this also install phpunit needed for running phpstan)
- `docker-compose exec php composer cs-fix` for running php-cs-fixer
- `docker-compose exec php composer phpstan` for running phpstan with `max` setting

# Workflow

The tasks can be in the following states, specified in `marking` field:

- `new`
- `in_progress`
- `done`

The following transitions can be applied to them, as seen in [workflow.yaml](/api/config/packages/workflow.yaml):

- `working`
- `completed`
- `not_done`

Only tasks that are `done` can be deleted.

See the following image for a visual explanation (image was generated with `bin/console workflow:dump tasks | dot -Tpng -o graph.png` command):

![Workflow](/api/graph.png)

# Admin users

Admin user can create tasks on behalf of other users, move them to other users and apply transactions to them.
