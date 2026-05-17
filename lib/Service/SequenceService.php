<?php
declare(strict_types=1);

namespace OCA\Athena\Service;

use OCA\Athena\Db\Client;
use OCA\Athena\Db\Sequence;
use OCA\Athena\Db\SequenceMapper;
use OCA\Athena\Db\SequenceStep;
use OCA\Athena\Db\SequenceStepMapper;
use OCA\Athena\Db\StepStatus;
use OCA\Athena\Db\StepStatusMapper;
use OCA\Athena\Exception\NotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;

class SequenceService {
    public function __construct(
        private readonly SequenceMapper     $sequenceMapper,
        private readonly SequenceStepMapper $stepMapper,
        private readonly StepStatusMapper   $statusMapper,
    ) {}

    /**
     * Returns the full sequence payload for a client on a given date.
     * StepStatus rows are lazily created (status = pending) on first access.
     */
    public function getForClientAndDate(Client $client, string $date): array {
        try {
            $sequence = $this->sequenceMapper->findById($client->getSequenceId());
        } catch (DoesNotExistException) {
            throw new NotFoundException('Assigned sequence not found');
        }

        $steps = $this->stepMapper->findBySequence($sequence->getId());
        $statuses = $this->ensureStatuses($client->getId(), $date, $steps);

        // Index statuses by step_id for O(1) lookup
        $statusByStepId = [];
        foreach ($statuses as $s) {
            $statusByStepId[$s->getStepId()] = $s;
        }

        $sequenceData = [];
        foreach ($steps as $step) {
            $status = $statusByStepId[$step->getId()];
            $sequenceData[] = $this->mergeStep($step, $status);
        }

        return [
            'abstract' => $sequence->getAbstract(),
            'sequence' => $sequenceData,
        ];
    }

    /**
     * Ensures a StepStatus row exists for every step in this client+date.
     * Missing rows are inserted; returns all rows (existing + newly created).
     *
     * @param  SequenceStep[] $steps
     * @return StepStatus[]
     */
    private function ensureStatuses(int $clientId, string $date, array $steps): array {
        $trackedIds = $this->statusMapper->findTrackedStepIds($clientId, $date);
        $trackedSet = array_flip($trackedIds);

        foreach ($steps as $step) {
            if (!isset($trackedSet[$step->getId()])) {
                $status = new StepStatus();
                $status->setClientId($clientId);
                $status->setStepId($step->getId());
                $status->setDate($date);
                $status->setStatus(StepStatus::STATUS_PENDING);
                $this->statusMapper->insert($status);
            }
        }

        return $this->statusMapper->findByClientAndDate($clientId, $date);
    }

    private function mergeStep(SequenceStep $step, StepStatus $status): array {
        return [
            'id'                     => $status->getId(),   // used for acknowledge/missed calls
            'step_key'               => $step->getStepKey(),
            'title'                  => $step->getTitle(),
            'description'            => $step->getDescription(),
            'scheduled_time'         => $step->getScheduledTime(),
            'alarm_interval_minutes' => $step->getAlarmIntervalMinutes(),
            'max_escalation_level'   => $step->getMaxEscalationLevel(),
            'status'                 => $status->getStatus(),
            'acknowledged_at'        => $status->getAcknowledgedAt()?->format(\DateTimeInterface::ATOM),
            'missed_at'              => $status->getMissedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    // ── Sequence management ──────────────────────────────────────────────────

    /** @return Sequence[] */
    public function listForUser(string $userId): array {
        return $this->sequenceMapper->findAllByUser($userId);
    }

    public function findForUser(int $id, string $userId): Sequence {
        try {
            return $this->sequenceMapper->findByIdAndUser($id, $userId);
        } catch (DoesNotExistException) {
            throw new NotFoundException("Sequence $id not found");
        }
    }

    public function create(string $userId, string $name, string $abstract): Sequence {
        $seq = new Sequence();
        $seq->setUserId($userId);
        $seq->setName($name);
        $seq->setAbstract($abstract);
        return $this->sequenceMapper->insert($seq);
    }

    public function update(int $id, string $userId, ?string $name, ?string $abstract): Sequence {
        $seq = $this->findForUser($id, $userId);
        if ($name !== null) {
            $seq->setName($name);
        }
        if ($abstract !== null) {
            $seq->setAbstract($abstract);
        }
        return $this->sequenceMapper->update($seq);
    }

    public function delete(int $id, string $userId): void {
        $seq = $this->findForUser($id, $userId);
        $this->sequenceMapper->delete($seq);
    }

    public function serializeSequence(Sequence $seq): array {
        return [
            'id'       => $seq->getId(),
            'name'     => $seq->getName(),
            'abstract' => $seq->getAbstract(),
        ];
    }

    // ── Step management ──────────────────────────────────────────────────────

    /** @return SequenceStep[] */
    public function listSteps(int $sequenceId, string $userId): array {
        $this->findForUser($sequenceId, $userId); // ownership check
        return $this->stepMapper->findBySequence($sequenceId);
    }

    public function addStep(
        int $sequenceId,
        string $userId,
        string $stepKey,
        string $title,
        string $description,
        string $scheduledTime,
        int $alarmIntervalMinutes,
        int $maxEscalationLevel,
    ): SequenceStep {
        $this->findForUser($sequenceId, $userId); // ownership check
        $position = $this->stepMapper->maxPositionForSequence($sequenceId) + 1;

        $step = new SequenceStep();
        $step->setSequenceId($sequenceId);
        $step->setStepKey($stepKey);
        $step->setPosition($position);
        $step->setTitle($title);
        $step->setDescription($description);
        $step->setScheduledTime($scheduledTime);
        $step->setAlarmIntervalMinutes($alarmIntervalMinutes);
        $step->setMaxEscalationLevel($maxEscalationLevel);

        return $this->stepMapper->insert($step);
    }

    public function updateStep(int $stepId, string $userId, array $fields): SequenceStep {
        try {
            $step = $this->stepMapper->findById($stepId);
        } catch (DoesNotExistException) {
            throw new NotFoundException("Step $stepId not found");
        }
        $this->findForUser($step->getSequenceId(), $userId); // ownership check

        if (isset($fields['title']))                  $step->setTitle($fields['title']);
        if (isset($fields['description']))            $step->setDescription($fields['description']);
        if (isset($fields['step_key']))               $step->setStepKey($fields['step_key']);
        if (isset($fields['scheduled_time']))         $step->setScheduledTime($fields['scheduled_time']);
        if (isset($fields['alarm_interval_minutes'])) $step->setAlarmIntervalMinutes((int)$fields['alarm_interval_minutes']);
        if (isset($fields['max_escalation_level']))   $step->setMaxEscalationLevel((int)$fields['max_escalation_level']);
        if (isset($fields['position']))               $step->setPosition((int)$fields['position']);

        return $this->stepMapper->update($step);
    }

    public function deleteStep(int $stepId, string $userId): void {
        try {
            $step = $this->stepMapper->findById($stepId);
        } catch (DoesNotExistException) {
            throw new NotFoundException("Step $stepId not found");
        }
        $this->findForUser($step->getSequenceId(), $userId); // ownership check
        $this->stepMapper->delete($step);
    }

    public function serializeStep(SequenceStep $step): array {
        return [
            'id'                     => $step->getId(),
            'sequence_id'            => $step->getSequenceId(),
            'step_key'               => $step->getStepKey(),
            'position'               => $step->getPosition(),
            'title'                  => $step->getTitle(),
            'description'            => $step->getDescription(),
            'scheduled_time'         => $step->getScheduledTime(),
            'alarm_interval_minutes' => $step->getAlarmIntervalMinutes(),
            'max_escalation_level'   => $step->getMaxEscalationLevel(),
        ];
    }
}
