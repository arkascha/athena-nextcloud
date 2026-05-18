<?php
declare(strict_types=1);

namespace OCA\Athena\Migration;

use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0004Date20260518000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();
        $table  = $schema->getTable('athena_clients');
        $col    = $table->getColumn('sequence_id');
        $col->setNotnull(false);
        $col->setDefault(null);
        return $schema;
    }
}
