<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<ClientShare>
 */
class ClientShareMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'athena_client_shares', ClientShare::class);
    }

    public function findById(int $id): ClientShare {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }

    /** @return ClientShare[] */
    public function findByClient(int $clientId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)));
        return $this->findEntities($qb);
    }

    /** @return ClientShare[] */
    public function findBySharedWith(string $userId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->getTableName())
           ->where($qb->expr()->eq('shared_with_user_id', $qb->createNamedParameter($userId)));
        return $this->findEntities($qb);
    }

    public function findByClientAndUser(int $clientId, string $userId): ClientShare {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->getTableName())
           ->where($qb->expr()->eq('client_id',           $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)))
           ->andWhere($qb->expr()->eq('shared_with_user_id', $qb->createNamedParameter($userId)));
        return $this->findEntity($qb);
    }

    public function deleteByClient(int $clientId): void {
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)));
        $qb->executeStatement();
    }
}
