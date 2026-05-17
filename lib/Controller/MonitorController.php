<?php
declare(strict_types=1);

namespace OCA\Athena\Controller;

use OCA\Athena\AppInfo\Application;
use OCA\Athena\Db\StepStatusMapper;
use OCA\Athena\Db\SequenceStepMapper;
use OCA\Athena\Service\ClientService;
use OCA\Athena\Service\EventService;
use OCA\Athena\Service\SequenceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class MonitorController extends Controller {
    public function __construct(
        IRequest                         $request,
        private readonly string          $userId,
        private readonly ClientService    $clientService,
        private readonly SequenceService  $sequenceService,
        private readonly EventService     $eventService,
        private readonly StepStatusMapper $statusMapper,
        private readonly SequenceStepMapper $stepMapper,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function client(int $id): DataResponse {
        $access   = $this->clientService->getAccessInfo($id, $this->userId);
        $client   = $access['client'];
        // Look up the sequence using the client's owner, not the current (possibly shared) user
        $sequence = $this->sequenceService->findForUser($client->getSequenceId(), $client->getUserId());

        $date     = (new \DateTime())->format('Y-m-d');
        $statuses = $this->statusMapper->findByClientAndDate($client->getId(), $date);
        $steps    = $this->stepMapper->findBySequence($sequence->getId());

        $stepById = [];
        foreach ($steps as $step) {
            $stepById[$step->getId()] = $step;
        }

        $stepList = [];
        foreach ($statuses as $status) {
            $step = $stepById[$status->getStepId()] ?? null;
            $stepList[] = [
                'id'              => $status->getId(),
                'step_key'        => $step?->getStepKey(),
                'title'           => $step?->getTitle(),
                'scheduled_time'  => $step?->getScheduledTime(),
                'status'          => $status->getStatus(),
                'acknowledged_at' => $status->getAcknowledgedAt()?->format(\DateTimeInterface::ATOM),
                'missed_at'       => $status->getMissedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return new DataResponse([
            'client' => array_merge(
                $this->clientService->serializeClient($client, $access['isOwner'], $access['canEdit']),
                [
                    'heartbeat_status' => $this->heartbeatStatus($client->getLastHeartbeat()),
                    'sequence_name'    => $sequence->getName(),
                ]
            ),
            'today' => [
                'date'        => $date,
                'steps'       => $stepList,
                'stats'       => $this->eventService->todayStats($client->getId()),
                'hb_timeline' => $this->eventService->heartbeatTimeline($client->getId()),
            ],
        ]);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function events(int $id, int $limit = 50, string $before = ''): DataResponse {
        $client = $this->clientService->findForUser($id, $this->userId);
        $events = $this->eventService->listForClient(
            $client->getId(), $limit, $before !== '' ? $before : null
        );
        return new DataResponse(array_map(
            fn($e) => $this->eventService->serializeEvent($e),
            $events
        ));
    }

    private function heartbeatStatus(?\DateTime $lastHeartbeat): string {
        if ($lastHeartbeat === null) {
            return 'never';
        }
        $ageSeconds = (new \DateTime())->getTimestamp() - $lastHeartbeat->getTimestamp();
        if ($ageSeconds < 300) {
            return 'active';
        }
        if ($ageSeconds < 1800) {
            return 'idle';
        }
        return 'offline';
    }
}
