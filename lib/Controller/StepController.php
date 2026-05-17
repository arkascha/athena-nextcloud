<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Attribute\ClientTokenRequired;
use OCA\Athena\Service\ClientSession;
use OCA\Athena\Service\StepService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class StepController extends Controller {
    public function __construct(
        IRequest                      $request,
        private readonly ClientSession $clientSession,
        private readonly StepService   $stepService,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[ClientTokenRequired]
    public function acknowledge(int $id): DataResponse {
        $status = $this->stepService->acknowledge($id, $this->clientSession->requireClient());
        return new DataResponse($this->stepService->serializeStatus($status));
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[ClientTokenRequired]
    public function missed(int $id): DataResponse {
        $status = $this->stepService->missed($id, $this->clientSession->requireClient());
        return new DataResponse($this->stepService->serializeStatus($status));
    }
}
