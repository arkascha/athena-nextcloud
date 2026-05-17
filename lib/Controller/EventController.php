<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Attribute\ClientTokenRequired;
use OCA\Athena\Db\Event;
use OCA\Athena\Service\ClientSession;
use OCA\Athena\Service\EventService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

/**
 * Accepts client-reported events: button presses, alarm escalations, etc.
 */
class EventController extends Controller {
    private const ALLOWED_TYPES = [
        Event::TYPE_BUTTON_PRESS,
        Event::TYPE_ALARM_ESCALATED,
    ];

    public function __construct(
        IRequest                       $request,
        private readonly ClientSession $clientSession,
        private readonly EventService  $eventService,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[ClientTokenRequired]
    public function record(): DataResponse {
        $client = $this->clientSession->requireClient();
        $body   = $this->request->getParams();

        // Accept either a single event object or an array of events
        $events = isset($body['event_type']) ? [$body] : array_values($body);

        $recorded = [];
        foreach ($events as $e) {
            $type = $e['event_type'] ?? '';
            if (!in_array($type, self::ALLOWED_TYPES, true)) {
                continue; // silently skip unknown/disallowed types
            }
            $payload     = $e['payload'] ?? [];
            $occurredAt  = isset($e['occurred_at']) ? new \DateTime($e['occurred_at']) : null;

            $event = $this->eventService->record($client->getId(), $type, $payload);
            $recorded[] = $this->eventService->serializeEvent($event);
        }

        return new DataResponse(['recorded' => count($recorded)]);
    }
}
