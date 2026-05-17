<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void   setUserId(string $userId)
 * @method string getName()
 * @method void   setName(string $name)
 * @method string getAbstract()
 * @method void   setAbstract(string $abstract)
 */
class Sequence extends Entity {
    protected string $userId = '';
    protected string $name = '';
    protected string $abstract = '';
}
