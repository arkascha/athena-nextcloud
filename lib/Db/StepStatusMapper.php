<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<StepStatus>
 */
class StepStatusMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'athena_step_status', StepStatus::class);
    }

    public function findById(int $id): StepStatus {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }

    /** @return StepStatus[] */
    public function findByClientAndDate(int $clientId, string $date): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)))
           ->andWhere($qb->expr()->eq('date', $qb->createNamedParameter($date)))
           ->orderBy('step_id');
        return $this->findEntities($qb);
    }

    public function deleteByClient(int $clientId): void {
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)));
        $qb->executeStatement();
    }

    /** @return int[] step_ids already tracked for this client+date */
    public function findTrackedStepIds(int $clientId, string $date): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('step_id')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)))
           ->andWhere($qb->expr()->eq('date', $qb->createNamedParameter($date)));
        $cursor = $qb->executeQuery();
        $ids = array_column($cursor->fetchAllAssociative(), 'step_id');
        $cursor->closeCursor();
        return array_map('intval', $ids);
    }
}
