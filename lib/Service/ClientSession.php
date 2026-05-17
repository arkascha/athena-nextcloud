<?php
declare(strict_types=1);

namespace OCA\Athena\Service;

use OCA\Athena\Db\Client;

/**
 * Request-scoped carrier: the middleware resolves the Bearer token to a Client
 * and stores it here; controllers read it back via constructor injection.
 * Registered as a shared singleton in Application::register().
 */
class ClientSession {
    private ?Client $client = null;

    public function setClient(Client $client): void {
        $this->client = $client;
    }

    public function getClient(): ?Client {
        return $this->client;
    }

    public function requireClient(): Client {
        if ($this->client === null) {
            throw new \LogicException('ClientSession has no client — middleware did not run');
        }
        return $this->client;
    }
}
