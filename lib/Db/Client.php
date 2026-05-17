<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\Entity;
use OCP\DB\Types;

/**
 * @method string getUserId()
 * @method void   setUserId(string $userId)
 * @method string getSlug()
 * @method void   setSlug(string $slug)
 * @method string getName()
 * @method void   setName(string $name)
 * @method string getTokenHash()
 * @method void   setTokenHash(string $tokenHash)
 * @method int    getSequenceId()
 * @method void   setSequenceId(int $sequenceId)
 * @method \DateTime|null getLastHeartbeat()
 * @method void           setLastHeartbeat(\DateTime|null $lastHeartbeat)
 */
class Client extends Entity {
    protected string $userId = '';
    protected string $slug = '';
    protected string $name = '';
    protected string $tokenHash = '';
    protected int $sequenceId = 0;
    protected ?\DateTime $lastHeartbeat = null;

    public function __construct() {
        $this->addType('lastHeartbeat', Types::DATETIME);
    }
}
