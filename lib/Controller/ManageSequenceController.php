<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Service\SequenceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ManageSequenceController extends Controller {
    public function __construct(
        IRequest                         $request,
        private readonly string          $userId,
        private readonly SequenceService $sequenceService,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): DataResponse {
        $sequences = $this->sequenceService->listForUser($this->userId);
        return new DataResponse(array_map(
            fn($s) => $this->sequenceService->serializeSequence($s),
            $sequences
        ));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function create(string $name, string $abstract = ''): DataResponse {
        if (trim($name) === '') {
            return new DataResponse(['error' => 'Name is required'], Http::STATUS_BAD_REQUEST);
        }
        $seq = $this->sequenceService->create($this->userId, $name, $abstract);
        return new DataResponse($this->sequenceService->serializeSequence($seq), Http::STATUS_CREATED);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function update(int $id, ?string $name = null, ?string $abstract = null): DataResponse {
        $seq = $this->sequenceService->update($id, $this->userId, $name, $abstract);
        return new DataResponse($this->sequenceService->serializeSequence($seq));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function destroy(int $id): DataResponse {
        $this->sequenceService->delete($id, $this->userId);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function steps(int $id): DataResponse {
        $steps = $this->sequenceService->listSteps($id, $this->userId);
        return new DataResponse(array_map(
            fn($s) => $this->sequenceService->serializeStep($s),
            $steps
        ));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function addStep(
        int    $id,
        string $stepKey,
        string $title,
        string $description = '',
        string $scheduledTime = '00:00',
        int    $alarmIntervalMinutes = 5,
        int    $maxEscalationLevel = 1,
    ): DataResponse {
        if (trim($title) === '') {
            return new DataResponse(['error' => 'Title is required'], Http::STATUS_BAD_REQUEST);
        }
        $step = $this->sequenceService->addStep(
            $id, $this->userId, $stepKey, $title, $description,
            $scheduledTime, $alarmIntervalMinutes, $maxEscalationLevel
        );
        return new DataResponse($this->sequenceService->serializeStep($step), Http::STATUS_CREATED);
    }
}
