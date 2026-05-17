<?php
declare(strict_types=1);

namespace OCA\Athena\AppInfo;

use OCA\Athena\Middleware\ClientTokenMiddleware;
use OCA\Athena\Service\ClientSession;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IUserSession;

class Application extends App implements IBootstrap {
    public const APP_ID = 'athena';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        // Shared stateful object that carries the token-resolved client across
        // the middleware → controller boundary within a single request.
        $context->registerService(ClientSession::class, fn() => new ClientSession());

        $context->registerMiddleware(ClientTokenMiddleware::class);

        // Inject the current NC user's UID into any service/controller that
        // declares a `string $userId` constructor parameter.
        $context->registerService('userId', function (\Psr\Container\ContainerInterface $c): string {
            $user = $c->get(IUserSession::class)->getUser();
            return $user?->getUID() ?? '';
        });
    }

    public function boot(IBootContext $context): void {
    }
}
