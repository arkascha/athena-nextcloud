<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int       getId()
 * @method int       getClientId()          setClientId(int $v)
 * @method string    getOwnerUserId()       setOwnerUserId(string $v)
 * @method string    getSharedWithUserId()  setSharedWithUserId(string $v)
 * @method bool      getCanEdit()           setCanEdit(bool $v)
 * @method \DateTime|null getCreatedAt()    setCreatedAt(\DateTime $v)
 */
class ClientShare extends Entity {
    protected int    $clientId         = 0;
    protected string $ownerUserId      = '';
    protected string $sharedWithUserId = '';
    protected bool   $canEdit          = false;
    protected ?\DateTime $createdAt    = null;

    public function __construct() {
        $this->addType('clientId', 'integer');
        $this->addType('canEdit',  'boolean');
    }
}
