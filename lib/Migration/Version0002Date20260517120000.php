<?php
declare(strict_types=1);

namespace OCA\Athena\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0002Date20260517120000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('athena_events')) {
            $t = $schema->createTable('athena_events');
            $t->addColumn('id',          Types::INTEGER,  ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $t->addColumn('client_id',   Types::INTEGER,  ['notnull' => true, 'unsigned' => true]);
            $t->addColumn('event_type',  Types::STRING,   ['notnull' => true, 'length' => 32]);
            $t->addColumn('payload',     Types::TEXT,     ['notnull' => false, 'default' => '{}']);
            $t->addColumn('occurred_at', Types::DATETIME, ['notnull' => true]);
            $t->setPrimaryKey(['id']);
            $t->addIndex(['client_id', 'occurred_at'], 'athena_ev_cid_ts');
            $t->addIndex(['client_id', 'event_type'],  'athena_ev_cid_type');
        }

        return $schema;
    }
}
