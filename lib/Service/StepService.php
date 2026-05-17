<?php
declare(strict_types=1);

namespace OCA\Athena\Service;

use OCA\Athena\Db\Client;
use OCA\Athena\Db\Event;
use OCA\Athena\Db\SequenceStepMapper;
use OCA\Athena\Db\StepStatus;
use OCA\Athena\Db\StepStatusMapper;
use OCA\Athena\Exception\ForbiddenException;
use OCA\Athena\Exception\NotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;

class StepService {
    public function __construct(
        private readonly StepStatusMapper   $statusMapper,
        private readonly SequenceStepMapper $stepMapper,
        private readonly EventService       $eventService,
    ) {}

    public function acknowledge(int $statusId, Client $client): StepStatus {
        $status = $this->loadAndVerify($statusId, $client);
        $status->setStatus(StepStatus::STATUS_ACKNOWLEDGED);
        $status->setAcknowledgedAt(new \DateTime());
        $updated = $this->statusMapper->update($status);
        $this->logStepEvent($client->getId(), Event::TYPE_STEP_ACKNOWLEDGED, $status);
        return $updated;
    }

    public function missed(int $statusId, Client $client): StepStatus {
        $status = $this->loadAndVerify($statusId, $client);
        $status->setStatus(StepStatus::STATUS_MISSED);
        $status->setMissedAt(new \DateTime());
        $updated = $this->statusMapper->update($status);
        $this->logStepEvent($client->getId(), Event::TYPE_STEP_MISSED, $status);
        return $updated;
    }

    private function logStepEvent(int $clientId, string $type, StepStatus $status): void {
        try {
            $step = $this->stepMapper->findById($status->getStepId());
            $this->eventService->record($clientId, $type, [
                'step_status_id' => $status->getId(),
                'step_key'       => $step->getStepKey(),
                'title'          => $step->getTitle(),
            ]);
        } catch (\Throwable) {
            // event logging must not break the main flow
        }
    }

    private function loadAndVerify(int $statusId, Client $client): StepStatus {
        try {
            $status = $this->statusMapper->findById($statusId);
        } catch (DoesNotExistException) {
            throw new NotFoundException("Step status $statusId not found");
        }
        if ($status->getClientId() !== $client->getId()) {
            throw new ForbiddenException('Step does not belong to this client');
        }
        return $status;
    }

    public function serializeStatus(StepStatus $status): array {
        return [
            'id'             => $status->getId(),
            'step_id'        => $status->getStepId(),
            'date'           => $status->getDate(),
            'status'         => $status->getStatus(),
            'acknowledged_at' => $status->getAcknowledgedAt()?->format(\DateTimeInterface::ATOM),
            'missed_at'       => $status->getMissedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
