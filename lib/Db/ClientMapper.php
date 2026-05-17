<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Client>
 */
class ClientMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'athena_clients', Client::class);
    }

    public function findById(int $id): Client {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        return $this->findEntity($qb);
    }

    public function findByTokenHash(string $tokenHash): Client {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('token_hash', $qb->createNamedParameter($tokenHash)));
        return $this->findEntity($qb);
    }

    public function findByIdAndUser(int $id, string $userId): Client {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
           ->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
        return $this->findEntity($qb);
    }

    public function findByUserAndSlug(string $userId, string $slug): Client {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->andWhere($qb->expr()->eq('slug', $qb->createNamedParameter($slug)));
        return $this->findEntity($qb);
    }

    /** @return Client[] */
    public function findAllByUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->orderBy('name');
        return $this->findEntities($qb);
    }

    public function slugExistsForUser(string $userId, string $slug, ?int $excludeId = null): bool {
        $qb = $this->db->getQueryBuilder();
        $qb->select('id')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
           ->andWhere($qb->expr()->eq('slug', $qb->createNamedParameter($slug)));
        if ($excludeId !== null) {
            $qb->andWhere($qb->expr()->neq('id', $qb->createNamedParameter($excludeId, IQueryBuilder::PARAM_INT)));
        }
        $cursor = $qb->executeQuery();
        $exists = $cursor->fetchOne() !== false;
        $cursor->closeCursor();
        return $exists;
    }
}
