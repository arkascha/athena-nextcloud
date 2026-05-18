<?php
declare(strict_types=1);

namespace OCA\Athena\Service;

use OCA\Athena\Db\Client;
use OCA\Athena\Db\ClientMapper;
use OCA\Athena\Db\ClientShare;
use OCA\Athena\Db\ClientShareMapper;
use OCA\Athena\Db\EventMapper;
use OCA\Athena\Db\StepStatusMapper;
use OCA\Athena\Exception\ForbiddenException;
use OCA\Athena\Exception\NotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;

class ClientService {
    public function __construct(
        private readonly ClientMapper      $clientMapper,
        private readonly ClientShareMapper $shareMapper,
        private readonly EventMapper       $eventMapper,
        private readonly StepStatusMapper  $stepStatusMapper,
    ) {}

    // ── Token helpers ────────────────────────────────────────────────────────

    /** @return array{token: string, hash: string} */
    public function generateToken(): array {
        $token = bin2hex(random_bytes(32));
        return ['token' => $token, 'hash' => hash('sha256', $token)];
    }

    public function recordHeartbeat(Client $client): void {
        $client->setLastHeartbeat(new \DateTime());
        $this->clientMapper->update($client);
    }

    // ── Access resolution ─────────────────────────────────────────────────────

    /**
     * Resolves the client and access level for $userId.
     * Returns ['client' => Client, 'isOwner' => bool, 'canEdit' => bool].
     *
     * @throws NotFoundException if not found or not accessible
     */
    private function resolveAccess(int $id, string $userId): array {
        // Owner check first
        try {
            $client = $this->clientMapper->findByIdAndUser($id, $userId);
            return ['client' => $client, 'isOwner' => true, 'canEdit' => true];
        } catch (DoesNotExistException) {}

        // Share check
        try {
            $share  = $this->shareMapper->findByClientAndUser($id, $userId);
            $client = $this->clientMapper->findById($share->getClientId());
            return ['client' => $client, 'isOwner' => false, 'canEdit' => $share->getCanEdit()];
        } catch (DoesNotExistException) {
            throw new NotFoundException("Client $id not found");
        }
    }

    /** @return array{client: Client, isOwner: bool, canEdit: bool} */
    public function getAccessInfo(int $id, string $userId): array {
        return $this->resolveAccess($id, $userId);
    }

    /** Returns client if $userId has at least read access (owner or any share). */
    public function findForUser(int $id, string $userId): Client {
        return $this->resolveAccess($id, $userId)['client'];
    }

    /** Returns client only if $userId has edit access (owner or edit share). */
    public function findEditableForUser(int $id, string $userId): Client {
        $access = $this->resolveAccess($id, $userId);
        if (!$access['canEdit']) {
            throw new ForbiddenException('Edit permission required');
        }
        return $access['client'];
    }

    /** Returns client only if $userId is the owner. */
    public function findOwnedForUser(int $id, string $userId): Client {
        $access = $this->resolveAccess($id, $userId);
        if (!$access['isOwner']) {
            throw new ForbiddenException('Only the owner can perform this action');
        }
        return $access['client'];
    }

    // ── List ──────────────────────────────────────────────────────────────────

    /**
     * Returns all clients the user can access (owned + shared), sorted by name.
     * @return array{client: Client, isOwner: bool, canEdit: bool}[]
     */
    public function listAccessibleForUser(string $userId): array {
        $result = [];

        foreach ($this->clientMapper->findAllByUser($userId) as $c) {
            $result[] = ['client' => $c, 'isOwner' => true, 'canEdit' => true];
        }

        foreach ($this->shareMapper->findBySharedWith($userId) as $share) {
            try {
                $c        = $this->clientMapper->findById($share->getClientId());
                $result[] = ['client' => $c, 'isOwner' => false, 'canEdit' => $share->getCanEdit()];
            } catch (DoesNotExistException) {
                // stale share — ignore
            }
        }

        usort($result, fn($a, $b) => strcmp($a['client']->getName(), $b['client']->getName()));
        return $result;
    }

    /** @return Client[] — owned clients only (Kobo-facing paths stay simple) */
    public function listForUser(string $userId): array {
        return $this->clientMapper->findAllByUser($userId);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    private function deriveSlug(string $name): string {
        $slug = mb_strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-') ?: 'client';
    }

    public function create(string $userId, ?string $slug, string $name, ?int $sequenceId): array {
        $slug = ($slug !== null && $slug !== '') ? $slug : $this->deriveSlug($name);
        if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
            throw new \InvalidArgumentException('Slug may only contain lowercase letters, digits, hyphens and underscores');
        }
        // ensure uniqueness by appending a counter if the derived slug is taken
        if ($this->clientMapper->slugExistsForUser($userId, $slug)) {
            $base = $slug; $i = 2;
            while ($this->clientMapper->slugExistsForUser($userId, $slug)) {
                $slug = $base . '-' . $i++;
            }
        }

        $tokenData = $this->generateToken();

        $client = new Client();
        $client->setUserId($userId);
        $client->setSlug($slug);
        $client->setName($name);
        $client->setSequenceId($sequenceId);
        $client->setTokenHash($tokenData['hash']);

        $client = $this->clientMapper->insert($client);
        return ['client' => $client, 'token' => $tokenData['token']];
    }

    public function update(int $id, string $userId, ?string $name, ?int $sequenceId, ?string $slug): Client {
        $client  = $this->findEditableForUser($id, $userId);
        $ownerId = $client->getUserId(); // always validate slug uniqueness against owner's namespace

        if ($slug !== null) {
            if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
                throw new \InvalidArgumentException('Slug may only contain lowercase letters, digits, hyphens and underscores');
            }
            if ($this->clientMapper->slugExistsForUser($ownerId, $slug, $id)) {
                throw new \InvalidArgumentException("Slug '$slug' is already taken");
            }
            $client->setSlug($slug);
        }
        if ($name !== null) {
            $client->setName($name);
        }
        if ($sequenceId !== null) {
            $client->setSequenceId($sequenceId);
        }

        return $this->clientMapper->update($client);
    }

    public function rotateToken(int $id, string $userId): array {
        $client    = $this->findOwnedForUser($id, $userId);
        $tokenData = $this->generateToken();
        $client->setTokenHash($tokenData['hash']);
        $this->clientMapper->update($client);
        return ['token' => $tokenData['token']];
    }

    public function delete(int $id, string $userId): void {
        $client = $this->findOwnedForUser($id, $userId);
        $cid = $client->getId();
        $this->eventMapper->deleteByClient($cid);
        $this->stepStatusMapper->deleteByClient($cid);
        $this->shareMapper->deleteByClient($cid);
        $this->clientMapper->delete($client);
    }

    // ── Serialisation ─────────────────────────────────────────────────────────

    public function serializeClient(Client $client, bool $isOwner = true, bool $canEdit = true): array {
        return [
            'id'             => $client->getId(),
            'slug'           => $client->getSlug(),
            'name'           => $client->getName(),
            'sequence_id'    => $client->getSequenceId(),
            'last_heartbeat' => $client->getLastHeartbeat()?->format(\DateTimeInterface::ATOM),
            'owner_user_id'  => $client->getUserId(),
            'is_owner'       => $isOwner,
            'can_edit'       => $canEdit,
        ];
    }

    // ── Share management ──────────────────────────────────────────────────────

    /** @return ClientShare[] */
    public function listShares(int $clientId, string $userId): array {
        $this->findOwnedForUser($clientId, $userId);
        return $this->shareMapper->findByClient($clientId);
    }

    public function createShare(int $clientId, string $userId, string $shareWith, bool $canEdit): ClientShare {
        $this->findOwnedForUser($clientId, $userId);

        if ($shareWith === $userId) {
            throw new \InvalidArgumentException('Cannot share with yourself');
        }

        $share = new ClientShare();
        $share->setClientId($clientId);
        $share->setOwnerUserId($userId);
        $share->setSharedWithUserId($shareWith);
        $share->setCanEdit($canEdit);
        $share->setCreatedAt(new \DateTime());
        return $this->shareMapper->insert($share);
    }

    public function updateShare(int $shareId, int $clientId, string $userId, bool $canEdit): ClientShare {
        $this->findOwnedForUser($clientId, $userId);
        $share = $this->shareMapper->findById($shareId);
        if ($share->getClientId() !== $clientId) {
            throw new NotFoundException("Share $shareId not found");
        }
        $share->setCanEdit($canEdit);
        return $this->shareMapper->update($share);
    }

    public function deleteShare(int $shareId, int $clientId, string $userId): void {
        $this->findOwnedForUser($clientId, $userId);
        $share = $this->shareMapper->findById($shareId);
        if ($share->getClientId() !== $clientId) {
            throw new NotFoundException("Share $shareId not found");
        }
        $this->shareMapper->delete($share);
    }
}
