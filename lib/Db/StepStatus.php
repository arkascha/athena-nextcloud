<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\Entity;
use OCP\DB\Types;

/**
 * @method int    getClientId()
 * @method void   setClientId(int $clientId)
 * @method int    getStepId()
 * @method void   setStepId(int $stepId)
 * @method string getDate()
 * @method void   setDate(string $date)
 * @method string getStatus()
 * @method void   setStatus(string $status)
 * @method \DateTime|null getAcknowledgedAt()
 * @method void           setAcknowledgedAt(\DateTime|null $acknowledgedAt)
 * @method \DateTime|null getMissedAt()
 * @method void           setMissedAt(\DateTime|null $missedAt)
 */
class StepStatus extends Entity {
    public const STATUS_PENDING      = 'pending';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_MISSED       = 'missed';

    protected int $clientId = 0;
    protected int $stepId = 0;
    protected string $date = '';
    protected string $status = self::STATUS_PENDING;
    protected ?\DateTime $acknowledgedAt = null;
    protected ?\DateTime $missedAt = null;

    public function __construct() {
        $this->addType('acknowledgedAt', Types::DATETIME);
        $this->addType('missedAt', Types::DATETIME);
    }
}
