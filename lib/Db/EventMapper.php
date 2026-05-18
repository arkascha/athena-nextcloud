<?php
declare(strict_types=1);

namespace OCA\Athena\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Event>
 */
class EventMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'athena_events', Event::class);
    }

    public function deleteByClient(int $clientId): void {
        $qb = $this->db->getQueryBuilder();
        $qb->delete($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)));
        $qb->executeStatement();
    }

    /**
     * Paginated event list for one client, newest first.
     * @return Event[]
     */
    public function findByClient(int $clientId, int $limit = 50, ?\DateTime $before = null): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)))
           ->orderBy('occurred_at', 'DESC')
           ->setMaxResults($limit);

        if ($before !== null) {
            $qb->andWhere($qb->expr()->lt(
                'occurred_at',
                $qb->createNamedParameter($before, IQueryBuilder::PARAM_DATE)
            ));
        }

        return $this->findEntities($qb);
    }

    /**
     * Heartbeat events within a time window — used for the 24 h timeline.
     * @return Event[]
     */
    public function findHeartbeatsInRange(int $clientId, \DateTime $from, \DateTime $to): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('occurred_at')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)))
           ->andWhere($qb->expr()->eq('event_type', $qb->createNamedParameter(Event::TYPE_HEARTBEAT)))
           ->andWhere($qb->expr()->gte('occurred_at', $qb->createNamedParameter($from, IQueryBuilder::PARAM_DATE)))
           ->andWhere($qb->expr()->lte('occurred_at', $qb->createNamedParameter($to,   IQueryBuilder::PARAM_DATE)))
           ->orderBy('occurred_at');
        return $this->findEntities($qb);
    }

    public function countByClientAndType(int $clientId, string $type, \DateTime $since): int {
        $qb = $this->db->getQueryBuilder();
        $qb->select($qb->func()->count('id'))
           ->from($this->getTableName())
           ->where($qb->expr()->eq('client_id', $qb->createNamedParameter($clientId, IQueryBuilder::PARAM_INT)))
           ->andWhere($qb->expr()->eq('event_type', $qb->createNamedParameter($type)))
           ->andWhere($qb->expr()->gte('occurred_at', $qb->createNamedParameter($since, IQueryBuilder::PARAM_DATE)));
        $cursor = $qb->executeQuery();
        $count  = (int)$cursor->fetchOne();
        $cursor->closeCursor();
        return $count;
    }
}
