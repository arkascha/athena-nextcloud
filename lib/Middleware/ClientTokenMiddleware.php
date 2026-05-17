<?php
declare(strict_types=1);

namespace OCA\Athena\Middleware;

use OCA\Athena\Attribute\ClientTokenRequired;
use OCA\Athena\Db\ClientMapper;
use OCA\Athena\Exception\ForbiddenException;
use OCA\Athena\Exception\NotFoundException;
use OCA\Athena\Exception\UnauthorizedException;
use OCA\Athena\Service\ClientSession;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Middleware;
use OCP\IRequest;

class ClientTokenMiddleware extends Middleware {
    public function __construct(
        private readonly IRequest      $request,
        private readonly ClientMapper  $clientMapper,
        private readonly ClientSession $clientSession,
    ) {}

    public function beforeController($controller, string $methodName): void {
        $reflection = new \ReflectionMethod($controller, $methodName);
        if (empty($reflection->getAttributes(ClientTokenRequired::class))) {
            return;
        }

        $header = $this->request->getHeader('Authorization');
        if (!str_starts_with($header, 'Bearer ')) {
            throw new UnauthorizedException('Missing or malformed Authorization header');
        }

        $token = substr($header, 7);
        $tokenHash = hash('sha256', $token);

        try {
            $client = $this->clientMapper->findByTokenHash($tokenHash);
        } catch (DoesNotExistException) {
            throw new UnauthorizedException('Invalid client token');
        }

        $this->clientSession->setClient($client);
    }

    public function afterException($controller, $methodName, \Exception $exception): \OCP\AppFramework\Http\Response {
        if ($exception instanceof UnauthorizedException) {
            return new JSONResponse(['error' => $exception->getMessage()], Http::STATUS_UNAUTHORIZED);
        }
        if ($exception instanceof ForbiddenException) {
            return new JSONResponse(['error' => $exception->getMessage()], Http::STATUS_FORBIDDEN);
        }
        if ($exception instanceof NotFoundException) {
            return new JSONResponse(['error' => $exception->getMessage()], Http::STATUS_NOT_FOUND);
        }
        if ($exception instanceof \InvalidArgumentException) {
            return new JSONResponse(['error' => $exception->getMessage()], Http::STATUS_BAD_REQUEST);
        }
        throw $exception;
    }
}
