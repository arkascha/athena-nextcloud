<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int    getSequenceId()
 * @method void   setSequenceId(int $sequenceId)
 * @method string getStepKey()
 * @method void   setStepKey(string $stepKey)
 * @method int    getPosition()
 * @method void   setPosition(int $position)
 * @method string getTitle()
 * @method void   setTitle(string $title)
 * @method string getDescription()
 * @method void   setDescription(string $description)
 * @method string getScheduledTime()
 * @method void   setScheduledTime(string $scheduledTime)
 * @method int    getAlarmIntervalMinutes()
 * @method void   setAlarmIntervalMinutes(int $alarmIntervalMinutes)
 * @method int    getMaxEscalationLevel()
 * @method void   setMaxEscalationLevel(int $maxEscalationLevel)
 */
class SequenceStep extends Entity {
    protected int $sequenceId = 0;
    protected string $stepKey = '';
    protected int $position = 0;
    protected string $title = '';
    protected string $description = '';
    protected string $scheduledTime = '';
    protected int $alarmIntervalMinutes = 0;
    protected int $maxEscalationLevel = 0;
}
