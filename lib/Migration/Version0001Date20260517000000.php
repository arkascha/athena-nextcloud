<?php
declare(strict_types=1);

namespace OCA\Athena\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0001Date20260517000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // ── athena_sequences ────────────────────────────────────────────────
        if (!$schema->hasTable('athena_sequences')) {
            $t = $schema->createTable('athena_sequences');
            $t->addColumn('id',       Types::INTEGER, ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $t->addColumn('user_id',  Types::STRING,  ['notnull' => true, 'length' => 64]);
            $t->addColumn('name',     Types::STRING,  ['notnull' => true, 'length' => 128]);
            $t->addColumn('abstract', Types::TEXT,    ['notnull' => false, 'default' => '']);
            $t->setPrimaryKey(['id']);
            $t->addIndex(['user_id'], 'athena_seq_uid');
        }

        // ── athena_seq_steps ────────────────────────────────────────────────
        if (!$schema->hasTable('athena_seq_steps')) {
            $t = $schema->createTable('athena_seq_steps');
            $t->addColumn('id',                      Types::INTEGER, ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $t->addColumn('sequence_id',             Types::INTEGER, ['notnull' => true, 'unsigned' => true]);
            $t->addColumn('step_key',                Types::STRING,  ['notnull' => true, 'length' => 64]);
            $t->addColumn('position',                Types::INTEGER, ['notnull' => true, 'default' => 0]);
            $t->addColumn('title',                   Types::STRING,  ['notnull' => true, 'length' => 255]);
            $t->addColumn('description',             Types::TEXT,    ['notnull' => false, 'default' => '']);
            $t->addColumn('scheduled_time',          Types::STRING,  ['notnull' => true, 'length' => 5, 'default' => '00:00']);
            $t->addColumn('alarm_interval_minutes',  Types::INTEGER, ['notnull' => true, 'default' => 5]);
            $t->addColumn('max_escalation_level',    Types::INTEGER, ['notnull' => true, 'default' => 1]);
            $t->setPrimaryKey(['id']);
            $t->addIndex(['sequence_id', 'position'], 'athena_step_seq_pos');
        }

        // ── athena_clients ──────────────────────────────────────────────────
        if (!$schema->hasTable('athena_clients')) {
            $t = $schema->createTable('athena_clients');
            $t->addColumn('id',             Types::INTEGER,  ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $t->addColumn('user_id',        Types::STRING,   ['notnull' => true, 'length' => 64]);
            $t->addColumn('slug',           Types::STRING,   ['notnull' => true, 'length' => 64]);
            $t->addColumn('name',           Types::STRING,   ['notnull' => true, 'length' => 128]);
            $t->addColumn('token_hash',     Types::STRING,   ['notnull' => true, 'length' => 128]);
            $t->addColumn('sequence_id',    Types::INTEGER,  ['notnull' => true, 'unsigned' => true]);
            $t->addColumn('last_heartbeat', Types::DATETIME, ['notnull' => false]);
            $t->setPrimaryKey(['id']);
            $t->addUniqueIndex(['token_hash'],         'athena_client_token');
            $t->addUniqueIndex(['user_id', 'slug'],    'athena_client_user_slug');
            $t->addIndex(['user_id'],                  'athena_client_uid');
        }

        // ── athena_step_status ──────────────────────────────────────────────
        if (!$schema->hasTable('athena_step_status')) {
            $t = $schema->createTable('athena_step_status');
            $t->addColumn('id',              Types::INTEGER,  ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $t->addColumn('client_id',       Types::INTEGER,  ['notnull' => true, 'unsigned' => true]);
            $t->addColumn('step_id',         Types::INTEGER,  ['notnull' => true, 'unsigned' => true]);
            $t->addColumn('date',            Types::STRING,   ['notnull' => true, 'length' => 10]);
            $t->addColumn('status',          Types::STRING,   ['notnull' => true, 'length' => 16, 'default' => 'pending']);
            $t->addColumn('acknowledged_at', Types::DATETIME, ['notnull' => false]);
            $t->addColumn('missed_at',       Types::DATETIME, ['notnull' => false]);
            $t->setPrimaryKey(['id']);
            $t->addIndex(['client_id', 'date'], 'athena_status_cid_date');
            $t->addUniqueIndex(['client_id', 'step_id', 'date'], 'athena_status_unique');
        }

        return $schema;
    }
}
