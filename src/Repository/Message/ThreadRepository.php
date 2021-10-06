<?php

namespace App\Repository\Message;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Core\User;
use App\Repository\AbstractEntityRepository;

class ThreadRepository extends AbstractEntityRepository {

	/////

	public function existsBySenderAndSubjectAndBody($sender, $subject, $body, $backwardDaysInterval = 1) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'count(t.id)' ))
			->from($this->getEntityName(), 't')
			->innerJoin('t.messages', 'm')
			->where('m.sender = :sender')
			->andWhere('LOWER(t.subject) = LOWER(:subject)')
			->andWhere('LOWER(m.body) = LOWER(:body)')
			->andWhere('t.createdAt >= :minDate')
			->setParameter('sender', $sender)
			->setParameter('subject', strtolower(trim($subject)))
			->setParameter('body', strtolower(trim($body)))
			->setParameter('minDate', (new \DateTime())->sub(new \DateInterval('P'.$backwardDaysInterval.'D')))
		;

		try {
			return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
		} catch (NonUniqueResultException $e) {
			return false;
		}
	}

	/////

	public function findOneByIdJoinedOnOptimized($id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 't', 'tm', 'm', 's', 'mm', 'p' ))
			->from($this->getEntityName(), 't')
			->innerJoin('t.metas', 'tm')
			->innerJoin('t.messages', 'm')
			->innerJoin('m.sender', 's')
			->innerJoin('m.metas', 'mm')
			->innerJoin('mm.participant', 'p')
			->where('t.id = :id')
			->setParameter('id', $id);

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findOneByIdJoinedOnMetaAndParticipant($id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 't', 'tm', 'p' ))
			->from($this->getEntityName(), 't')
			->innerJoin('t.metas', 'tm')
			->innerJoin('tm.participant', 'p')
			->where('t.id = :id')
			->setParameter('id', $id);

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	public function countUnreadMessageByThreadAndUser($thread, User $participant) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select('COUNT(m)')
			->from($this->getEntityName(), 't')
			->innerJoin('t.messages', 'm')
			->innerJoin('m.metas', 'mm')
			->where('t = :thread')
			->andWhere('mm.participant = :participant')
			->andWhere('mm.isRead = true')
			->setParameter('thread', $thread)
			->setParameter('participant', $participant);

		try {
			return $thread->getMessageCount() - $queryBuilder->getQuery()->getSingleScalarResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return 0;
		}
	}

	/////

	public function findPaginedByUsers(array $users, $offset, $limit, $filter = 'all') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 't' ))
			->from($this->getEntityName(), 't')
			->innerJoin('t.metas', 'tm')
			->innerJoin('tm.participant', 'p')
			->where('p IN (:users)')
			->andWhere('tm.isDeleted = false')
			->setParameter('users', $users)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		if ($filter == 'sent' && count($users) > 0) {
			$queryBuilder
				->andWhere('t.createdBy = :createdBy')
				->setParameter('createdBy', $users[0]);
		}

		$queryBuilder
			->addOrderBy('t.lastMessageDate', 'DESC');

		return new Paginator($queryBuilder->getQuery());
	}

}