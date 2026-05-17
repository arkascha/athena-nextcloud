<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Db\ClientShare;
use OCA\Athena\Service\ClientService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserManager;

class ManageShareController extends Controller {
    public function __construct(
        IRequest                       $request,
        private readonly string        $userId,
        private readonly ClientService $clientService,
        private readonly IUserManager  $userManager,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(int $id): DataResponse {
        $shares = $this->clientService->listShares($id, $this->userId);
        return new DataResponse(array_map(fn($s) => $this->serializeShare($s), $shares));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function create(int $id, string $shareWith, bool $canEdit = false): DataResponse {
        $share = $this->clientService->createShare($id, $this->userId, $shareWith, $canEdit);
        return new DataResponse($this->serializeShare($share), Http::STATUS_CREATED);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function update(int $id, int $shareId, bool $canEdit): DataResponse {
        $share = $this->clientService->updateShare($shareId, $id, $this->userId, $canEdit);
        return new DataResponse($this->serializeShare($share));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function destroy(int $id, int $shareId): DataResponse {
        $this->clientService->deleteShare($shareId, $id, $this->userId);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }

    /** Search NC users for the share-with autocomplete. Requires at least 2 chars. */
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function searchUsers(string $q = ''): DataResponse {
        if (strlen(trim($q)) < 2) {
            return new DataResponse([]);
        }
        $users  = $this->userManager->search($q, 10);
        $result = [];
        foreach ($users as $user) {
            if ($user->getUID() === $this->userId) {
                continue;
            }
            $result[] = [
                'uid'         => $user->getUID(),
                'displayName' => $user->getDisplayName(),
            ];
        }
        return new DataResponse($result);
    }

    private function serializeShare(ClientShare $share): array {
        $user = $this->userManager->get($share->getSharedWithUserId());
        return [
            'id'                   => $share->getId(),
            'client_id'            => $share->getClientId(),
            'shared_with_user_id'  => $share->getSharedWithUserId(),
            'shared_with_display'  => $user?->getDisplayName() ?? $share->getSharedWithUserId(),
            'can_edit'             => $share->getCanEdit(),
            'created_at'           => $share->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
