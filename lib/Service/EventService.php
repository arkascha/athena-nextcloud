<?php
declare(strict_types=1);

namespace OCA\Athena\Service;

use OCA\Athena\Db\Event;
use OCA\Athena\Db\EventMapper;

class EventService {
    public function __construct(
        private readonly EventMapper $eventMapper,
    ) {}

    public function record(int $clientId, string $type, array $payload = []): Event {
        $event = new Event();
        $event->setClientId($clientId);
        $event->setEventType($type);
        $event->setPayloadArray($payload);
        $event->setOccurredAt(new \DateTime());
        return $this->eventMapper->insert($event);
    }

    /** @return Event[] */
    public function listForClient(int $clientId, int $limit = 50, ?string $before = null): array {
        $beforeDt = $before !== null ? new \DateTime($before) : null;
        return $this->eventMapper->findByClient($clientId, $limit, $beforeDt);
    }

    /**
     * Returns 48 slot statuses for the 24 h heartbeat timeline.
     * Each slot covers 30 minutes. Status: 'active'|'missed'|'inactive'|'future'.
     *
     * 'missed' = within the client's active window but no heartbeat received.
     * Determining the active window is out of scope here — we flag any slot
     * with no heartbeat within the window as 'missed' if there were heartbeats
     * in adjacent slots (i.e. the client was online that day).
     */
    public function heartbeatTimeline(int $clientId): array {
        $now  = new \DateTime();
        $day  = (clone $now)->setTime(0, 0, 0);
        $tomorrow = (clone $day)->modify('+1 day');

        $heartbeats = $this->eventMapper->findHeartbeatsInRange($clientId, $day, $now);

        // Build a set of which 30-min slots have at least one heartbeat
        $slotsWithHb = [];
        foreach ($heartbeats as $hb) {
            $ts = $hb->getOccurredAt();
            if ($ts === null) continue;
            $minutesSinceMidnight = (int)$ts->format('H') * 60 + (int)$ts->format('i');
            $slotIndex = (int)floor($minutesSinceMidnight / 30);
            $slotsWithHb[$slotIndex] = true;
        }

        $currentSlot  = (int)floor(((int)$now->format('H') * 60 + (int)$now->format('i')) / 30);
        $clientWasActive = !empty($slotsWithHb);

        $slots = [];
        for ($i = 0; $i < 48; $i++) {
            if ($i > $currentSlot) {
                $slots[] = 'future';
            } elseif (isset($slotsWithHb[$i])) {
                $slots[] = 'active';
            } elseif ($clientWasActive) {
                // Client was online today but missed this slot
                $slots[] = 'missed';
            } else {
                $slots[] = 'inactive';
            }
        }

        return $slots;
    }

    public function todayStats(int $clientId): array {
        $midnight = (new \DateTime())->setTime(0, 0, 0);
        return [
            'heartbeats'  => $this->eventMapper->countByClientAndType($clientId, Event::TYPE_HEARTBEAT,         $midnight),
            'acknowledged'=> $this->eventMapper->countByClientAndType($clientId, Event::TYPE_STEP_ACKNOWLEDGED, $midnight),
            'missed'      => $this->eventMapper->countByClientAndType($clientId, Event::TYPE_STEP_MISSED,       $midnight),
            'alarms'      => $this->eventMapper->countByClientAndType($clientId, Event::TYPE_ALARM_ESCALATED,   $midnight),
        ];
    }

    public function serializeEvent(Event $event): array {
        return [
            'id'          => $event->getId(),
            'event_type'  => $event->getEventType(),
            'payload'     => $event->getPayloadArray(),
            'occurred_at' => $event->getOccurredAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
