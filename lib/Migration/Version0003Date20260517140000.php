<?php
declare(strict_types=1);

namespace OCA\Athena\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0003Date20260517140000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('athena_client_shares')) {
            $t = $schema->createTable('athena_client_shares');
            $t->addColumn('id',                  Types::INTEGER,  ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $t->addColumn('client_id',            Types::INTEGER,  ['notnull' => true, 'unsigned' => true]);
            $t->addColumn('owner_user_id',         Types::STRING,   ['notnull' => true, 'length' => 64]);
            $t->addColumn('shared_with_user_id',   Types::STRING,   ['notnull' => true, 'length' => 64]);
            $t->addColumn('can_edit',              Types::BOOLEAN,  ['notnull' => true, 'default' => false]);
            $t->addColumn('created_at',            Types::DATETIME, ['notnull' => true]);
            $t->setPrimaryKey(['id']);
            $t->addUniqueIndex(['client_id', 'shared_with_user_id'], 'athena_share_unique');
            $t->addIndex(['shared_with_user_id'], 'athena_share_with');
        }

        return $schema;
    }
}
