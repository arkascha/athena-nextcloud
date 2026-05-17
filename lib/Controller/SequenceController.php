<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Attribute\ClientTokenRequired;
use OCA\Athena\Db\Event;
use OCA\Athena\Exception\ForbiddenException;
use OCA\Athena\Service\ClientSession;
use OCA\Athena\Service\EventService;
use OCA\Athena\Service\SequenceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class SequenceController extends Controller {
    public function __construct(
        IRequest                        $request,
        private readonly ClientSession   $clientSession,
        private readonly SequenceService $sequenceService,
        private readonly EventService    $eventService,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[ClientTokenRequired]
    public function sequence(string $profile, string $date): DataResponse {
        $client = $this->clientSession->requireClient();

        if ($profile !== $client->getSlug()) {
            throw new ForbiddenException('Profile does not match client identity');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return new DataResponse(['error' => 'Invalid date format, expected YYYY-MM-DD'], 400);
        }

        $payload = $this->sequenceService->getForClientAndDate($client, $date);
        $this->eventService->record($client->getId(), Event::TYPE_SEQUENCE_LOADED, [
            'date'       => $date,
            'step_count' => count($payload['sequence']),
        ]);
        return new DataResponse($payload);
    }
}
