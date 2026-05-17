<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Attribute\ClientTokenRequired;
use OCA\Athena\Db\Event;
use OCA\Athena\Service\ClientService;
use OCA\Athena\Service\ClientSession;
use OCA\Athena\Service\EventService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class HeartbeatController extends Controller {
    public function __construct(
        IRequest                       $request,
        private readonly ClientSession $clientSession,
        private readonly ClientService $clientService,
        private readonly EventService  $eventService,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[ClientTokenRequired]
    public function heartbeat(): DataResponse {
        $client = $this->clientSession->requireClient();
        $this->clientService->recordHeartbeat($client);
        $this->eventService->record($client->getId(), Event::TYPE_HEARTBEAT);
        return new DataResponse([
            'status'    => 'ok',
            'timestamp' => (new \DateTime())->format(\DateTimeInterface::ATOM),
        ]);
    }
}
