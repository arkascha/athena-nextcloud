<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\Entity;
use OCP\DB\Types;

/**
 * @method int    getClientId()
 * @method void   setClientId(int $clientId)
 * @method string getEventType()
 * @method void   setEventType(string $eventType)
 * @method string getPayload()
 * @method void   setPayload(string $payload)
 * @method \DateTime getOccurredAt()
 * @method void      setOccurredAt(\DateTime $occurredAt)
 */
class Event extends Entity {
    // Server-recorded
    public const TYPE_HEARTBEAT         = 'heartbeat';
    public const TYPE_STEP_ACKNOWLEDGED = 'step_acknowledged';
    public const TYPE_STEP_MISSED       = 'step_missed';
    public const TYPE_SEQUENCE_LOADED   = 'sequence_loaded';
    public const TYPE_CONFIG_CHANGED    = 'config_changed';

    // Client-reported (via POST /api/v1/events)
    public const TYPE_BUTTON_PRESS      = 'button_press';
    public const TYPE_ALARM_ESCALATED   = 'alarm_escalated';

    protected int $clientId = 0;
    protected string $eventType = '';
    protected string $payload = '{}';
    protected ?\DateTime $occurredAt = null;

    public function __construct() {
        $this->addType('occurredAt', Types::DATETIME);
    }

    public function getPayloadArray(): array {
        return json_decode($this->payload, true) ?? [];
    }

    public function setPayloadArray(array $data): void {
        $this->setPayload(json_encode($data));
    }
}
