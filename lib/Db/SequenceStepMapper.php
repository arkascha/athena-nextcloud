<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<SequenceStep>
 */
class SequenceStepMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'athena_seq_steps', SequenceStep::class);
    }

    public function findById(int $id): SequenceStep {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }

    /** @return SequenceStep[] ordered by position */
    public function findBySequence(int $sequenceId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('sequence_id', $qb->createNamedParameter($sequenceId, IQueryBuilder::PARAM_INT)))
           ->orderBy('position');
        return $this->findEntities($qb);
    }

    public function maxPositionForSequence(int $sequenceId): int {
        $qb = $this->db->getQueryBuilder();
        $qb->select($qb->func()->max('position'))
           ->from($this->getTableName())
           ->where($qb->expr()->eq('sequence_id', $qb->createNamedParameter($sequenceId, IQueryBuilder::PARAM_INT)));
        $cursor = $qb->executeQuery();
        $max = $cursor->fetchOne();
        $cursor->closeCursor();
        return $max !== false ? (int)$max : 0;
    }
}
