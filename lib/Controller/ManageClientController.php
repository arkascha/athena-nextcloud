<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Db\Event;
use OCA\Athena\Service\ClientService;
use OCA\Athena\Service\EventService;
use OCA\Athena\Service\SequenceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ManageClientController extends Controller {
    public function __construct(
        IRequest                         $request,
        private readonly string          $userId,
        private readonly ClientService   $clientService,
        private readonly SequenceService $sequenceService,
        private readonly EventService    $eventService,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): DataResponse {
        $items = $this->clientService->listAccessibleForUser($this->userId);
        return new DataResponse(array_map(
            fn($item) => $this->clientService->serializeClient($item['client'], $item['isOwner'], $item['canEdit']),
            $items
        ));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function create(string $slug, string $name, ?int $sequenceId = null): DataResponse {
        if (trim($name) === '') {
            return new DataResponse(['error' => 'Name is required'], Http::STATUS_BAD_REQUEST);
        }
        if ($sequenceId !== null) {
            $this->sequenceService->findForUser($sequenceId, $this->userId);
        }

        $result = $this->clientService->create($this->userId, $slug, $name, $sequenceId);
        return new DataResponse([
            'client' => $this->clientService->serializeClient($result['client']),
            'token'  => $result['token'],
        ], Http::STATUS_CREATED);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function update(int $id, ?string $name = null, ?int $sequenceId = null, ?string $slug = null): DataResponse {
        // Resolve access and get the client (also provides owner user id for sequence validation)
        $old = $this->clientService->findForUser($id, $this->userId);

        if ($sequenceId !== null) {
            // Sequence must belong to the client's owner, not necessarily the current (shared) editor
            $this->sequenceService->findForUser($sequenceId, $old->getUserId());
        }

        $client = $this->clientService->update($id, $this->userId, $name, $sequenceId, $slug);

        if ($sequenceId !== null && $sequenceId !== $old->getSequenceId()) {
            $this->eventService->record($client->getId(), Event::TYPE_CONFIG_CHANGED, [
                'old_sequence_id' => $old->getSequenceId(),
                'new_sequence_id' => $sequenceId,
                'changed_by'      => $this->userId,
            ]);
        }

        $access = $this->clientService->getAccessInfo($id, $this->userId);
        return new DataResponse($this->clientService->serializeClient($client, $access['isOwner'], $access['canEdit']));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function destroy(int $id): DataResponse {
        $this->clientService->delete($id, $this->userId); // owner-only; throws ForbiddenException otherwise
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function rotateToken(int $id): DataResponse {
        $result = $this->clientService->rotateToken($id, $this->userId); // owner-only
        return new DataResponse(['token' => $result['token']]);
    }

    /**
     * Returns sequences available for a given client (i.e. the owner's sequences).
     * Accessible to anyone with at least read access to the client — used by shared
     * editors to populate the sequence dropdown in the edit form.
     */
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function sequences(int $id): DataResponse {
        $client = $this->clientService->findForUser($id, $this->userId);
        $seqs   = $this->sequenceService->listForUser($client->getUserId());
        return new DataResponse(array_map(
            fn($s) => $this->sequenceService->serializeSequence($s),
            $seqs
        ));
    }
}
