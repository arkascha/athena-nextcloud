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

class ManageStepController extends Controller {
    public function __construct(
        IRequest                         $request,
        private readonly string          $userId,
        private readonly SequenceService $sequenceService,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function update(int $id): DataResponse {
        $body = $this->request->getParams();
        $step = $this->sequenceService->updateStep($id, $this->userId, $body);
        return new DataResponse($this->sequenceService->serializeStep($step));
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function destroy(int $id): DataResponse {
        $this->sequenceService->deleteStep($id, $this->userId);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }
}
